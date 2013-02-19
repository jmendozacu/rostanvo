<?php

class Magestore_Customerreward_Model_Mysql4_Count_Customer extends Mage_Core_Model_Mysql4_Abstract
{
    public function _construct(){
        $this->_init('customerreward/count_customer', 'id');
    }
}