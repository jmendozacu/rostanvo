<?php

class Magestore_Customerreward_Block_Adminhtml_Offer_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{

  public function __construct()
  {
      parent::__construct();
      $this->setId('offer_tabs');
      $this->setDestElementId('edit_form');
      $this->setTitle(Mage::helper('customerreward')->__('Offer Information'));
  }

  protected function _beforeToHtml()
  {
      $this->addTab('general_section', array(
          'label'     => Mage::helper('customerreward')->__('General Information'),
          'title'     => Mage::helper('customerreward')->__('General Information'),
          'content'   => $this->getLayout()->createBlock('customerreward/adminhtml_offer_edit_tab_form')->toHtml(),
      ));
      
      $this->addTab('conditions_section', array(
          'label'     => Mage::helper('customerreward')->__('Conditions'),
          'title'     => Mage::helper('customerreward')->__('Conditions'),
          'content'   => $this->getLayout()->createBlock('customerreward/adminhtml_offer_edit_tab_conditions')->toHtml(),
      ));
      
      $this->addTab('actions_section', array(
          'label'     => Mage::helper('customerreward')->__('Actions'),
          'title'     => Mage::helper('customerreward')->__('Actions'),
          'content'   => $this->getLayout()->createBlock('customerreward/adminhtml_offer_edit_tab_actions')->toHtml(),
      ));
      
      $this->addTab('category_section', array(
          'label'     => Mage::helper('customerreward')->__('Categories'),
          'title'     => Mage::helper('customerreward')->__('Categories'),
          'url'		  => $this->getUrl('*/*/categories',array('_current'=>true,'id'=>$this->getRequest()->getParam('id'))),
          'class'	  => 'ajax',
      ));
      
      $this->addTab('product_section', array(
          'label'     => Mage::helper('customerreward')->__('Products'),
          'title'     => Mage::helper('customerreward')->__('Products'),
          'url'		  => $this->getUrl('*/*/products',array('_current'=>true,'id'=>$this->getRequest()->getParam('id'))),
          'class'	  => 'ajax',
      ));
     
      return parent::_beforeToHtml();
  }
}