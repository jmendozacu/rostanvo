<?php

class Magestore_Customerreward_Model_Mysql4_Count_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
	protected $_hasOffer = false;
	
    public function _construct()
    {
        parent::_construct();
        $this->_init('customerreward/count');
    }
    
    public function joinOffer(){
    	if ($this->_hasOffer) return $this;
    	$this->_hasOffer = true;
    	$this->getSelect()->joinLeft(array('offer'=>$this->getTable('customerreward/offer')),'main_table.offer_id=offer.offer_id',array('is_active','from_date','to_date','uses_per_coupon','uses_per_customer'));
    	return $this;
    }
}