<?php
class Magestore_Customerreward_Block_Offer_View extends Mage_Core_Block_Template
{
	protected $_offer;
	
	public function setOffer($offer)
	{
		$this->_offer = $offer;
		return $this;
	}
	
	public function _prepareLayout(){
		$headBlock = $this->getLayout()->getBlock('head');
		if ($headBlock){
			$titleDescription = $this->getTitleDescription();
			if ($title = $titleDescription['title'])
				$headBlock->setTitle($title);
			else
				$headBlock->setTitle($this->__('Expired Offer!'));
			if ($description = $titleDescription['description'])
				$headBlock->setDescription($description);
			if ($img = $this->getImgSrc())
				$headBlock->addLinkRel('image_src',$img);
		}
		return parent::_prepareLayout();
    }
    
    public function getOffer(){
        if (Mage::helper('customerreward')->getReferConfig('coupon'))
            return $this->_offer;
    	if (is_null($this->_offer)){
    		if ($key = Mage::app()->getRequest()->getParam('k'))
				$count = Mage::getModel('customerreward/count')->loadByKey($key);
			if (!$count || !$count->getId())
				$count = Mage::getModel('customerreward/count')->loadByKey(Mage::getSingleton('core/cookie')->get('customerreward_offer_key'));
			if ($count && $count->getId()){
				$this->_offer = Mage::getModel('customerreward/offer')->loadByCount($count);
			}
    	}
    	return $this->_offer;
    }
    
    public function getImgSrc(){
    	if ($this->getOffer() && $this->getOffer()->getId()){
    		$url = $this->getOffer()->getImage();
    		if ($url)
    			return $this->escapeUrl(Mage::getBaseUrl('media').$url);
    	}
    }
    
    public function getTitleDescription(){
    	$offer = $this->getOffer();
    	if (!$offer || !$offer->getId() || !$offer->getDiscount()) return array();
    	
    	if ($offer->getDiscountMethod() == Magestore_Customerreward_Helper_Offer::OFFER_METHOD_DISCOUNT){
    		$currentMoney = Mage::app()->getStore()->convertPrice($offer->getDiscount());
    		$currencyText = Mage::app()->getStore()->formatPrice($currentMoney,false);
    		$discountText = $this->__('discount');
    	}else{
    		$discountText = $this->__('Reward');
    		$currencyText = $this->__('%.0f point(s)',$offer->getDiscount());
    	}
        
        if (Mage::helper('customerreward')->getReferConfig('coupon'))
            $offerText = $this->__('coupon');
        else
            $offerText = $this->__('special friends only offer');
    	
    	if ($offer->getDiscountType() == Magestore_Customerreward_Helper_Offer::OFFER_TYPE_FIXED){
    		
    		$title = $this->__('Get %s %s at the %s',$currencyText,$discountText,$this->getStoreName());
    		$description = $this->__('Get %s %s when purchase using this %s!',$currencyText,$discountText,$offerText);
    	}else{
    		$title = $this->__('%s up to %.0f%% for each order at %s',$discountText,$offer->getDiscount(),$this->getStoreName());
    		$description = $this->__('Get %s as %.0f%% when purchase using this %s!',$discountText,$offer->getDiscount(),$offerText);
    	}
    	if ($offer->getToDate())
    		$description .= $this->__(' This %s is only valid until %s',$discountText,$this->getToDate($offer));
    	return array('title'=>$title,'description'=>$description);
    }
    
    public function getTitleDescriptionHtml(){
    	$offer = $this->getOffer();
    	if (!$offer || !$offer->getId() || !$offer->getDiscount()) return array();
    	
    	if ($offer->getDiscountMethod() == Magestore_Customerreward_Helper_Offer::OFFER_METHOD_DISCOUNT){
    		$currentMoney = Mage::app()->getStore()->convertPrice($offer->getDiscount());
    		$currencyText = Mage::app()->getStore()->formatPrice($currentMoney);
    		$discountText = $this->__('discount');
    	}else{
    		$discountText = $this->__('Reward');
    		$currencyText = $this->__('%.0f point(s)',$offer->getDiscount());
    	}
        
        if (Mage::helper('customerreward')->getReferConfig('coupon'))
            $offerText = $this->__('coupon');
        else
            $offerText = $this->__('special friends only offer');
    	
    	if ($offer->getDiscountType() == Magestore_Customerreward_Helper_Offer::OFFER_TYPE_FIXED){
    		$title = $this->__('Get <strong class="customerreward-money">%s</strong> %s at the %s',$currencyText,$discountText,$this->getStoreName());
    		$description = $this->__('Get <strong class="customerreward-money">%s</strong> %s when purchase using this %s!',$currencyText,$discountText,$offerText);
    	}else{
    		$title = $this->__('%s up to <strong class="customerreward-money">%.0f%%</strong> for each order at %s',$discountText,$offer->getDiscount(),$this->getStoreName());
    		$description = $this->__('Get %s up to <strong class="customerreward-money">%.0f%%</strong> when purchase using this %s!',$discountText,$offer->getDiscount(),$offerText);
    	}
    	return array('title'=>$title,'description'=>$description);
    }
    
    public function getToDate($offer){
    	return Mage::getModel('core/date')->date('M d, Y',$offer->getToDate());
    }
    
    public function getProceedUrl(){
    	return Mage::getUrl('customerreward/index/offer');
    }
    
    public function getBaseUrl(){
    	return Mage::getBaseUrl();
    }
    
    public function getStoreName(){
    	return Mage::app()->getStore()->getFrontendName();
    }
    
    public function getConfigEmail(){
    	return Mage::getStoreConfig('trans_email/ident_support/email');
    }
    
    public function getConfigPhone(){
    	return Mage::getStoreConfig('general/store_information/phone');
    }
}