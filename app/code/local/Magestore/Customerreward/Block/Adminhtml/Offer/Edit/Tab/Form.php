<?php

class Magestore_Customerreward_Block_Adminhtml_Offer_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form
{
  protected function _prepareForm()
  {
      if ( Mage::getSingleton('adminhtml/session')->getFormData()){
          $data = Mage::getSingleton('adminhtml/session')->getFormData();
          Mage::getSingleton('adminhtml/session')->setFormData(null);
      } elseif ( Mage::registry('offer_data') ){
          $data = Mage::registry('offer_data')->getData();
      }
  	  
      $form = new Varien_Data_Form();
      $form->setHtmlIdPrefix('offer_');
      $this->setForm($form);
      
      $form->addFieldSet('description_fieldset',array('legend'=>Mage::helper('customerreward')->__('Description')))->setRenderer(Mage::getBlockSingleton('adminhtml/widget_form_renderer_fieldset')->setTemplate('customerreward/offer/description.phtml'));
      
      $fieldset = $form->addFieldset('general_fieldset', array('legend'=>Mage::helper('customerreward')->__('General information')));
     
      $fieldset->addField('title', 'text', array(
          'label'     => Mage::helper('customerreward')->__('Title'),
          'title'	  => Mage::helper('customerreward')->__('Title'),
          'class'     => 'required-entry',
          'required'  => true,
          'name'      => 'title',
      ));
      
      $fieldset->addField('description', 'editor', array(
          'label'     => Mage::helper('customerreward')->__('Description'),
          'title'	  => Mage::helper('customerreward')->__('Description'),
          'class'     => 'required-entry',
          'required'  => true,
          'name'      => 'description',
          'wysiwyg'   => true,
      ));
      
      $fieldset->addField('image','image',array(
	  	'name'	=> 'image',
	  	'label' => Mage::helper('customerreward')->__('Image'),
	  	'title'	=> Mage::helper('customerreward')->__('Image'),
	  	'note'	=> Mage::helper('customerreward')->__('Recommend image width is 200px.'),
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
	  
	  $fieldset->addField('coupon','text',array(
	  	'label' => Mage::helper('customerreward')->__('Coupon code Pattern'),
	  	'title'	=> Mage::helper('customerreward')->__('Coupon code Pattern'),
	  	'name'	=> 'coupon',
	  	'note'	=> Mage::helper('customerreward')->__('Empty if using the default configuration. Pattern examples:<br/><strong>[A.8] : 8 alpha chars<br/>[N.4] : 4 numerics<br/>[AN.6] : 6 alphanumeric<br/>REWARD-[A.4]-[AN.6] : REWARD-ADFA-12NF0O'),
	  ));
	  
	  $fieldset->addField('uses_per_coupon','text',array(
	  	'label' => Mage::helper('customerreward')->__('Uses per Coupon'),
	  	'title'	=> Mage::helper('customerreward')->__('Uses per Coupon'),
	  	'name'	=> 'uses_per_coupon',
        'note'  => Mage::helper('customerreward')->__('0 is unlimited'),
	  ));
	  
	  $fieldset->addField('uses_per_customer','text',array(
	  	'label' => Mage::helper('customerreward')->__('Uses per Customer'),
	  	'title'	=> Mage::helper('customerreward')->__('Uses per Customer'),
	  	'name'	=> 'uses_per_customer',
        'note'  => Mage::helper('customerreward')->__('0 is unlimited'),
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