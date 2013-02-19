<?php

class Magestore_Customerreward_Model_Actions_Admin extends Magestore_Customerreward_Model_Actions_Abstract
{
	protected $_action = 'admin';
	protected $_title = 'Account balance is changed by Admin';
	
	protected function _calculateAmount(){
		$changeBalance = $this->getActionObject();
		if ($changeBalance->getBalanceChange()){
			$this->setAmount($changeBalance->getBalanceChange());
		}
		return $this;
	}
}