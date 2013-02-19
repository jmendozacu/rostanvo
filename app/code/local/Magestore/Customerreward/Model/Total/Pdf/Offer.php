<?php

class Magestore_Customerreward_Model_Total_Pdf_Offer extends Mage_Sales_Model_Order_Pdf_Total_Default
{
	public function getTotalsForDisplay(){
		$amount = $this->getAmount();
		$fontSize = $this->getFontSize() ? $this->getFontSize() : 7;
		if(floatval($amount)){
			$amount = $this->getOrder()->formatPriceTxt($amount);
			if ($this->getAmountPrefix()){
				$discount = $this->getAmountPrefix().$discount;
			}
			$totals = array(array(
				'label' => Mage::helper('customerreward')->__('Offer Discount:'),
				'amount' => $amount,
				'font_size' => $fontSize,
				)
			);	
			return $totals;
		}
	}
	
	public function getAmount(){
		return $this->getOrder()->getOfferDiscount();
	}
}