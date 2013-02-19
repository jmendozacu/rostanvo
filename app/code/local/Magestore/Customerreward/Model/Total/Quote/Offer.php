<?php

class Magestore_Customerreward_Model_Total_Quote_Offer extends Mage_Sales_Model_Quote_Address_Total_Abstract
{
	public function __construct(){
		$this->setCode('offer');
	}
	
	public function collect(Mage_Sales_Model_Quote_Address $address){
		$count = Mage::getModel('customerreward/count')->loadByKey(Mage::getSingleton('core/cookie')->get('customerreward_offer_key'));
		if (!$count || !$count->getId() || !$count->validateCount()) return $this;
		$offer = Mage::getModel('customerreward/offer')->loadByCount($count);
		if ($offer && $offer->getId()){
			if ($offer->getDiscountMethod() != Magestore_Customerreward_Helper_Offer::OFFER_METHOD_DISCOUNT
				|| $offer->getDiscountShow() != Magestore_Customerreward_Helper_Offer::SHOW_OFFER_IN_CART) return $this;
			
			$productIds = $offer->getProductIds();
			$items = $address->getAllItems();
			$baseDiscount = 0;
			foreach ($items as $item){
				if (in_array($item->getProduct()->getId(),$productIds)){
					if ($offer->getDiscountType() == Magestore_Customerreward_Helper_Offer::OFFER_TYPE_FIXED)
						$discount = ($item->getProduct()->getFinalPrice() < $offer->getDiscount()) ? $item->getProduct()->getFinalPrice() : $offer->getDiscount();
					else
						$discount = $item->getProduct()->getFinalPrice() * $offer->getDiscount() / 100;
					$baseDiscount += $item->getQty() * $discount;
				}
			}
			if ($baseDiscount <= 0) return $this;
			$discount = Mage::app()->getStore()->convertPrice($baseDiscount);
			
			$address->setOfferDiscount(-$discount);
			$address->setBaseOfferDiscount(-$baseDiscount);
			$address->setGrandTotal($address->getGrandTotal() + $address->getOfferDiscount());
			$address->setBaseGrandTotal($address->getBaseGrandTotal() + $address->getBaseOfferDiscount());
			
			$session = Mage::getSingleton('checkout/session');
			$session->setData('offer_discount',-$discount);
			$session->setData('base_offer_discount',-$baseDiscount);
		}
		return $this;
	}
	
	public function fetch(Mage_Sales_Model_Quote_Address $address){
		$amount = $address->getOfferDiscount();
		if ($amount != 0){
			$title = Mage::helper('customerreward')->__('Offer Discount');
			$address->addTotal(array(
				'code'	=> $this->getCode(),
				'title'	=> $title,
				'value'	=> $amount
			));
		}
		return $this;
	}
}