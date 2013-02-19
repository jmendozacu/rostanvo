<?php

class Magestore_Customerreward_Model_Actions_Cashback extends Magestore_Customerreward_Model_Actions_Abstract
{
	protected $_action = 'cashback';
	protected $_title = 'Cash back from order %s';
	
	protected function _calculateAmount(){
		$order = $this->getActionObject();
		
		$rewardOrder = Mage::getModel('customerreward/order')->loadByOrderIncrementId($order->getIncrementId());
		$offer = Mage::getModel('customerreward/offer')->load($rewardOrder->getOfferId());
		
		if (!$offer->getId() || $offer->getDiscountMethod() != Magestore_Customerreward_Helper_Offer::OFFER_METHOD_CASHBACK) return $this;
		
		$productIds = $offer->getProductIdsByOrder($order);
		$total = 0; $items = 0;
		foreach ($order->getItemsCollection() as $item)
			if (!$item->isDeleted() && in_array($item->getProductId(),$productIds)){
				$total += $item->getBasePrice();
				$items ++;
			}
		
		if ($offer->getDiscountType() == Magestore_Customerreward_Helper_Offer::OFFER_TYPE_FIXED){
			if ($items) $this->setAmount($offer->getDiscount());
		}else{
			$referalGrandTotal = $total * $offer->getDiscount() / 100;
			$customer = $this->getCustomer();
			$pointToMoney = Mage::getModel('customerreward/rate')->getPointToMoney($customer->getWebsiteId(),$customer->getGroupId());
			if ($pointToMoney['money']){
				$this->setAmount(floor($referalGrandTotal * $pointToMoney['points'] / $pointToMoney['money']));
			}
		}
		return $this;
	}
	
	public function getTitle(){
		return Mage::helper('customerreward')->__($this->_title,$this->_extraContent['order_increment_id']);
	}
	
	public function getTitleHtml($isAdminArea = false){
		parse_str($this->getTransaction()->getData('extra_content'),$order);
		if ($isAdminArea){
			$url = Mage::getUrl('adminhtml/sales_order/view',array('order_id' => $order['order_id']));
		}else{
			$url = Mage::getUrl('sales/order/view',array('order_id' => $order['order_id']));
		}
		return Mage::helper('customerreward')->__($this->_title,'<a href="'.$url.'">#'.$order['order_increment_id'].'</a>');
	}
}