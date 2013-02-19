<?php

class Magestore_Customerreward_Model_Actions_Newsletter extends Magestore_Customerreward_Model_Actions_Abstract
{
	protected $_action = 'newsletter';
	protected $_title = 'Receive point(s) when signup newsletter';
	
	protected function _calculateAmount(){
		$this->setAmount((int)Mage::helper('customerreward')->getEarnConfig('newsletter'));
		return $this;
	}
}