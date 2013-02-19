<?php

class Magestore_Customerreward_Model_Total_Order_Invoice_Offer extends Mage_Sales_Model_Order_Invoice_Total_Abstract
{
	public function collect(Mage_Sales_Model_Order_Invoice $invoice){
		$order = $invoice->getOrder();
		if ($order->getOfferDiscount() && $order->getBaseOfferDiscount()){
			$baseOfferDiscount = $order->getBaseOfferDiscount();
			$offerDiscount = $order->getOfferDiscount();
			if ($invoice->getBaseGrandTotal() + $baseOfferDiscount < 0){
				$invoice->getBaseOfferDiscount(-$invoice->getBaseGrandTotal());
				$invoice->getOfferDiscount(-$invoice->getGrandTotal());
				$invoice->setBaseGrandTotal(0);
				$invoice->setGrandTotal(0);
			}else{
				$invoice->getBaseOfferDiscount($baseOfferDiscount);
				$invoice->getOfferDiscount($offerDiscount);
				$invoice->setBaseGrandTotal($invoice->getBaseGrandTotal()+$baseOfferDiscount);
				$invoice->setGrandTotal($invoice->getGrandTotal()+$offerDiscount);
			}
		}
	}
}