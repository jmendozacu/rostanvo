<?php

class Magestore_Customerreward_Model_Total_Order_Invoice_Point extends Mage_Sales_Model_Order_Invoice_Total_Abstract
{
	public function collect(Mage_Sales_Model_Order_Invoice $invoice){
		$order = $invoice->getOrder();
		if ($order->getMoneyBase() && $order->getCurrentMoney()){
			$money = $order->getMoneyBase();
			$currentMoney = $order->getCurrentMoney();
			if ($invoice->getBaseGrandTotal() + $money < 0){
				$invoice->setMoneyBase(-$invoice->getBaseGrandTotal());
				$invoice->setCurrentMoney(-$invoice->getGrandTotal());
				$invoice->setBaseGrandTotal(0);
				$invoice->setGrandTotal(0);
			}else{
				$invoice->setMoneyBase($money);
				$invoice->setCurrentMoney($currentMoney);
				$invoice->setBaseGrandTotal($invoice->getBaseGrandTotal()+$money);
				$invoice->setGrandTotal($invoice->getGrandTotal()+$currentMoney);
			}
		}
	}
}