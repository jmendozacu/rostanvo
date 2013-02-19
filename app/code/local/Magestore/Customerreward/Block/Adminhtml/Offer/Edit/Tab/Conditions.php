<?php

class Magestore_Customerreward_Block_Adminhtml_Offer_Edit_Tab_Conditions extends Mage_Adminhtml_Block_Widget_Form
{
  protected function _prepareForm()
  {
      if ( Mage::getSingleton('adminhtml/session')->getFormData()){
          $data = Mage::getSingleton('adminhtml/session')->getFormData();
          $model = Mage::getModel('customerreward/offer')
          		->load($data['offer_id'])
		  		->setData($data);
          Mage::getSingleton('adminhtml/session')->setFormData(null);
      } elseif ( Mage::registry('offer_data')){
          $model = Mage::registry('offer_data');
          $data = $model->getData();
      }
  	  
      $form = new Varien_Data_Form();
      $form->setHtmlIdPrefix('offer_');
      
      $renderer = Mage::getBlockSingleton('adminhtml/widget_form_renderer_fieldset')
            ->setTemplate('promo/fieldset.phtml')
            ->setNewChildUrl($this->getUrl('adminhtml/promo_quote/newConditionHtml/form/offer_conditions_fieldset'));
      
      $fieldset = $form->addFieldset('conditions_fieldset', array('legend'=>Mage::helper('customerreward')->__('Apply the rule only if the following conditions are met (leave blank for all orders)')))->setRenderer($renderer);
      
      $fieldset->addField('conditions','text',array(
      	'name'	=> 'conditions',
      	'label'	=> Mage::helper('customerreward')->__('Conditions'),
      	'title'	=> Mage::helper('customerreward')->__('Conditions'),
      	'required'	=> true,
	  ))->setRule($model)->setRenderer(Mage::getBlockSingleton('rule/conditions'));
      
      $form->setValues($data);
      $this->setForm($form);
      return parent::_prepareForm();
  }
}