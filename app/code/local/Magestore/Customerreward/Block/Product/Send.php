<?php
class Magestore_Customerreward_Block_Product_Send extends Mage_Core_Block_Template
{
	public function _prepareLayout(){
		return parent::_prepareLayout();
    }
    
    public function getShareUrl(){
    	return Mage::getUrl('customerreward/offer/',array(
			'id'	=> $this->getRequest()->getParam('id'),
			'cat_id'=> $this->getRequest()->getParam('category'),
		));
    }
    
    public function isEnabled(){
    	if (Mage::helper('customerreward')->isDisabled()) return false;
		if(!Mage::helper('magenotification')->checkLicenseKey('Customerreward')){return false;}
    	if (Mage::getSingleton('customer/session')->getCustomerId()){
    		$order = new Varien_Object(array(
    			'created_at'	=> now(),
    			'reward_product_id'	=> $this->getRequest()->getParam('id')
    		));
			$offer = Mage::getModel('customerreward/offer')->loadByCustomerOrder(Mage::getSingleton('customer/session')->getCustomer(),$order);
			if ($offer->getId()) return true;
    	}
    	return false;
    }
}