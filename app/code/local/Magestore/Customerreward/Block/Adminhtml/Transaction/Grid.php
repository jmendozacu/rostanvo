<?php

class Magestore_Customerreward_Block_Adminhtml_Transaction_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
  public function __construct()
  {
      parent::__construct();
      $this->setId('transactionGrid');
      $this->setDefaultSort('transaction_id');
      $this->setDefaultDir('DESC');
      $this->setSaveParametersInSession(true);
  }

  protected function _prepareCollection()
  {
      $collection = Mage::getModel('customerreward/transaction')->getCollection();
      $this->setCollection($collection);
      return parent::_prepareCollection();
  }

  protected function _prepareColumns()
  {
      $this->addColumn('transaction_id', array(
          'header'    => Mage::helper('customerreward')->__('ID'),
          'align'     =>'right',
          'width'     => '50px',
          'index'     => 'transaction_id',
      ));

      $this->addColumn('customer_id', array(
          'header'    => Mage::helper('customerreward')->__('Customer'),
          'align'     =>'left',
          'index'     => 'customer_id',
          'renderer'  => 'customerreward/adminhtml_transaction_renderer_customer',
      ));

      $this->addColumn('title', array(
          'header'    => Mage::helper('customerreward')->__('Title'),
          'align'     =>'left',
          'index'     => 'title',
      ));

      $this->addColumn('notice', array(
          'header'    => Mage::helper('customerreward')->__('Notice'),
          'align'     =>'left',
          'index'     => 'notice',
      ));

      $this->addColumn('points_change', array(
          'header'    => Mage::helper('customerreward')->__('Point(s)'),
          'align'     =>'left',
          'index'     => 'points_change',
          'type'	  => 'number',
      ));

      $this->addColumn('points_spent', array(
          'header'    => Mage::helper('customerreward')->__('Spent'),
          'align'     =>'left',
          'index'     => 'points_spent',
          'type'	  => 'number',
      ));

      $this->addColumn('create_at', array(
          'header'    => Mage::helper('customerreward')->__('Create at'),
          'align'     =>'left',
          'index'     => 'create_at',
          'type'	  => 'datetime',
      ));

      $this->addColumn('expiration_date', array(
          'header'    => Mage::helper('customerreward')->__('Expiration date'),
          'align'     =>'left',
          'index'     => 'expiration_date',
          'type'	  => 'datetime',
      ));

      $this->addColumn('is_expired', array(
          'header'    => Mage::helper('customerreward')->__('Expired'),
          'align'     => 'left',
          'index'     => 'is_expired',
          'type'      => 'options',
          'options'   => array(
              '0' => Mage::helper('customerreward')->__('No'),
              '1' => Mage::helper('customerreward')->__('Yes'),
          ),
      ));

      $this->addColumn('store_id', array(
          'header'    => Mage::helper('customerreward')->__('Store view'),
          'align'     =>'left',
          'index'     => 'store_id',
          'type'	  => 'options',
          'options'	  => Mage::getModel('adminhtml/system_store')->getStoreOptionHash(true),
      ));
      
      return parent::_prepareColumns();
  }
  
  public function getRowUrl($row)
  {
      return $this->getUrl('*/*/view', array('id' => $row->getId()));
  }
}