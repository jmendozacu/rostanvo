<?php

class Magestore_Customerreward_Model_Count extends Mage_Core_Model_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('customerreward/count');
    }
    
    public function getHashKey(){
    	return strtr(base64_encode(microtime().','.$this->getId().','.$this->getCustomerId().','.$this->getOfferId()),'+/=','-_,');
    }
    
    public function loadByKey($key){
    	return $this->load($key,'key');
    }
    
    protected function _beforeSave(){
        if (!$this->getData('coupon'))
            $this->setData('coupon',Mage::helper('customerreward')->getReferConfig('pattern'));
        if ($this->couponIsExpression())
            $this->setData('coupon',$this->_getCouponCode());
        if (!$this->getData('coupon'))
            $this->setData('coupon',strtr(base64_encode(microtime()),'+/=,','ACTZ'));
        return parent::_beforeSave();
    }
    
    public function couponIsExpression(){
        return Mage::helper('customerreward/coupon')->isExpression($this->getData('coupon'));
    }
    
    protected function _getCouponCode(){
    	$code = Mage::helper('customerreward/coupon')->calcCode($this->getData('coupon'));
    	$times = 10;
    	while(Mage::getModel('customerreward/count')->loadByCoupon($code)->getId() && $times){
    		$code = Mage::helper('customerreward/coupon')->calcCode($this->getData('coupon'));
    		$times--;
    		if ($times == 0)
                $code = '';
    	}
    	return $code;
    }
    
    public function loadByCoupon($code){
        return $this->load($code,'coupon');
    }
    
    public function getCoupon(){
        if (!$this->getData('coupon') && $this->getId()){
            $offer = Mage::getModel('customerreward/offer')->load($this->getOfferId());
            $count = Mage::getModel('customerreward/count')->load($this->getId());
            if (Mage::helper('customerreward/coupon')->isExpression($offer->getCoupon()))
                $count->setData('coupon',$offer->getCoupon());
            try{
                $count->setId($this->getId())->save();
            }catch(Exception $e){}
            $this->setData('coupon',$count->getData('coupon'));
        }
        return $this->getData('coupon');
    }
    
    public function validateCount(){
        if (!$this->getId()) return false;
        if (!Mage::helper('customerreward')->getReferConfig('coupon')) return true;
        
        $offer = Mage::getModel('customerreward/offer')->loadByCount($this);
		if (!$offer->getId()) return false;
        if ($offer->getUsesPerCoupon() && $this->getUsed() >= $offer->getUsesPerCoupon()) return false;
        
        $countCustomer = Mage::getModel('customerreward/count_customer')->loadByCount($this->getId());
        if ($offer->getUsesPerCustomer() && $countCustomer->getUsed() >= $offer->getUsesPerCustomer()) return false;
        
        return true;
    }
}