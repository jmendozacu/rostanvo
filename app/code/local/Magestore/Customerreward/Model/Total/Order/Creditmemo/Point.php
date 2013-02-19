<?php

class Magestore_Customerreward_Model_Total_Order_Creditmemo_Point extends Mage_Sales_Model_Order_Creditmemo_Total_Abstract
{
	public function collect(Mage_Sales_Model_Order_Creditmemo $creditmemo){
		$order = $creditmemo->getOrder();
		if ($order->getMoneyBase() && $order->getCurrentMoney()){
			$money = $order->getMoneyBase();
			$currentMoney = $order->getCurrentMoney();
			if ($creditmemo->getBaseGrandTotal() + $money < 0){
				$creditmemo->setMoneyBase(-$creditmemo->getBaseGrandTotal());
				$creditmemo->setCurrentMoney(-$creditmemo->getGrandTotal());
				$creditmemo->setBaseGrandTotal(0);
				$creditmemo->setGrandTotal(0);
			}else{
				$creditmemo->setMoneyBase($money);
				$creditmemo->setCurrentMoney($currentMoney);
				$creditmemo->setBaseGrandTotal($creditmemo->getBaseGrandTotal()+$money);
				$creditmemo->setGrandTotal($creditmemo->getGrandTotal()+$currentMoney);
			}
		}
	}
}