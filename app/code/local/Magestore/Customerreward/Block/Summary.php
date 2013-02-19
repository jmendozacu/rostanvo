<?php
class Magestore_Customerreward_Block_Summary extends Mage_Core_Block_Template
{
	public function _prepareLayout(){
		return parent::_prepareLayout();
    }
    
    public function getBalance(){
    	return (int)Mage::getModel('customerreward/customer')->loadByCustomerId(Mage::getSingleton('customer/session')->getCustomerId())->getData('total_points');
    }
    
    public function getMoneyBalance(){
    	$balance = $this->getBalance();
    	$customer = Mage::getSingleton('customer/session')->getCustomer();
    	$money = $balance * Mage::getModel('customerreward/rate')->getPointToMoneyRate($customer->getWebsiteId(),$customer->getGroupId());
    	$moneyText = $this->getCurrentCurrencyText($money);
    	if ($moneyText){
    		return ' ('.$moneyText.')';
    	}
    	return '';
    }
    
    public function getCurrentCurrencyText($money){
    	$currentMoney = Mage::app()->getStore()->convertPrice($money);
    	return Mage::app()->getStore()->formatPrice($currentMoney);
    }
    
    public function showExchangeRate(){
    	$customer = Mage::getSingleton('customer/session')->getCustomer();
    	$websiteId = $customer->getWebsiteId();
    	$groupId = $customer->getGroupId();
    	$rate = Mage::getModel('customerreward/rate');
    	
    	$pointToMoney = $rate->getPointToMoney($websiteId,$groupId);
    	$moneyToPoint = $rate->getMoneyToPoint($websiteId,$groupId);
    	$visitToPoint = $rate->getVisitToPoint($websiteId,$groupId);
    	$clickToPoint = $rate->getClickToPoint($websiteId,$groupId);
    	
    	$html = '';
    	if ($pointToMoney['points'])
    		$html .= $this->__('Each <strong class="customerreward-money">%s point(s)</strong> can be redeemed for <strong class="customerreward-money">%s</strong>.<br />',$pointToMoney['points'],$this->getCurrentCurrencyText($pointToMoney['money']));
    	if ($moneyToPoint['money'])
    		$html .= $this->__('Each <strong class="customerreward-money">%s</strong> spent for your order will earn <strong class="customerreward-money">%s point(s)</strong>.<br />',$this->getCurrentCurrencyText($moneyToPoint['money']),$moneyToPoint['points']);
    	if ($visitToPoint['money'] && Mage::helper('customerreward')->getReferConfig('visit'))
    		$html .= $this->__('Each <strong class="customerreward-money">%.0f visitor(s)</strong> from your referral link, you will earn <strong class="customerreward-money">%s</strong> point(s).<br />',$visitToPoint['money'],$visitToPoint['points']);
    	if ($clickToPoint['money'] && Mage::helper('customerreward')->getReferConfig('uniqueclick'))
    		$html .= $this->__('Each <strong class="customerreward-money">%.0f unique click(s)</strong> of referral link, you will earn <strong class="customerreward-money">%s</strong> point(s).<br />',$clickToPoint['money'],$clickToPoint['points']);
    	return $html;
    }
    
    public function showLimit(){
    	$max = (int)Mage::helper('customerreward')->getEarnConfig('max');
    	$min = (int)Mage::helper('customerreward')->getEarnConfig('min');
    	$html = '';
    	if ($max)
    		$html .= $this->__('Maximum of your balance: <strong class="customerreward-money">%s point(s)</strong>.<br />',$max);
   		if ($min)
   			$html .= $this->__('Reach <strong class="customerreward-money">%s point(s)</strong> to start using your balance for your purchase.<br />',$min);
   		return $html;
    }
    
    public function getExpirationDate(){
    	return (int)Mage::helper('customerreward')->getEarnConfig('expire');
    }
}