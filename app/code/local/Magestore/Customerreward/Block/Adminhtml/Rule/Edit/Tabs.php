<?php

class Magestore_Customerreward_Block_Adminhtml_Rule_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{

  public function __construct()
  {
      parent::__construct();
      $this->setId('rule_tabs');
      $this->setDestElementId('edit_form');
      $this->setTitle(Mage::helper('customerreward')->__('Rule Information'));
  }

  protected function _beforeToHtml()
  {
      $this->addTab('general', array(
          'label'     => Mage::helper('customerreward')->__('General Information'),
          'title'     => Mage::helper('customerreward')->__('General Information'),
          'content'   => $this->getLayout()->createBlock('customerreward/adminhtml_rule_edit_tab_form')->toHtml(),
      ));
      
      $this->addTab('condition', array(
          'label'     => Mage::helper('customerreward')->__('Conditions'),
          'title'     => Mage::helper('customerreward')->__('Conditions'),
          'content'   => $this->getLayout()->createBlock('customerreward/adminhtml_rule_edit_tab_conditions')->toHtml(),
      ));
      
      $this->addTab('actions', array(
      	  'label'     => Mage::helper('customerreward')->__('Actions'),
          'title'     => Mage::helper('customerreward')->__('Actions'),
          'content'   => $this->getLayout()->createBlock('customerreward/adminhtml_rule_edit_tab_actions')->toHtml(),
      ));
      
      return parent::_beforeToHtml();
  }
}