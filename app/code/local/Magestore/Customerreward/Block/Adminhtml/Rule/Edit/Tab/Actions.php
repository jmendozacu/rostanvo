<?php

class Magestore_Customerreward_Block_Adminhtml_Rule_Edit_Tab_Actions extends Mage_Adminhtml_Block_Widget_Form
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
      $fieldset = $form->addFieldset('actions_fieldset', array('legend'=>Mage::helper('customerreward')->__('Actions information')));
     
      $fieldset->addField('points_earned', 'text', array(
          'label'     => Mage::helper('customerreward')->__('Earn points'),
          'title'	  => Mage::helper('customerreward')->__('Earn points'),
          'class'     => 'required-entry',
          'required'  => true,
          'name'      => 'points_earned',
      ));
      
      $form->setValues($data);
      return parent::_prepareForm();
  }
}