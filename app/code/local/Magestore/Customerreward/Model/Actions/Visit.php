<?php

class Magestore_Customerreward_Model_Actions_Visit extends Magestore_Customerreward_Model_Actions_Abstract
{
	protected $_action = 'visit';
	protected $_title = 'Receive point(s) when your referal click to link';
	
	protected function _calculateAmount(){
		$count = $this->getActionObject();
		$customer = $this->getCustomer();
		
		$visitToPoint = Mage::getModel('customerreward/rate')->getVisitToPoint($customer->getWebsiteId(),$customer->getGroupId());
		
		$deltaClick = $count->getVisitCount() - $count->getSpentVisitCount();
		if ($deltaClick >= $visitToPoint['money'] && $visitToPoint['money'] > 0)
			$this->setAmount(floor($deltaClick * $visitToPoint['points'] / $visitToPoint['money']));
		
		return $this;
	}
	
	public function additionAction(){
		if ($this->getAmount()){
			$count = $this->getActionObject();
			$count->setSpentVisitCount($count->getVisitCount());
		}
		return parent::additionAction();
	}
}