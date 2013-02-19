<?php

class Magestore_Customerreward_Block_Adminhtml_Customer_Tab_Customerreward extends Mage_Adminhtml_Block_Widget_Form implements Mage_Adminhtml_Block_Widget_Tab_Interface
{
	protected $_customer_reward;
	
	protected function _prepareForm(){
		$form = new Varien_Data_Form();
		$this->setForm($form);
		$fieldset = $form->addFieldset('customerreward_fieldset', array('legend'=>Mage::helper('customerreward')->__('Reward information')));
		
		$customerReward = $this->getCustomerReward();
		
		$fieldset->addField('total_points', 'note', array(
			'label'	=> Mage::helper('customerreward')->__('Account Balance'),
			'title'	=> Mage::helper('customerreward')->__('Account Balance'),
			'text'	=> Mage::helper('customerreward')->__('<strong>%s</strong> point(s)',$customerReward->getTotalPoints()),
		));
		
		$fieldset->addField('change_balance', 'text', array(
			'label'	=> Mage::helper('customerreward')->__('Change Balance'),
			'title'	=> Mage::helper('customerreward')->__('Change Balance'),
			'name'	=> 'change_balance',
			'note'	=> Mage::helper('customerreward')->__('Add or subtract customer\'s balance. For ex: 99 or -99 points.')
		));
		
		$fieldset->addField('is_notification', 'checkbox', array(
			'label'	=> Mage::helper('customerreward')->__('Email notification'),
			'title'	=> Mage::helper('customerreward')->__('Email notification'),
			'name'	=> 'is_notification',
			'checked'	=> $customerReward->getIsNotification(),
			'value'	=> 1,
		));
	}
	
	public function getCustomerReward(){
		if (is_null($this->_customer_reward)){
			$customerId = Mage::registry('current_customer')->getId();
			$this->_customer_reward = Mage::getModel('customerreward/customer')->loadByCustomerId($customerId);
		}
		return $this->_customer_reward;
	}
	
	public function getTabLabel(){
		return Mage::helper('customerreward')->__('Customer Reward');
	}
	
	public function getTabTitle(){
		return Mage::helper('customerreward')->__('Customer Reward');
	}
	
	public function canShowTab(){
		if ($this->getCustomerReward()->getId()){
			return true;
		}
		return false;
	}
	
	public function isHidden(){
		if ($this->getCustomerReward()->getId()){
			return false;
		}
		return true;
	}
}