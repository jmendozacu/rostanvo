<?php

class Magestore_Customerreward_Model_Count_Customer extends Mage_Core_Model_Abstract
{
	public function _construct(){
		parent::_construct();
		$this->_init('customerreward/count_customer');
	}
	
	public function loadByCustomer($customerId, $countId){
		$item = $this->getCollection()
			->addFieldToFilter('count_id',$countId)
			->addFieldToFilter('customer_id',$customerId)
			->getFirstItem();
		$this->setData($item->getData())
			->setData('count_id',$countId)
			->setData('customer_id',$customerId)
			->setId($item->getId());
		return $this;
	}
	
	public function loadByCount($countId){
		$customerId = Mage::getSingleton('customer/session')->getCustomer()->getId();
		if ($customerId)
			$this->loadByCustomer($customerId,$countId);
		return $this;
	}
}