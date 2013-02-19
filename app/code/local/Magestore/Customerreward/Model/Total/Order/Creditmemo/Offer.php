<?php

class Magestore_Customerreward_Model_Total_Order_Creditmemo_Offer extends Mage_Sales_Model_Order_Creditmemo_Total_Abstract
{
	public function collect(Mage_Sales_Model_Order_Creditmemo $creditmemo){
		$order = $creditmemo->getOrder();
		if ($order->getOfferDiscount() && $order->getBaseOfferDiscount()){
			$baseOfferDiscount = $order->getBaseOfferDiscount();
			$offerDiscount = $order->getOfferDiscount();
			if ($creditmemo->getBaseGrandTotal() + $baseOfferDiscount < 0){
				$creditmemo->getBaseOfferDiscount(-$creditmemo->getBaseGrandTotal());
				$creditmemo->getOfferDiscount(-$creditmemo->getGrandTotal());
				$creditmemo->setBaseGrandTotal(0);
				$creditmemo->setGrandTotal(0);
			}else{
				$creditmemo->getBaseOfferDiscount($baseOfferDiscount);
				$creditmemo->getOfferDiscount($offerDiscount);
				$creditmemo->setBaseGrandTotal($creditmemo->getBaseGrandTotal()+$baseOfferDiscount);
				$creditmemo->setGrandTotal($creditmemo->getGrandTotal()+$offerDiscount);
			}
		}
	}
}