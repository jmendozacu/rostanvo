<?php
class Magestore_Customerreward_Block_Adminhtml_Sales_Order_Offer extends Mage_Adminhtml_Block_Sales_Order_Totals_Item
{
	public function initTotals(){
		$orderTotalsBlock = $this->getParentBlock();
		$order = $orderTotalsBlock->getOrder();
		if ($order->getOfferDiscount()){
			$orderTotalsBlock->addTotal(new Varien_Object(array(
				'code'	=> 'offer',
				'label'	=> $this->__('Offer Discount'),
				'value'	=> $order->getOfferDiscount(),
			)),'subtotal');
		}
	}
}