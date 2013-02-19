<?php

class Magestore_Customerreward_Model_Actions_Uniqueclick extends Magestore_Customerreward_Model_Actions_Abstract
{
	protected $_action = 'uniqueclick';
	protected $_title = 'Receive point(s) when your referal click to your link';
	
	protected function _calculateAmount(){
		$count = $this->getActionObject();
		$customer = $this->getCustomer();
		
		$clickToPoint = Mage::getModel('customerreward/rate')->getClickToPoint($customer->getWebsiteId(),$customer->getGroupId());
		
		$deltaClick = $count->getUniqueClick () - $count->getSpentUniqueClick();
		if ($deltaClick >= $clickToPoint['money'] && $clickToPoint['money'] > 0)
			$this->setAmount(floor($deltaClick * $clickToPoint['points'] / $clickToPoint['money']));
		
		return $this;
	}
	
	public function additionAction(){
		if ($this->getAmount()){
			$count = $this->getActionObject();
			$count->setSpentUniqueClick($count->getUniqueClick());
		}
		return parent::additionAction();
	}
}