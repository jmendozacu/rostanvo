<?php

class Magestore_Storepickup_Block_Adminhtml_Store_Edit_Tab_Timeschedule extends Mage_Adminhtml_Block_Widget_Form
{
  protected function _prepareForm()
  {
      $form = new Varien_Data_Form();
      $this->setForm($form);
      $fieldset = $form->addFieldset('timeschedule_form', array('legend'=>Mage::helper('storepickup')->__('Time Schedule')));
     
	 $fieldset->addField('minimum_gap', 'text', array(
          'label'     => Mage::helper('storepickup')->__('Minimum Gap'),
          'note'     => 'The minimum time in minutes to ship ',
          'required'  => true,
          'name'      => 'minimum_gap',
      ));	
	 
      $fieldset->addField('monday_open', 'text', array(
          'label'     => Mage::helper('storepickup')->__('Monday Open Time'),
          'note'      => 'format example 12:30',
          'required'  => false,
          'name'      => 'monday_open',
      ));
	  
	  $fieldset->addField('monday_close', 'text', array(
          'label'     => Mage::helper('storepickup')->__('Monday Close Time'),
          'note'      => 'format example 24:00',
          'required'  => false,
          'name'      => 'monday_close',
      ));
	  
	  $fieldset->addField('tuesday_open', 'text', array(
          'label'     => Mage::helper('storepickup')->__('Tuesday Open Time'),
          'required'  => false,
          'name'      => 'tuesday_open',
      ));
	  
	  $fieldset->addField('tuesday_close', 'text', array(
          'label'     => Mage::helper('storepickup')->__('Tuesday Close Time'),
          'required'  => false,
          'name'      => 'tuesday_close',
      ));
	  
	  $fieldset->addField('wednesday_open', 'text', array(
          'label'     => Mage::helper('storepickup')->__('Wednesday Open Time'),
          'required'  => false,
          'name'      => 'wednesday_open',
      ));
	  
	  $fieldset->addField('wednesday_close', 'text', array(
          'label'     => Mage::helper('storepickup')->__('Wednesday Close Time'),
          'required'  => false,
          'name'      => 'wednesday_close',
      ));
	  
	  $fieldset->addField('thursday_open', 'text', array(
          'label'     => Mage::helper('storepickup')->__('Thursday Open Time'),
          'required'  => false,
          'name'      => 'thursday_open',
      ));
	  
	  $fieldset->addField('thursday_close', 'text', array(
          'label'     => Mage::helper('storepickup')->__('Thursday Close Time'),
          'required'  => false,
          'name'      => 'thursday_close',
      ));
	  
	  $fieldset->addField('friday_open', 'text', array(
          'label'     => Mage::helper('storepickup')->__('Friday Open Time'),
          'required'  => false,
          'name'      => 'friday_open',
      ));
	  
	  $fieldset->addField('friday_close', 'text', array(
          'label'     => Mage::helper('storepickup')->__('Friday Close Time'),
          'required'  => false,
          'name'      => 'friday_close',
      ));
	  
	  $fieldset->addField('saturday_open', 'text', array(
          'label'     => Mage::helper('storepickup')->__('Saturday Open Time'),
          'required'  => false,
          'name'      => 'saturday_open',
      ));
	  
	  $fieldset->addField('saturday_close', 'text', array(
          'label'     => Mage::helper('storepickup')->__('Saturday Close Time'),
          'required'  => false,
          'name'      => 'saturday_close',
      ));
	  
	  $fieldset->addField('sunday_open', 'text', array(
          'label'     => Mage::helper('storepickup')->__('Sunday Open Time'),
          'required'  => false,
          'name'      => 'sunday_open',
      ));
	  
	  $fieldset->addField('sunday_close', 'text', array(
          'label'     => Mage::helper('storepickup')->__('Sunday Close Time'),
          'required'  => false,
          'name'      => 'sunday_close',
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