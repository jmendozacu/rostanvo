<?php
class Magestore_Customerreward_Block_Sales_Order_Totals extends Mage_Core_Block_Template
{
	public function initTotals(){
		$orderTotalsBlock = $this->getParentBlock();
		$order = $orderTotalsBlock->getOrder();
		if ($order->getCurrentMoney()){
			$orderTotalsBlock->addTotal(new Varien_Object(array(
				'code'	=> 'point',
				'label'	=> $this->__('Use point(s) on spend'),
				'value'	=> $order->getCurrentMoney(),
			)),'subtotal');
		}
	}
}