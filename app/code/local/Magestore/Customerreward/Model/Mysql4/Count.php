<?php

class Magestore_Customerreward_Model_Mysql4_Count extends Mage_Core_Model_Mysql4_Abstract
{
    public function _construct()
    {
        $this->_init('customerreward/count', 'id');
    }
}