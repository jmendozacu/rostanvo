<?php
class Magestore_Customerreward_Block_Offer extends Mage_Core_Block_Template
{
	protected $_offer;
	
	protected $_items_in;
	protected $_referal_offer;
	
	protected $_reward_count;
	
	protected function _construct(){
		parent::_construct();
		$this->_reward_count = $this->addRewardCount();
	}
	
	public function _prepareLayout(){
		return parent::_prepareLayout();
    }
    
	public function getTemplate()
	{
		if(!Mage::helper('magenotification')->checkLicenseKey('Customerreward')){
			return null;
		} else {
			return parent::getTemplate();
		}
	}
	
    protected function _getOrder(){
    	$orderId = Mage::getSingleton('checkout/session')->getLastOrderId();
    	$order = Mage::getModel('sales/order')->load($orderId);
    	if ($order->getId()) return $order;
    	return null;
    }
    
    protected function _getCustomer(){
    	$customer = Mage::getSingleton('customer/session')->getCustomer();
    	if ($customer->getId()) return $customer;
    	return null;
    }
    
    public function getOffer(){
		$order = $this->_getOrder();
    	if (is_null($this->_offer) && is_null($this->_referal_offer) && $order){
			$customer = $this->_getCustomer();
			$count = Mage::getModel('customerreward/count')->loadByKey(Mage::getSingleton('core/cookie')->get('customerreward_offer_key'));
			if ($count && $count->getId())
				$offer = Mage::getModel('customerreward/offer')->loadByCount($count);
			$allItems = $order->getAllItems();
			$itemsIn = 0;
			if ($offer && $offer->getId()){
				$productIds = $offer->getProductIdsByOrder($order);
				foreach ($allItems as $item)
					if (in_array($item->getProductId(),$productIds))
						$itemsIn++;
				if ( $itemsIn && $count->getCustomerId() != $customer->getId() ){
					$this->_referal_offer = $offer;
					$this->_items_in = $itemsIn;
				}else
					$this->_referal_offer = Mage::getModel('customerreward/offer')->load();
			}
			$itemsOut = count($allItems)-$itemsIn;
			if ( $customer && $order->getCustomerId() == $customer->getId()
				&& ($itemsOut || $count->getCustomerId() == $customer->getId()) )
				$this->_offer = Mage::getModel('customerreward/offer')->loadByCustomerOrder($customer,$order,$this->_referal_offer);
			else
				$this->_offer = Mage::getModel('customerreward/offer')->load();
   		}
		if ($this->getIsReferal()){
            if (Mage::helper('customerreward')->getReferConfig('coupon')
                && Mage::getSingleton('core/cookie')->get('customerreward_offer_key'))
                    Mage::getSingleton('core/cookie')->delete('customerreward_offer_key');
            
			return $this->_referal_offer;
        }
    	return $this->_offer;
    }
    
    public function addRewardCount(){
    	$customer = $this->_getCustomer();
    	$offer = $this->getOffer();
    	if ($this->getIsReferal()) return null;
    	if ($customer && $offer && $offer->getId()){
    		$rewardCount = Mage::getModel('customerreward/count')->getCollection()
    			->addFieldToFilter('customer_id',$customer->getId())
    			->addFieldToFilter('offer_id',$offer->getId())
    			->getFirstItem();
   			if ($rewardCount->getCoupon())
               return $rewardCount;
            elseif (!$rewardCount->getId())
       			$rewardCount = Mage::getModel('customerreward/count')
       				->setData('customer_id',$customer->getId())
       				->setData('offer_id',$offer->getId());
            if (Mage::helper('customerreward/coupon')->isExpression($offer->getCoupon()))
                $rewardCount->setData('coupon',$offer->getCoupon());
 			try{
 				$rewardCount->save();
                if (!$rewardCount->getData('key'))
 				   $rewardCount->setData('key',$rewardCount->getHashKey())->save();
 				return $rewardCount;
 			}catch(Exception $e){
 				return $rewardCount;
 			}
    	}
    }
    
    public function getImageSrc($offer){
    	$url = $offer->getImage();
    	if ($url){
    		return $this->escapeUrl(Mage::getBaseUrl('media').$url);
    	}
    	return $this->getSkinUrl('images/np_thumb.gif');
    }
    
    public function getCouponCode(){
        return $this->_reward_count->getCoupon();
    }
    
    public function getShareUrl($offer = null){
        if (Mage::helper('customerreward')->getReferConfig('coupon'))
            return $this->getUrl();
    	$key = $this->_reward_count->getKey();
    	return $this->getUrl('customerreward/offer/view',array('k' => $key));//.'?k='.$key;
    }
    
    public function getEmailUrl($offerId,$title,$url,$coupon){
    	$emailUrl = sprintf('%s?offer_id=%s&title=%s&url=%s',$this->getUrl('customerreward/offer/email'),$offerId,urlencode($title),urlencode($url));
        if (Mage::helper('customerreward')->getReferConfig('coupon'))
            $emailUrl .= '&coupon='.urlencode($coupon);
        return $emailUrl;
    }
    
    public function getToDate($offer){
    	return Mage::getModel('core/date')->date('M d, Y',$offer->getToDate());
    }
    
    public function showDiscountHtml(){
    	$offer = $this->getOffer();
    	
    	if (!$offer || !$offer->getDiscount()) return '';
    	
    	if ($offer->getDiscountMethod() == Magestore_Customerreward_Helper_Offer::OFFER_METHOD_DISCOUNT){
    		$html = $this->__('<h3>Discount at the %s</h3>',$this->getStoreName());
    		$discountText = $this->__('discount');
    		$discount = $offer->getDiscount();
    		if ($this->getIsReferal() && $this->_items_in)
    			$discount *= $this->_items_in;
    		$currencyText = $this->getCurrentCurrencyText($discount);
    	}else{
    		$html = $this->__('<h3>Reward points at the %s</h3>',$this->getStoreName());
    		$discountText = $this->__('reward');
    		$currencyText = $this->__('%.0f point(s)',$offer->getDiscount());
    	}
        
        if (Mage::helper('customerreward')->getReferConfig('coupon'))
            $offerText = $this->__('coupon');
        else
            $offerText = $this->__('special friends only offer');
    	
    	$html .= '<ul class="offer-discount">';
    	
    	if ($offer->getDiscountType() == Magestore_Customerreward_Helper_Offer::OFFER_TYPE_FIXED)
    		$html .= $this->__('<li>Get <strong class="customerreward-money">%s</strong> %s on purchase using this %s.</li>',$currencyText,$discountText,$offerText);
    	else
    		$html .= $this->__('<li>Get %s as <strong class="customerreward-money">%.0f%%</strong> on purchase using this %s.</li>',$discountText,$offer->getDiscount(),$offerText);
    	
    	$html .= '</ul>';
    	
    	return $html;
    }
    
    public function getStoreName(){
    	return Mage::app()->getStore()->getFrontendName();
    }
    
    public function showEarnHtml(){
    	$customer = Mage::getSingleton('customer/session')->getCustomer();
    	$websiteId = $customer->getWebsiteId();
    	$groupId = $customer->getGroupId();
    	$rate = Mage::getModel('customerreward/rate');
    	
    	$visitToPoint = $rate->getVisitToPoint($websiteId,$groupId);
    	$clickToPoint = $rate->getClickToPoint($websiteId,$groupId);
    	
    	$offer = $this->getOffer();
		
		if (Mage::helper('customerreward')->getReferConfig('coupon'))
            $offerText = $this->__('coupon');
        else
            $offerText = $this->__('link');
    	
    	$html = '<ul class="offer-earn">';
    	if ($offer->getCommissionType() == Magestore_Customerreward_Helper_Offer::OFFER_TYPE_FIXED
    		&& $offer->getCommission())
    		$html .= $this->__('<li>Earn <strong class="customerreward-money">%s point(s)</strong> per purchase from your %s</li>',$offer->getCommission(),$offerText);
    	elseif ($offer->getCommission())
    		$html .= $this->__('<li>Earn <strong class="customerreward-money">%s%%</strong> sales from your %s</li>',$offer->getCommission(),$offerText);
    	
    	if ($visitToPoint['money'] && Mage::helper('customerreward')->getReferConfig('visit'))
    		$html .= $this->__('<li>Earn <strong class="customerreward-money">%s point(s)</strong> from <strong class="customerreward-money">%.0f visitor(s)</strong> from your %s</li>',$visitToPoint['points'],$visitToPoint['money'],$offerText);
    	if ($clickToPoint['money'] && Mage::helper('customerreward')->getReferConfig('uniqueclick'))
    		$html .= $this->__('<li>Earn <strong class="customerreward-money">%s point(s)</strong> from <strong class="customerreward-money">%.0f unique click(s)</strong> from your %s</li>',$clickToPoint['points'],$clickToPoint['money'],$offerText);
    	$html .= '</ul>';
    	
    	return $html;
    }
    
    public function getCurrentCurrencyText($money){
    	$currentMoney = Mage::app()->getStore()->convertPrice($money);
    	return Mage::app()->getStore()->formatPrice($currentMoney);
    }
}