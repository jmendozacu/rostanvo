<?php

class Magestore_Customerreward_Model_Customer extends Mage_Core_Model_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('customerreward/customer');
    }
    
    public function getCustomer(){
    	return Mage::getModel('customer/customer')->load($this->getCustomerId());
    }
    
    public function loadByCustomerId($customerId){
    	return $this->load($customerId,'customer_id');
    }
}