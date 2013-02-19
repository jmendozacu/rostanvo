<?php

class Magestore_Customerreward_Model_Observer
{
	public function couponPostAction($observer){
		if (!Mage::helper('magenotification')->checkLicenseKey('Customerreward')){return;}
		if (!Mage::helper('customerreward')->getReferConfig('coupon')) return $this;
		
		$action = $observer->getEvent()->getControllerAction();
		$code = trim($action->getRequest()->getParam('coupon_code'));
		if (!$code) return $this;
		
		$count = Mage::getModel('customerreward/count')->loadByCoupon($code);
		
		$customerId = Mage::getSingleton('customer/session')->getCustomer()->getId();
		if (!$count->getId() || $count->getCustomerId() == $customerId) return $this->useDefaultCoupon();
		if (!$count->validateCount()) return $this->useDefaultCoupon();
		
		if ($action->getRequest()->getParam('remove') == 1){
			if ($count->getKey() == Mage::getSingleton('core/cookie')->get('customerreward_offer_key')){
				Mage::getSingleton('core/cookie')->delete('customerreward_offer_key');
				Mage::getSingleton('checkout/session')->addSuccess(Mage::helper('customerreward')->__('Coupon code was canceled.'));
			}
		}else{
			Mage::getSingleton('core/cookie')->set('customerreward_offer_key',$count->getKey());
			Mage::getSingleton('checkout/session')->addSuccess(Mage::helper('customerreward')->__('Coupon code "%s" was applied.',$code));
		}
		$action->setFlag('', Mage_Core_Controller_Varien_Action::FLAG_NO_DISPATCH, true);
		$action->getResponse()->setRedirect(Mage::getUrl('checkout/cart'));
	}
	
	public function useDefaultCoupon(){
		if (Mage::getSingleton('core/cookie')->get('customerreward_offer_key'))
			Mage::getSingleton('core/cookie')->delete('customerreward_offer_key');
		return;
	}
	
	public function customerLogin($observer){
		if (Mage::helper('customerreward')->isDisabled()) return $this;
		if(!Mage::helper('magenotification')->checkLicenseKey('Customerreward')){return;}
		$customer = $observer->getEvent()->getCustomer();
		if (!$customer->getId()) return $this;
		if (Mage::getModel('customerreward/customer')->loadByCustomerId($customer->getId())->getId()) return $this;
		
		$rewardCustomer = Mage::getModel('customerreward/customer')->load()
			->setData(array(
				'customer_id'	=> $customer->getId(),
				'total_points'	=> 0,
				'is_notification'	=> 1,
			))
			->save();
		
		Mage::helper('customerreward/action')->addTransaction('initialize',$customer,$customer,array('notice' => Mage::helper('customerreward')->__('Initialize the points balance in the system')));
		return $this;
	}
	
	public function newsletterSubscriber($observer){
		if (Mage::helper('customerreward')->isDisabled()) return $this;
		
		if(!Mage::helper('magenotification')->checkLicenseKey('Customerreward')){return;}
		
		$subscriber = $observer->getEvent()->getSubscriber();
		if (!$subscriber->isSubscribed()) return $this;
		
		$customer = Mage::getModel('customer/customer')->load($subscriber->getCustomerId());
		
		$rewardCustomer = Mage::getModel('customerreward/customer')->loadByCustomerId($customer->getId());
		if (!$rewardCustomer->getId()) return $this;
		
		parse_str($rewardCustomer->getExtraContent(),$customerExtraContent);
		if ($customerExtraContent['signup_newsletter']) return $this;
		
		Mage::helper('customerreward/action')->addTransaction('newsletter',$customer,$subscriber,array(
			'notice'	=> Mage::helper('customerreward')->__('Signup newsletter!'),
			'customer_extra_content' => array(
				'signup_newsletter' => 1,
			),
		));
		return $this;
	}
	
	public function reviewSaveAfter($observer){
		$review = $observer->getDataObject();
		if (Mage::helper('customerreward')->isDisabled($review->getStoreId())) return $this;
		
		if(!Mage::helper('magenotification')->checkLicenseKey('Customerreward')){return;}
		
		if ($review->getStatusId() != Mage_Review_Model_Review::STATUS_APPROVED) return $this;
		
		$customerId = $review->getCustomerId();
		if (!$customerId) return $this;
		
		$productId = $review->getEntityPkValue();
		$product = Mage::getModel('catalog/product')->load($productId);
		
		$customer = Mage::getModel('customer/customer')->load($customerId);
		
		if (Mage::getModel('customerreward/transaction')->loadByProductId($customerId,$productId,'review')->getId()) return $this;
		
		Mage::helper('customerreward/action')->addTransaction('review',$customer,$review,array(
			'notice'	=> Mage::helper('customerreward')->__('Review product!'),
			'extra_content'	=> array(
				'product_id'	=> $product->getId(),
				'product_name'	=> $product->getName(),
			),
			'product_name'	=> $product->getName(),
		));
		return $this;
	}
	
	public function modelSaveAfter($observer){
		if (Mage::helper('customerreward')->isDisabled()) return $this;
		
		if (($tagRelation = $observer->getObject()) instanceof Mage_Tag_Model_Tag_Relation){
			if(!Mage::helper('magenotification')->checkLicenseKey('Customerreward')){return;}
			$tag = Mage::getModel('tag/tag')->load($tagRelation->getTagId());
			if ($tag->getStatus() == Mage_Tag_Model_Tag::STATUS_APPROVED)
				return $this->_addTagProductPoint($tagRelation);
			return $this;
		}
		if (($pollVote = $observer->getObject()) instanceof Mage_Poll_Model_Poll_Vote){
			if(!Mage::helper('magenotification')->checkLicenseKey('Customerreward')){return;}
			$customerId = $pollVote->getCustomerId();
			if (!$customerId) return $this;
			$customer = Mage::getModel('customer/customer')->load($customerId);
			Mage::helper('customerreward/action')->addTransaction('poll',$customer,$tagRelation,array(
				'notice'	=> Mage::helper('customerreward')->__('Participate in poll!'),
			));
			return $this;
		}
	}
	
	public function adminTagSave($observer){
		$action = $observer->getEvent()->getControllerAction();
		$tagId = $action->getRequest()->getParam('tag_id');
		
		$storeId = $action->getRequest()->getParam('store');
		if (Mage::helper('customerreward')->isDisabled($storeId)) return $this;
		
		if(!Mage::helper('magenotification')->checkLicenseKey('Customerreward')){return;}
		
		$tagStatus = $action->getRequest()->getParam('tag_status');
		if ($tagStatus != Mage_Tag_Model_Tag::STATUS_APPROVED) return $this;
		
		$tagRelationCollection = Mage::getModel('tag/tag')->getCollection()->joinRel();
		$tagRelationCollection->getSelect()
			->where('main_table.tag_id = ?',$tagId)
			->where('relation.store_id = ?',$storeId)
			->where('relation.active = 1');
		foreach ($tagRelationCollection->getData() as $tag){
			$tagRelation = Mage::getModel('tag/tag_relation')->load($tag['tag_relation_id']);
			$this->_addTagProductPoint($tagRelation);
		}
		return $this;
	}
	
	protected function _addTagProductPoint($tagRelation){
		$customerId = $tagRelation->getCustomerId();
		if (!$customerId) return $this;
		
		$productId = $tagRelation->getProductId();
		$product = Mage::getModel('catalog/product')->load($productId);
		
		$customer = Mage::getModel('customer/customer')->load($customerId);
		
		$rewardCustomer = Mage::getModel('customerreward/customer')->loadByCustomerId($customer->getId());
		if (!$rewardCustomer->getId()) return $this;
		
		parse_str($rewardCustomer->getExtraContent(),$customerExtraContent);
		$productIds = explode(',',$customerExtraContent['tag_product_ids']);
		if (in_array($productId,$productIds)) return $this;
		$productIds[] = $productId;
		
		Mage::helper('customerreward/action')->addTransaction('tag',$customer,$tagRelation,array(
			'notice'	=> Mage::helper('customerreward')->__('Tag product!'),
			'extra_content'	=> array(
				'product_id'	=> $product->getId(),
				'product_name'	=> $product->getName(),
			),
			'product_name'	=> $product->getName(),
			'customer_extra_content' => array(
				'tag_product_ids' => implode(',',$productIds),
			),
		));
		return $this;
	}
	
	public function paymentImportDataBefore($observer){
		$input = $observer->getEvent()->getInput();
		$session = Mage::getSingleton('checkout/session');
		
		$session->setData('use_point',$input->getData('use_point'));
		$session->setData('point_amount',$input->getData('point_amount'));
		return $this;
	}
	
	public function orderPlaceBefore($observer){
		$session = Mage::getSingleton('checkout/session');
		$order = $observer->getEvent()->getOrder();
		if ($session->getData('offer_discount')){
			$order->setOfferDiscount($session->getData('offer_discount'));
			$order->setBaseOfferDiscount($session->getData('base_offer_discount'));
		}
		if ($session->getData('use_point') && $session->getData('point_amount')>0){
			if ($order->getCustomerIsGuest()) return $this;
			
			$pointAmount = (int)$session->getData('point_amount');
			$customer = Mage::getSingleton('customer/session')->getCustomer();
			if (!$customer->getId()) return $this;
			$customerBalance = $this->getBalance($customer->getId());
			if ($customerBalance < $pointAmount || $customerBalance < (int)Mage::helper('customerreward')->getEarnConfig('min'))
				throw new Mage_Core_Exception(Mage::helper('customerreward')->__('Incorrect point(s) amount!'));
			$moneyBase = $pointAmount * Mage::getModel('customerreward/rate')->getPointToMoneyRate($customer->getWebsiteId(),$customer->getGroupId());
			$currentMoney = Mage::app()->getStore()->convertPrice($moneyBase);
			
			$order->setPointAmount(-$pointAmount);
			$order->setMoneyBase(-$moneyBase);
			$order->setCurrentMoney(-$currentMoney);
		}
		return $this;
	}
	
	public function orderPlaceAfter($observer){
		$order = $observer->getEvent()->getOrder();
		if ($order->getPointAmount()){
			$customer = Mage::getSingleton('customer/session')->getCustomer();
			Mage::helper('customerreward/action')->addTransaction('spend',$customer,$order,array(
				'notice'	=> Mage::helper('customerreward')->__('Spend for order!'),
				'extra_content'	=> array(
					'order_id'	=> $order->getId(),
					'order_increment_id'	=> $order->getIncrementId(),
					'money_base'	=> $order->getMoneyBase(),
					'current_money'	=> $order->getCurrentMoney(),
				),
				'order_increment_id'	=> $order->getIncrementId(),
			));
		}
		$key = Mage::getSingleton('core/cookie')->get('customerreward_offer_key');
		if (!$key) return $this;
		$count = Mage::getModel('customerreward/count')->loadByKey($key);
		if (!$count || !$count->getId()) return $this;
		$offer = Mage::getModel('customerreward/offer')->loadByCount($count);
		if ($offer && $offer->getId()){
			$customerId = $count->getCustomerId();
			$rewardOrder = Mage::getModel('customerreward/order')->load()
				->setOrderIncrementId($order->getIncrementId())
				->setCustomerId($customerId)
				->setOfferId($offer->getId());
			$session = Mage::getSingleton('checkout/session');
			if ($session->getData('offer_discount')){
				$rewardOrder->setOfferDiscount($session->getData('offer_discount'))
					->setBaseOfferDiscount($session->getData('base_offer_discount'));
			}
			try{
				$rewardOrder->save();
				$count->setUsed($count->getUsed()+1)
					->setId($count->getId())
					->save();
				$countCustomer = Mage::getModel('customerreward/count_customer')->loadByCount($count->getId());
				if ($countCustomer->getCountId())
					$countCustomer->setUsed($countCustomer->getUsed()+1)
						->setId($countCustomer->getId())
						->save();
			}catch(Exception $e){}
		}
	}
	
	public function getBalance($customerId){
    	return (int)Mage::getModel('customerreward/customer')->loadByCustomerId($customerId)->getData('total_points');
    }
	
	public function onepageCheckoutSuccess($observer){
		$session = Mage::getSingleton('checkout/session');
		$session->unsetData('use_point');
		$session->unsetData('point_amount');
		
		$session->unsetData('offer_discount');
		$session->unsetData('base_offer_discount');
	}
	
	public function orderLoadAfter($observer){
		$order = $observer->getEvent()->getOrder();
		$transaction = Mage::getModel('customerreward/transaction')->loadByOrder($order);
		parse_str($transaction->getExtraContent(),$orderInfo);
		$order->setMoneyBase($orderInfo['money_base']);
		$order->setCurrentMoney($orderInfo['current_money']);
		
		$rewardOrder = Mage::getModel('customerreward/order')->loadByOrderIncrementId($order->getIncrementId());
		$order->setOfferDiscount($rewardOrder->getOfferDiscount());
		$order->setBaseOfferDiscount($rewardOrder->getBaseOfferDiscount());
	}
	
	public function invoiceLoadAfter($observer){
		$invoice = $observer->getEvent()->getInvoice();
		$order = $invoice->getOrder();
		$transaction = Mage::getModel('customerreward/transaction')->loadByOrder($order);
		parse_str($transaction->getExtraContent(),$orderInfo);
		$invoice->setMoneyBase($orderInfo['money_base']);
		$invoice->setCurrentMoney($orderInfo['current_money']);
		
		$rewardOrder = Mage::getModel('customerreward/order')->loadByOrderIncrementId($order->getIncrementId());
		$invoice->setOfferDiscount($rewardOrder->getOfferDiscount());
		$invoice->setBaseOfferDiscount($rewardOrder->getBaseOfferDiscount());
	}
	
	public function creditmemoLoadAfter($observer){
		$creditmemo = $observer->getEvent()->getCreditmemo();
		$order = $creditmemo->getOrder();
		$transaction = Mage::getModel('customerreward/transaction')->loadByOrder($order);
		parse_str($transaction->getExtraContent(),$orderInfo);
		$creditmemo->setMoneyBase($orderInfo['money_base']);
		$creditmemo->setCurrentMoney($orderInfo['current_money']);
		
		$rewardOrder = Mage::getModel('customerreward/order')->loadByOrderIncrementId($order->getIncrementId());
		$creditmemo->setOfferDiscount($rewardOrder->getOfferDiscount());
		$creditmemo->setBaseOfferDiscount($rewardOrder->getBaseOfferDiscount());
	}
	
	public function orderSaveAfter($observer){
		if (Mage::helper('customerreward')->isDisabled()) return $this;
		if(!Mage::helper('magenotification')->checkLicenseKey('Customerreward')){return;}
		$order = $observer->getOrder();
		if ($order->getStatus() == Mage_Sales_Model_Order::STATE_COMPLETE)
			$this->_receivePointInvoice($order,'cashback')
				->_receivePointInvoice($order,'rule')
				->_receivePointInvoice($order,'invoice')
				->_receivePointByOffer($order);
		$cancelStatus = explode(',',Mage::helper('customerreward')->getEarnConfig('cancel_orderstatus'));
		if (in_array($order->getStatus(),$cancelStatus))
			$this->_refundPoint($order)
				->_refundOffer($order)
				->_cancelOrder($order);
	}
	
	protected function _refundPoint($order){
		$action = 'refund';
		if (Mage::getModel('customerreward/transaction')->loadByOrder($order,$action)->getId()) return $this;
		
		$customer = Mage::getModel('customer/customer')->load($order->getCustomerId());
		if ($customer->getId())
			Mage::helper('customerreward/action')->addTransaction($action,$customer,$order,array(
				'notice'	=> Mage::helper('customerreward')->__('Refund for order!'),
				'extra_content'	=> array(
					'order_id'	=> $order->getId(),
					'order_increment_id'	=> $order->getIncrementId(),
				),
				'order_increment_id'	=> $order->getIncrementId(),
			));
		return $this;
	}
	
	protected function _refundOffer($order){
		$action = 'refundoffer';
		if (Mage::getModel('customerreward/transaction')->loadByOrder($order,$action)->getId()) return $this;
		
		$rewardOrder = Mage::getModel('customerreward/order')->loadByOrderIncrementId($order->getIncrementId());
		$customer = Mage::getModel('customer/customer')->load($rewardOrder->getCustomerId());
		if ($customer->getId())
			Mage::helper('customerreward/action')->addTransaction($action,$customer,$order,array(
				'notice'	=> Mage::helper('customerreward')->__('Refund for order!'),
				'extra_content'	=> array(
					'order_id'	=> $order->getId(),
					'order_increment_id'	=> $order->getIncrementId(),
				),
				'order_increment_id'	=> $order->getIncrementId(),
			));
		return $this;
	}
	
	protected function _cancelOrder($order){
		$action = 'cancel';
		if (Mage::getModel('customerreward/transaction')->loadByOrder($order,$action)->getId()) return $this;
		
		$spendAction = 'spend';
		$spend = Mage::getModel('customerreward/transaction')->loadByOrder($order,$spendAction);
		$customer = Mage::getModel('customer/customer')->load($order->getCustomerId());
		
		if ($customer->getId() && $spend->getId())
			Mage::helper('customerreward/action')->addTransaction($action,$customer,$spend,array(
				'notice'	=> Mage::helper('customerreward')->__('Refund for order!'),
				'extra_content'	=> array(
					'order_id'	=> $order->getId(),
					'order_increment_id'	=> $order->getIncrementId(),
				),
				'order_increment_id'	=> $order->getIncrementId(),
			));
		return $this;
	}
	
	protected function _receivePointInvoice($order,$action){
		$rewardOrder = Mage::getModel('customerreward/order')->loadByOrderIncrementId($order->getIncrementId());
		if ( $rewardOrder && $rewardOrder->getOfferDiscount() != 0 ) return $this;
		if (Mage::getModel('customerreward/transaction')->loadByOrder($order,'cashback')->getId()) return $this;
		if (Mage::getModel('customerreward/transaction')->loadByOrder($order,'rule')->getId()) return $this;
		if (Mage::getModel('customerreward/transaction')->loadByOrder($order,$action)->getId()) return $this;
		$customer = Mage::getModel('customer/customer')->load($order->getCustomerId());
		if ($customer->getId()){
			Mage::helper('customerreward/action')->addTransaction($action,$customer,$order,array(
				'notice'	=> Mage::helper('customerreward')->__('Receive point(s) when invoice!'),
				'extra_content'	=> array(
					'order_id'	=> $order->getId(),
					'order_increment_id'	=> $order->getIncrementId(),
				),
				'order_increment_id'	=> $order->getIncrementId(),
			));
		}
		return $this;
	}
	
	protected function _receivePointByOffer($order){
		if (!Mage::helper('customerreward')->getReferConfig('enable')) return $this;
		$action = 'offer';
		if (Mage::getModel('customerreward/transaction')->loadByOrder($order,$action)->getId()) return $this;
		$rewardOrder = Mage::getModel('customerreward/order')->loadByOrderIncrementId($order->getIncrementId());
		$customer = Mage::getModel('customer/customer')->load($rewardOrder->getCustomerId());
		if ($customer->getId()){
			Mage::helper('customerreward/action')->addTransaction($action,$customer,$order,array(
				'notice'	=> Mage::helper('customerreward')->__('Receive point(s) when your referal invoice!'),
				'extra_content'	=> array(
					'order_id'	=> $order->getId(),
					'order_increment_id'	=> $order->getIncrementId(),
				),
				'order_increment_id'	=> $order->getIncrementId(),
			));
		}
		return $this;
	}
	
	public function actionPredispatch($observer){
		$action = $observer->getEvent()->getControllerAction();
		if ($key = Mage::getSingleton('core/cookie')->get('customerreward_offer_key')){
			$count = Mage::getModel('customerreward/count')->loadByKey($key);
			$customerId = Mage::getSingleton('customer/session')->getCustomer()->getId();
			if ($customerId == $count->getCustomerId()
				&& !$count->validateCount())
				Mage::getSingleton('core/cookie')->delete('customerreward_offer_key');
		}
		if ($key = $action->getRequest()->getParam('k')
			&& !Mage::helper('customerreward')->getReferConfig('coupon')){
			$count = Mage::getModel('customerreward/count')->loadByKey($key);
			$customerId = Mage::getSingleton('customer/session')->getCustomer()->getId();
			if ( $count->getId() && $customerId != $count->getCustomerId() ){
				$offer = Mage::getModel('customerreward/offer')->loadByCount($count);
				if (!Mage::getSingleton('core/cookie')->get('customerreward_offer_key') || Mage::getSingleton('core/cookie')->get('customerreward_offer_key') != $key){
					Mage::getSingleton('core/cookie')->set('customerreward_offer_key',$key);
					$count->setVisitCount($count->getVisitCount()+1);
					if ($offer && $offer->getId())
						$this->_spendForVisitLink($count);
				}
				$clientIp = $action->getRequest()->getClientIp();
				$ipList = explode(',',$count->getIpList());
				if (!in_array($clientIp,$ipList)){
					$ipList[] = $clientIp;
					$count->setIpList(implode(',',$ipList))
						->setUniqueClick($count->getUniqueClick()+1);
					if ($offer && $offer->getId())
						$this->_spendForUniqueClick($count);
				}
				try{
					$count->save();
				}catch(Exception $e){}
			}
		}
		return $this;
	}
	
	public function productChangeFinalPrice($observer){
		$count = Mage::getModel('customerreward/count')->loadByKey(Mage::getSingleton('core/cookie')->get('customerreward_offer_key'));
		if (!$count || !$count->getId()) return $this;
		$offer = Mage::getModel('customerreward/offer')->loadByCount($count);
		if ($offer && $offer->getId()){
			if ($offer->getDiscountMethod() != Magestore_Customerreward_Helper_Offer::OFFER_METHOD_DISCOUNT
				|| $offer->getDiscountShow() != Magestore_Customerreward_Helper_Offer::SHOW_OFFER_IN_PRODUCT)
					return $this;
			$product = $observer->getEvent()->getProduct();
			$productIds = $offer->getProductIds();
			if (!in_array($product->getId(),$productIds)) return $this;
			if ($offer->getDiscountType() == Magestore_Customerreward_Helper_Offer::OFFER_TYPE_FIXED){
				$price = $product->getFinalPrice() - $offer->getDiscount();
				if ($price > 0)
					$product->setFinalPrice($price);
				else
					$product->setFinalPrice(0);
			}else{
				$price = $product->getFinalPrice()*(1-$offer->getDiscount()/100);
				if ($price > 0)
					$product->setFinalPrice($price);
				else
					$product->setFinalPrice(0);
			}
		}
	}
	
	public function productListCollection($observer){
		$count = Mage::getModel('customerreward/count')->loadByKey(Mage::getSingleton('core/cookie')->get('customerreward_offer_key'));
		if (!$count || !$count->getId()) return $this;
		$offer = Mage::getModel('customerreward/offer')->loadByCount($count);
		if ($offer && $offer->getId()){
			if ($offer->getDiscountMethod() != Magestore_Customerreward_Helper_Offer::OFFER_METHOD_DISCOUNT
				|| $offer->getDiscountShow() != Magestore_Customerreward_Helper_Offer::SHOW_OFFER_IN_PRODUCT)
					return $this;
			$productIds = $offer->getProductIds();
			$productCollection=$observer->getEvent()->getCollection();
			foreach ($productCollection as $product){
				if (in_array($product->getId(),$productIds)){
					if ($offer->getDiscountType() == Magestore_Customerreward_Helper_Offer::OFFER_TYPE_FIXED){
						$price = $product->getFinalPrice() - $offer->getDiscount();
						if ($price > 0)
							$product->setFinalPrice($price);
						else
							$product->setFinalPrice(0);
					}else{
						$price = $product->getFinalPrice()*(1-$offer->getDiscount()/100);
						if ($price > 0)
							$product->setFinalPrice($price);
						else
							$product->setFinalPrice(0);
					}
				}
			}
		}
	}
	
	protected function _spendForVisitLink($count){
		if (!Mage::helper('customerreward')->getReferConfig('visit')) return $this;
		$customer = Mage::getModel('customer/customer')->load($count->getCustomerId());
		if ($customer->getId())
			Mage::helper('customerreward/action')->addTransaction('visit',$customer,$count,array(
				'notice'	=> Mage::helper('customerreward')->__('Receive point(s) when your referal click to link'),
			));
		return $this;
	}
	
	protected function _spendForUniqueClick($count){
		if (!Mage::helper('customerreward')->getReferConfig('uniqueclick')) return $this;
		$customer = Mage::getModel('customer/customer')->load($count->getCustomerId());
		if ($customer->getId())
			Mage::helper('customerreward/action')->addTransaction('uniqueclick',$customer,$count,array(
				'notice'	=> Mage::helper('customerreward')->__('Receive point(s) when your referal click to your link!'),
			));
		return $this;
	}
	
	public function paypalPrepareItems($observer){
		$paypalCart = $observer->getEvent()->getPaypalCart();
		if ($paypalCart){
			$salesEntity = $paypalCart->getSalesEntity();
			if ($salesEntity->getMoneyBase())
				$paypalCart->updateTotal(Mage_Paypal_Model_Cart::TOTAL_DISCOUNT,abs((float)$salesEntity->getMoneyBase()),Mage::helper('customerreward')->__('Use point(s) on spend'));
			if ($salesEntity->getBaseOfferDiscount())
				$paypalCart->updateTotal(Mage_Paypal_Model_Cart::TOTAL_DISCOUNT,abs((float)$salesEntity->getBaseOfferDiscount()),Mage::helper('customerreward')->__('Offer Discount'));
		}
	}
	
	public function expireTransaction(){
		if (Mage::helper('customerreward')->isDisabled()) return $this;
		
		$day = Mage::getModel('core/date')->gmtDate();		
		$expiredTransactions = Mage::getModel('customerreward/transaction')->getCollection()
			->addAvailableBalanceFilter()
			->addFieldToFilter('is_expired',0)
			->addFieldToFilter('expiration_date',array('to' => $day))
			->addFieldToFilter('expiration_date',array('notnull' => true));
		foreach ($expiredTransactions as $transaction){
			$pointAvailable = $transaction->getPointsSpent() - $transaction->getPointsChange();
			$transaction->setIsExpired(1)->save();
			$rewardCustomer = Mage::getModel('customerreward/customer')->loadByCustomerId($transaction->getCustomerId());
			if ($rewardCustomer->getId())
				$rewardCustomer->setTotalPoints($rewardCustomer->getTotalPoints()+$pointAvailable)->save();
		}
		
		if (!Mage::helper('customerreward')->getEmailConfig('enable')) return $this;
		
		$dayBefore = (int)Mage::helper('customerreward')->getEmailConfig('day_before');
		$beforeExpireTransactions = Mage::getModel('customerreward/transaction')->getCollection()
			->addAvailableBalanceFilter()
			->addFieldToFilter('is_expired',0)
			->addExpireAfterDaysFilter($dayBefore);
		foreach ($beforeExpireTransactions as $transaction){
			$this->_sendEmailNotification($transaction,$dayBefore);
		}
	}
	
	protected function _sendEmailNotification($transaction,$dayBefore){
		$rewardCustomer = Mage::getModel('customerreward/customer')->loadByCustomerId($transaction->getCustomerId());
		if (!$rewardCustomer->getIsNotification()) return $this;
		$customer = Mage::getModel('customer/customer')->load($transaction->getCustomerId());
		
		$translate = Mage::getSingleton('core/translate');
		$translate->setTranslateInline(false);
		
		$mailTemplate = Mage::getModel('core/email_template')
			->setDesignConfig(array(
				'area'	=> 'frontend',
				'store'	=> $transaction->getStoreId(),
			))
			->sendTransactional(
				Mage::helper('customerreward')->getEmailConfig('transaction_expire'),
				Mage::helper('customerreward')->getEmailConfig('sender'),
				$customer->getEmail(),
				$customer->getName(),
				array(
					'store'	=> $store,
					'customer'	=> $customer,
					'title'	=> $transaction->getTitle(),
					'amount'	=> $transaction->getPointsChange(),
					'spent'		=> $transaction->getPointsSpent(),
					'total'	=> $rewardCustomer->getTotalPoints(),
					'expirationdays'	=> $dayBefore,
					'expirationdate'	=> Mage::getModel('core/date')->date('M d, Y H:i:s',$transaction->getExpirationDate()),
				)
			);
		
		$translate->setTranslateInline(true);
		return $this;
	}
	
	public function customerSaveAfter($observer){
		$customer = $observer->getEvent()->getCustomer();
		$customerReward = Mage::getModel('customerreward/customer')->loadByCustomerId($customer->getId());
		if (!$customerReward->getId()) return $this;
		
		$is_notification = Mage::app()->getRequest()->getParam('is_notification') ? 1 : 0;
		$balance_change = Mage::app()->getRequest()->getParam('change_balance');
		if ($customerReward->getIsNotification() != $is_notification)
			$customerReward->setIsNotification($is_notification)->save();
		if ($balance_change){
			$balanChange = new Varien_Object();
			$balanChange->setBalanceChange((int)$balance_change);
			Mage::helper('customerreward/action')->addTransaction('admin',$customer,$balanChange,array('notice' => Mage::helper('customerreward')->__('Change by admin')));
		}
		return $this;
	}
	
	public function checkoutPredispatch($observer){
		return;
		if (Mage::helper('customerreward')->isDisabled()) return $this;
		$controllerAction = $observer->getEvent()->getControllerAction();
		if ($controllerAction->getRequest()->getRequestedControllerName() == 'onepage' ){
			$rewrite = Mage::getConfig()->getNode('global/blocks/checkout/rewrite');
			$rewritenode = Mage::getConfig()->getNode('global/blocks/checkout/rewritenode');
			foreach ($rewritenode->children() as $node)
				$rewrite->appendChild($node);
		}
	}
}