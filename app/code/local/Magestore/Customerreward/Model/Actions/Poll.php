<?php

class Magestore_Customerreward_Model_Actions_Poll extends Magestore_Customerreward_Model_Actions_Abstract
{
	protected $_action = 'poll';
	protected $_title = 'Receive point(s) when participate in poll';
	
	protected function _calculateAmount(){
		$amount = (int)Mage::helper('customerreward')->getEarnConfig('poll');
		$amountPerDay = (int)Mage::helper('customerreward')->getEarnConfig('poll_limit');
		
		$amountOfDay = Mage::helper('customerreward')->getAmountOfDay($this->getCustomer()->getId(),$this->getAction(),Mage::getModel('core/date')->gmtTimestamp());
		
		if ($amount > 0
			&& ($amountOfDay + $amount) > $amountPerDay){ 
			if ($amountOfDay < $amountPerDay )
				$amount = $amountPerDay - $amountOfDay;
			else
				$amount = 0;
		}
		
		$this->setAmount($amount);
		return $this;
	}
}