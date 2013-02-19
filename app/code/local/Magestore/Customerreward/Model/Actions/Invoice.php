<?php

class Magestore_Customerreward_Model_Actions_Invoice extends Magestore_Customerreward_Model_Actions_Abstract
{
	protected $_action = 'invoice';
	protected $_title = 'Receive point(s) from Order %s which you placed';
	
	protected function _calculateAmount(){
		$order = $this->getActionObject();
		if ($baseGrandTotal = $order->getBaseGrandTotal()){
			$customer = $this->getCustomer();
			$moneyToPoint = Mage::getModel('customerreward/rate')->getMoneyToPoint($customer->getWebsiteId(),$customer->getGroupId());
			
			if ($moneyToPoint['money']){
				$amount = floor($baseGrandTotal * $moneyToPoint['points'] / $moneyToPoint['money']);
				$this->setAmount($amount);
			}
		}
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