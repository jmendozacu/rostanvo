<?php

class Magestore_Customerreward_Block_Adminhtml_Rate_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form
{
  protected function _prepareForm()
  {
  	  if (Mage::getSingleton('adminhtml/session')->getFormData()){
          $data = Mage::getSingleton('adminhtml/session')->getFormData();
          Mage::getSingleton('adminhtml/session')->setFormData(null);
      } elseif ( Mage::registry('rate_data') ) {
          $data = Mage::registry('rate_data')->getData();
      }
  		
      $form = new Varien_Data_Form();
      $this->setForm($form);
      $fieldset = $form->addFieldset('form_fieldset', array('legend'=>Mage::helper('customerreward')->__('Rate information')));
     
     
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
	  
	  $fieldset->addField('points', 'text', array(
          'label'     => Mage::helper('customerreward')->__('Point(s)'),
          'class'     => 'required-entry',
          'required'  => true,
          'name'      => 'points',
      ));
      
      $fieldset->addField('direction', 'select', array(
          'label'     => Mage::helper('customerreward')->__('Direction'),
          'required'  => false,
          'name'      => 'direction',
          'values'	  => array(
		  					array(
							  	'value'	=> Magestore_Customerreward_Model_Rate::POINT_TO_MONEY,
							  	'label'	=> Mage::helper('customerreward')->__('Spend money from point(s)'),
							  ),
		  					array(
							  	'value'	=> Magestore_Customerreward_Model_Rate::MONEY_TO_POINT,
							  	'label'	=> Mage::helper('customerreward')->__('Earn point(s) from money spent'),
							  ),
		  					array(
							  	'value'	=> Magestore_Customerreward_Model_Rate::CLICK_TO_POINT,
							  	'label'	=> Mage::helper('customerreward')->__('Earn point(s) from number of unique clicks'),
							  ),
		  					array(
							  	'value'	=> Magestore_Customerreward_Model_Rate::VISIT_TO_POINT,
							  	'label'	=> Mage::helper('customerreward')->__('Earn point(s) from number of visits'),
							  ),
						  ),
      ));
     
      $fieldset->addField('money', 'text', array(
          'label'     => Mage::helper('customerreward')->__('Money / #Click / #Visit'),
          'class'     => 'required-entry',
          'required'  => true,
          'name'      => 'money',
          'after_element_html'	=> '<strong>['.Mage::getStoreConfig(Mage_Directory_Model_Currency::XML_PATH_CURRENCY_BASE).']</strong> or <strong>#click / #visit</strong>',
      ));

      $fieldset->addField('sort_order', 'text', array(
          'label'     => Mage::helper('customerreward')->__('Priority'),
          'required'  => false,
          'name'      => 'sort_order',
	  ));
	  
      $form->setValues($data);
      return parent::_prepareForm();
  }
}