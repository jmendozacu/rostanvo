<?php

class Magestore_Customerreward_Model_Mysql4_Order extends Mage_Core_Model_Mysql4_Abstract
{
    public function _construct()
    {
        $this->_init('customerreward/order', 'order_id');
    }
}