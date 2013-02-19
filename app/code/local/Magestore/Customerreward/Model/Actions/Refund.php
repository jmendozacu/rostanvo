<?php

class Magestore_Customerreward_Model_Actions_Refund extends Magestore_Customerreward_Model_Actions_Abstract
{
	protected $_action = 'refund';
	protected $_title = 'Refund point(s) that you receive from order %s';
	
	protected function _calculateAmount(){
		$order = $this->getActionObject();
		$actions = array('invoice','rule','cashback');
		$amount = 0;
		$pointsChange = 0;
		
		foreach ($actions as $action){
			$transaction = Mage::getModel('customerreward/transaction')->loadByOrder($order,$action);
			if ($transaction->getIsExpired() == 2) return $this;
			if ($transaction->getId()){
				$amount += $transaction->getPointsSpent();
				$pointsChange += $transaction->getPointsChange();
				$transaction->setIsExpired(2)->save();
			}
		}
		
		if ($pointsChange){
			$rewardCustomer = Mage::getModel('customerreward/customer')->loadByCustomerId($this->getCustomer()->getId());
			if ($rewardCustomer->getId())
				$rewardCustomer->setTotalPoints($rewardCustomer->getTotalPoints()-$pointsChange + $amount)->save();
		}
		
		if ($amount > 0)
			$this->setAmount(-$amount);
		return $this;
	}
	
	public function getTitle(){
		return Mage::helper('customerreward')->__($this->_title,$this->_extraContent['order_increment_id']);
	}
	
	public function getTitleHtml($isAdminArea = false){
		parse_str($this->getTransaction()->getData('extra_content'),$order);
		if ($isAdminArea){
			$url = Mage::getUrl('adminhtml/sales_order/view',array('order_id' => $order['order_id']));
		}else{
			$url = Mage::getUrl('sales/order/view',array('order_id' => $order['order_id']));
		}
		return Mage::helper('customerreward')->__($this->_title,'<a href="'.$url.'">#'.$order['order_increment_id'].'</a>');
	}
}