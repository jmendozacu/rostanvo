<?php

class Magestore_Storepickup_Block_Adminhtml_Store_Edit_Tab_Contactform extends Mage_Adminhtml_Block_Widget_Form
{
  protected function _prepareForm()
  {
      $form = new Varien_Data_Form();
      $this->setForm($form);
      $fieldset = $form->addFieldset('store_form', array('legend'=>Mage::helper('storepickup')->__('Contact information')));
     
      $fieldset->addField('store_manager', 'text', array(
          'label'     => Mage::helper('storepickup')->__('Store Manager'),
          'class'     => 'required-entry',
          'required'  => true,
          'name'      => 'store_manager',
      ));	  
	  	  
      $fieldset->addField('store_phone', 'text', array(
          'label'     => Mage::helper('storepickup')->__('Phone Number'),
          'class'     => 'required-entry',
          'required'  => true,
          'name'      => 'store_phone',
      ));	

      $fieldset->addField('store_email', 'text', array(
          'label'     => Mage::helper('storepickup')->__('Email Address'),
          'class'     => 'required-entry',
          'required'  => true,
          'name'      => 'store_email',
      ));	

      $fieldset->addField('store_fax', 'text', array(
          'label'     => Mage::helper('storepickup')->__('Fax Number'),
          'name'      => 'store_fax',
      ));		  

	  
      if ( Mage::getSingleton('adminhtml/session')->getStoreData() )
      {
          $form->setValues(Mage::getSingleton('adminhtml/session')->getStoreData());
          Mage::getSingleton('adminhtml/session')->setStoreData(null);
      } elseif ( Mage::registry('store_data') ) {
          $form->setValues(Mage::registry('store_data')->getData());
      }
      return parent::_prepareForm();
  }
}