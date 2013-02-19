<?php

class Magestore_Customerreward_Model_Actions_Cancel extends Magestore_Customerreward_Model_Actions_Abstract
{
	protected $_action = 'cancel';
	protected $_title = 'Receive point(s) that you spent for order %s';
	
	protected function _calculateAmount(){
		$spend = $this->getActionObject();
		$amount = $spend->getPointsChange();
		if ($amount < 0)
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