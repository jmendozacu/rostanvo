<?php

class Magestore_Customerreward_Block_Adminhtml_Transaction_Edit_Form extends Mage_Adminhtml_Block_Widget_Form
{
  protected function _prepareForm()
  {
      if ($data = Mage::getSingleton('adminhtml/session')->getFormData()){
          $transaction = Mage::getModel('customerreward/transaction')->load($data['transaction_id']);
          Mage::getSingleton('adminhtml/session')->setFormData(null);
      } elseif ( Mage::registry('transaction_data')){
          $transaction = Mage::registry('transaction_data');
      }
      
      $form = new Varien_Data_Form();
      
      $fieldset = $form->addFieldset('transaction_form', array('legend'=>Mage::helper('customerreward')->__('Transaction information')));
     
      $fieldset->addField('title', 'note', array(
          'label'     => Mage::helper('customerreward')->__('Title'),
          'text'	  => $transaction->getActionInstance()->getTitleHtml(true),
      ));
      
      $customer = $transaction->getCustomer();
      $fieldset->addField('customer_id', 'note', array(
          'label'     => Mage::helper('customerreward')->__('Customer'),
          'text'	  => Mage::helper('customerreward')->__('<a target="_blank" href="%s">%s</a>',$this->getUrl('adminhtml/customer/edit',array('id'=>$customer->getId())),$customer->getName()),
      ));
      
      if ($transaction->getNotice())
	      $fieldset->addField('notice', 'note', array(
	          'label'     => Mage::helper('customerreward')->__('Notice'),
	          'text'	  => $transaction->getNotice(),
	      ));

      $fieldset->addField('points_change', 'note', array(
          'label'     => Mage::helper('customerreward')->__('Point(s) change'),
          'text'  	  => $transaction->getPointsChange(),
	  ));
	  
	  if ($transaction->getPointsSpent())
		  $fieldset->addField('points_spent', 'note', array(
	          'label'     => Mage::helper('customerreward')->__('Point(s) spent'),
	          'text'  	  => $transaction->getPointsSpent(),
		  ));
	  
	  if ($transaction->getExpirationDate())
		  $fieldset->addField('expiration_date', 'note', array(
	          'label'     => Mage::helper('customerreward')->__('Expiration date'),
	          'text'  	  => Mage::getModel('core/date')->date('M d, Y',$transaction->getExpirationDate()),
		  ));
	  
	  $fieldset->addField('is_expired', 'note', array(
          'label'     => Mage::helper('customerreward')->__('Expired'),
          'text'  	  => $transaction->getIsExpired() ? Mage::helper('customerreward')->__('Yes') : Mage::helper('customerreward')->__('No'),
	  ));
	  
	  $fieldset->addField('	store_id', 'note', array(
          'label'     => Mage::helper('customerreward')->__('Store View'),
          'text'  	  => Mage::app()->getStore($transaction->getStoreId())->getName(),
	  ));
      
      $form->setUseContainer(true);
      $this->setForm($form);
      return parent::_prepareForm();
  }
}