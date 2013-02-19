<?php
class Magestore_Customerreward_Block_Adminhtml_Rate extends Mage_Adminhtml_Block_Widget_Grid_Container
{
  public function __construct()
  {
    $this->_controller = 'adminhtml_rate';
    $this->_blockGroup = 'customerreward';
    $this->_headerText = Mage::helper('customerreward')->__('Rate Manager');
    $this->_addButtonLabel = Mage::helper('customerreward')->__('Add Rate');
    parent::__construct();
  }
}