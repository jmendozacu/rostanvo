<?php

class Magestore_Customerreward_Model_Order extends Mage_Core_Model_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('customerreward/order');
    }
    
    public function loadByOrderIncrementId($orderIncrementId){
    	return $this->load($orderIncrementId,'order_increment_id');
    }
}