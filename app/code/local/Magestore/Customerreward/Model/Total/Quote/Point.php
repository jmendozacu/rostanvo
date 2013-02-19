<?php

class Magestore_Customerreward_Model_Total_Quote_Point extends Mage_Sales_Model_Quote_Address_Total_Abstract
{
	public function __construct(){
		$this->setCode('point');
	}
	
	public function collect(Mage_Sales_Model_Quote_Address $address){
		$quote = $address->getQuote();
		$session = Mage::getSingleton('checkout/session');
		$customer = Mage::getSingleton('customer/session')->getCustomer();
		
		$params = Mage::app()->getRequest()->getParam('payment');
		if ($params['use_point'] != null
			|| $params['point_amount'] != null)
			$session->setData('use_point',$params['use_point'])
				->setData('point_amount',$params['point_amount']);
		
		if ($session->getData('use_point') && $address->getBaseGrandTotal() && $customer->getId()){
			$pointAmount = $session->getData('point_amount');
			
			$rate = Mage::getModel('customerreward/rate')->getPointToMoneyRate($customer->getWebsiteId(),$customer->getGroupId());;
			
			$moneyBase = $pointAmount * $rate;
			$currentMoney = Mage::app()->getStore()->convertPrice($moneyBase);
			
			$baseSubtotalWithDiscount = $address->getData('base_subtotal') + $address->getData('base_discount_amount');
			$subtotalWithDiscount = $address->getData('subtotal') + $address->getData('discount_amount');
			
			if ($moneyBase < $baseSubtotalWithDiscount){
				$address->setBaseGrandTotal($address->getBaseGrandTotal()-$moneyBase);
				$address->setGrandTotal($address->getGrandTotal()-$currentMoney);
				
				$address->setMoneyBase($moneyBase);
				$address->setCurrentMoney($currentMoney);
				
				$quote->setMoneyBase($moneyBase);
				$quote->setCurrentMoney($currentMoney);
			}else{
				$pointUsed = ceil($baseSubtotalWithDiscount / $rate);
				$moneyBaseUsed = $pointUsed * $rate;
				$currentMoneyUsed = Mage::app()->getStore()->convertPrice($moneyBaseUsed);
				$session->setData('point_amount',$pointUsed);
				
				$address->setBaseGrandTotal($address->getBaseGrandTotal()-$baseSubtotalWithDiscount);
				$address->setGrandTotal($address->getGrandTotal()-$subtotalWithDiscount);
				
				$address->setMoneyBase($moneyBaseUsed);
				$address->setCurrentMoney($currentMoneyUsed);
				
				$quote->setMoneyBase($moneyBaseUsed);
				$quote->setCurrentMoney($currentMoneyUsed);
			}
		}
		
		return $this;
	}
	
	public function fetch(Mage_Sales_Model_Quote_Address $address){
		$session = Mage::getSingleton('checkout/session');
		
		if ($currentMoney = $address->getCurrentMoney()){
			$pointAmount = $session->getData('point_amount');
			if ($pointAmount){
				$title = Mage::helper('customerreward')->__('Use point (%s point(s))',$pointAmount);
			}else{
				$title = Mage::helper('customerreward')->__('Use point(s) on spend');
			}
			$address->addTotal(array(
				'code'	=> $this->getCode(),
				'title'	=> $title,
				'value'	=> -$currentMoney,
			));
		}
		return $this;
	}
}