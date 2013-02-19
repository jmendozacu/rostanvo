<?php
class Magestore_Customerreward_Block_Adminhtml_Offer extends Mage_Adminhtml_Block_Widget_Grid_Container
{
  public function __construct()
  {
    $this->_controller = 'adminhtml_offer';
    $this->_blockGroup = 'customerreward';
    $this->_headerText = Mage::helper('customerreward')->__('Offer Manager');
    $this->_addButtonLabel = Mage::helper('customerreward')->__('Add Offer');
    parent::__construct();
  }
}