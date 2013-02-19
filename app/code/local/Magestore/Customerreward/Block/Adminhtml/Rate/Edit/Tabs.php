<?php

class Magestore_Customerreward_Block_Adminhtml_Rate_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{

  public function __construct()
  {
      parent::__construct();
      $this->setId('rate_tabs');
      $this->setDestElementId('edit_form');
      $this->setTitle(Mage::helper('customerreward')->__('Rate Information'));
  }

  protected function _beforeToHtml()
  {
      $this->addTab('form_section', array(
          'label'     => Mage::helper('customerreward')->__('Rate Information'),
          'title'     => Mage::helper('customerreward')->__('Rate Information'),
          'content'   => $this->getLayout()->createBlock('customerreward/adminhtml_rate_edit_tab_form')->toHtml(),
      ));
     
      return parent::_beforeToHtml();
  }
}