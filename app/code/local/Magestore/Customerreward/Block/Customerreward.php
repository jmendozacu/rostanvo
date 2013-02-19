<?php
class Magestore_Customerreward_Block_Customerreward extends Mage_Core_Block_Template
{
	public function _prepareLayout(){
		return parent::_prepareLayout();
    }
    
    public function getSaveUrl(){
    	return $this->getUrl('*/*/save');
    }
    
    public function getIsNotification(){
    	return Mage::getModel('customerreward/customer')->loadByCustomerId(Mage::getSingleton('customer/session')->getCustomerId())->getData('is_notification');
    }
}