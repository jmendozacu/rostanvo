<?php

class Magestore_Customerreward_Model_Mysql4_Offer_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('customerreward/offer');
    }
    
    public function getAvailableOffer($customer,$order){
    	$createdAt = $order->getCreatedAt();
    	$this->addFieldToFilter('website_ids',array('finset' => $customer->getWebsiteId()))
			->addFieldToFilter('customer_group_ids',array('finset' => $customer->getGroupId()))
			->addFieldToFilter('is_active',1);
		$this->getSelect()->where('(from_date IS NULL) OR (date(from_date) <= date(?))',$createdAt);
		$this->getSelect()->where('(to_date IS NULL) OR (date(to_date) >= date(?))',$createdAt);
		$this->setOrder('sort_order','DESC');
		return $this;
    }
}