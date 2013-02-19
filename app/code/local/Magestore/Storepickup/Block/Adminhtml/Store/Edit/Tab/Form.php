<?php

class Magestore_Storepickup_Block_Adminhtml_Store_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form
{
  protected function _prepareForm()
  {
      $form = new Varien_Data_Form();
      $this->setForm($form);
      $fieldset = $form->addFieldset('store_form', array('legend'=>Mage::helper('storepickup')->__('Store information')));
     
	 
      $fieldset->addField('store_name', 'text', array(
          'label'     => Mage::helper('storepickup')->__('Store Name'),
          'class'     => 'required-entry',
          'required'  => true,
          'name'      => 'store_name',
      ));
	  
      $fieldset->addField('status', 'select', array(
          'label'     => Mage::helper('storepickup')->__('Status'),
          'name'      => 'store_status',
          'values'    => array(
              array(
                  'value'     => 1,
                  'label'     => Mage::helper('storepickup')->__('Enabled'),
              ),

              array(
                  'value'     => 2,
                  'label'     => Mage::helper('storepickup')->__('Disabled'),
              ),
          ),
      ));	  
	  
	   $fieldset->addField('address', 'text', array(
          'label'     => Mage::helper('storepickup')->__('Address'),
          'class'     => 'required-entry',
          'required'  => true,
          'name'      => 'address',
		  'style'     => 'width:500px;',
      ));		
	  
      $fieldset->addField('city', 'text', array(
          'label'     => Mage::helper('storepickup')->__('City'),
          'class'     => 'required-entry',
          'required'  => true,		  
          'name'      => 'city',
      ));		

      $fieldset->addField('zipcode', 'text', array(
          'label'     => Mage::helper('storepickup')->__('Zipcode'),
          'class'     => 'required-entry',
          'required'  => true,
          'name'      => 'zipcode',
      ));  	

	  $fieldset->addField('country', 'select', array(
          'label'     => Mage::helper('storepickup')->__('Country'),
          'class'     => 'required-entry',
          'required'  => true,
          'name'      => 'country',
		  'values'    => Mage::helper('storepickup/location')->getOptionCountry(),
      ));	
	
	  $fieldset->addField('stateEl', 'note', array(
          'label'     => Mage::helper('storepickup')->__('State/Province'),
		  'name'	  => 'stateEl',
		  'text'	  => $this->getLayout()->createBlock('storepickup/adminhtml_region')->setTemplate('storepickup/region.phtml')->toHtml(),
	 ));	
		
	  $fieldset->addField('store_latitude', 'text', array(
          'label'     => Mage::helper('storepickup')->__('Store Latitude'),
          'name'      => 'store_latitude',
      ));	
	
	$fieldset->addField('store_longitude', 'text', array(
          'label'     => Mage::helper('storepickup')->__('Store Longitude'),
          'name'      => 'store_longitude',
      ));	  
	  	  
     
      $fieldset->addField('description', 'textarea', array(
          'name'      => 'description',
          'label'     => Mage::helper('storepickup')->__('Description'),
          'title'     => Mage::helper('storepickup')->__('Description'),
          'style'     => 'width:500px; height:150px;',
          'wysiwyg'   => false,
          'required'  => false,
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