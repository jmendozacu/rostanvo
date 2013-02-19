<?php

class Magestore_Customerreward_Block_Adminhtml_Rule_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form
{
  protected function _prepareForm()
  {
      if ( Mage::getSingleton('adminhtml/session')->getFormData()){
          $data = Mage::getSingleton('adminhtml/session')->getFormData();
          Mage::getSingleton('adminhtml/session')->setFormData(null);
      } elseif ( Mage::registry('rule_data') ){
          $data = Mage::registry('rule_data')->getData();
      }
  	  
      $form = new Varien_Data_Form();
      $form->setHtmlIdPrefix('rule_');
      $this->setForm($form);
      $fieldset = $form->addFieldset('general_fieldset', array('legend'=>Mage::helper('customerreward')->__('General information')));
     
      $fieldset->addField('title', 'text', array(
          'label'     => Mage::helper('customerreward')->__('Title'),
          'title'	  => Mage::helper('customerreward')->__('Title'),
          'class'     => 'required-entry',
          'required'  => true,
          'name'      => 'title',
      ));
      
      $fieldset->addField('description','editor',array(
	  	'name'	=> 'description',
	  	'label' => Mage::helper('customerreward')->__('Description'),
	  	'title'	=> Mage::helper('customerreward')->__('Description'),
	  	'style'	=> 'width:276px;height:100px;',
	  ));

      if (!Mage::app()->isSingleStoreMode()) {
            $fieldset->addField('website_ids', 'multiselect', array(
                'name'      => 'website_ids[]',
                'label'     => Mage::helper('customerreward')->__('Websites'),
                'title'     => Mage::helper('customerreward')->__('Websites'),
                'required'  => true,
                'values'    => Mage::getSingleton('adminhtml/system_config_source_website')->toOptionArray(),
            ));
        }else {
            $fieldset->addField('website_ids', 'hidden', array(
                'name'      => 'website_ids[]',
                'value'     => Mage::app()->getStore(true)->getWebsiteId()
            ));
            $data['website_ids'] = Mage::app()->getStore(true)->getWebsiteId();
        }
		
      $fieldset->addField('customer_group_ids','multiselect',array(
		'label'		=> Mage::helper('customerreward')->__('Customer groups'),
		'title'		=> Mage::helper('customerreward')->__('Customer groups'),
		'name'		=> 'customer_group_ids',
		'required'	=> true,
		'values'	=> Mage::getResourceModel('customer/group_collection')
						->addFieldToFilter('customer_group_id', array('gt'=> 0))
						->load()
						->toOptionArray()
	  ));
	  
	  $dateFormatIso = Mage::app()->getLocale()->getDateFormat(Mage_Core_Model_Locale::FORMAT_TYPE_SHORT);
	  $fieldset->addField('from_date','date',array(
	  	'name'	=> 'from_date',
	  	'label'	=> Mage::helper('customerreward')->__('Validate from'),
	  	'title' => Mage::helper('customerreward')->__('From date'),
	  	'image'	=> $this->getSkinUrl('images/grid-cal.gif'),
	  	'input_format'	=> Varien_Date::DATE_INTERNAL_FORMAT,
	  	'format'	=> $dateFormatIso,
	  ));
	  
	  $fieldset->addField('to_date','date',array(
	  	'name'	=> 'to_date',
	  	'label'	=> Mage::helper('customerreward')->__('Validate to'),
	  	'title' => Mage::helper('customerreward')->__('To date'),
	  	'image'	=> $this->getSkinUrl('images/grid-cal.gif'),
	  	'input_format'	=> Varien_Date::DATE_INTERNAL_FORMAT,
	  	'format'	=> $dateFormatIso,
	  ));
	  
	  $fieldset->addField('is_active', 'select', array(
          'label'     => Mage::helper('customerreward')->__('Status'),
          'title'     => Mage::helper('customerreward')->__('Status'),
          'name'      => 'is_active',
          'values'    => array(
              array(
                  'value'     => '1',
                  'label'     => Mage::helper('customerreward')->__('Active'),
              ),
              array(
                  'value'     => '0',
                  'label'     => Mage::helper('customerreward')->__('Inactive'),
              ),
          ),
      ));
      
      $fieldset->addField('sort_order','text',array(
	  	'name'	=> 'sort_order',
	  	'label'	=> Mage::helper('customerreward')->__('Priority'),
	  	'title' => Mage::helper('customerreward')->__('Priority'),
	  ));
      
      $form->setValues($data);
      return parent::_prepareForm();
  }
}