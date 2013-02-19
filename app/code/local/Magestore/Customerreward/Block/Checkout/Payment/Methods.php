<?php
class Magestore_Customerreward_Block_Checkout_Payment_Methods extends Mage_Checkout_Block_Onepage_Payment_Methods
{
	private $_money_per_point = 0;
	
	public function getMoneyPerPoint(){
		return $this->_money_per_point;
	}
	
	protected function _construct(){
		parent::_construct();
    	if ($customer = $this->getCustomer())
			$this->_money_per_point = Mage::getModel('customerreward/rate')->getPointToMoneyRate($customer->getWebsiteId(),$customer->getGroupId());
	}
	
	protected function _toHtml(){
		$this->setTemplate('customerreward/checkout/payment/methods.phtml');
		if ($this->getRequest()->getModuleName() == 'onestepcheckout')
			$this->setTemplate('customerreward/checkout/payment/onestep.phtml');
		return parent::_toHtml();
	}
	
	public function getBalance(){
		if ($this->getCustomer())
    		return (int)Mage::getModel('customerreward/customer')->loadByCustomerId(Mage::getSingleton('customer/session')->getCustomerId())->getData('total_points');
    }
    
    public function getCurrentCurrencyText($money){
    	$currentMoney = Mage::app()->getStore()->convertPrice($money);
    	return Mage::app()->getStore()->formatPrice($currentMoney);
    }
    
    public function getMoneyBalance(){
    	$balance = $this->getBalance();
    	$money = $balance * $this->getMoneyPerPoint();
    	$moneyText = $this->getCurrentCurrencyText($money);
    	if ($moneyText){
    		return ' ('.$moneyText.')';
    	}
    	return '';
    }
    
    public function getMaxPointsForOrder(){
    	if ($this->getGrandTotal() > $this->getBalance()){
    		return $this->getBalance();
    	}else{
    		return $this->getGrandTotal();
    	}
    }
    
    public function getGrandTotal(){
    	if ($this->getMoneyPerPoint())
    		return ceil($this->getQuote()->getBaseGrandTotal()/$this->getMoneyPerPoint());
    }
    
    protected function getCustomer(){
    	$customer = Mage::getSingleton('customer/session')->getCustomer();
    	if ($customer->getId() > 0) return $customer;
    	return null;
    }
    
    public function customerrewardEnabled(){
    	return ($this->getCustomer()
    		&& !Mage::helper('customerreward')->isDisabled()
			&& $this->getMoneyPerPoint()
			&& $this->getBalance()
			&& $this->getBalance() >= (int)Mage::helper('customerreward')->getEarnConfig('min'));
    }
}