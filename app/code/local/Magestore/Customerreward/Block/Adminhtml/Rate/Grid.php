<?php

class Magestore_Customerreward_Block_Adminhtml_Rate_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
  public function __construct()
  {
      parent::__construct();
      $this->setId('rateGrid');
      $this->setDefaultSort('rate_id');
      $this->setDefaultDir('ASC');
      $this->setSaveParametersInSession(true);
  }

  protected function _prepareCollection()
  {
      $collection = Mage::getModel('customerreward/rate')->getCollection();
      $this->setCollection($collection);
      parent::_prepareCollection();
      foreach ($collection as $rate){
      	$rate->setData('website_ids',explode(',',$rate->getData('website_ids')));
      	$rate->setData('customer_group_ids',explode(',',$rate->getData('customer_group_ids')));
      }
      return $this;
  }

  protected function _prepareColumns()
  {
      $this->addColumn('rate_id', array(
          'header'    => Mage::helper('customerreward')->__('ID'),
          'align'     =>'right',
          'width'     => '50px',
          'index'     => 'rate_id',
      ));

      	if (!Mage::app()->isSingleStoreMode()) {
			$this->addColumn('website_ids', array(
				'header'	=> Mage::helper('customerreward')->__('Website'),
				'align'		=> 'left',
				'width'		=> '200px',
				'type'		=> 'options',
				'options'	=> Mage::getSingleton('adminhtml/system_store')->getWebsiteOptionHash(),
				'index'		=> 'website_ids',
				'filter_condition_callback'	=> array($this, 'filterCallback'),
				'sortable'	=> false,
			));
		}
		
		$this->addColumn('customer_group_ids', array(
			'header'	=> Mage::helper('customerreward')->__('Customer Group IDs'),
			'align'		=>'left',
			'index'		=> 'customer_group_ids',
			'type'		=> 'options',
			'width'		=> '200px',
			'sortable'	=> false,
			'options'	=> Mage::getResourceModel('customer/group_collection')
						->addFieldToFilter('customer_group_id', array('gt'=> 0))
						->load()
						->toOptionHash(),
			'filter_condition_callback' => array($this, 'filterCallback'),
		));
		
		$this->addColumn('points',array(
			'header'	=> Mage::helper('customerreward')->__('Point(s)'),
			'align'		=> 'right',
			'index'		=> 'points',
			'type'		=> 'number',
		));
		
		$this->addColumn('direction',array(
			'header'	=> Mage::helper('customerreward')->__('Direction'),
			'align'		=> 'left',
			'index'		=> 'direction',
			'type'		=> 'options',
			'options'	=> array(
							Magestore_Customerreward_Model_Rate::POINT_TO_MONEY => Mage::helper('customerreward')->__('Spend money from point(s)'),
							Magestore_Customerreward_Model_Rate::MONEY_TO_POINT => Mage::helper('customerreward')->__('Earn point(s) from money spent'),
							Magestore_Customerreward_Model_Rate::CLICK_TO_POINT => Mage::helper('customerreward')->__('Earn point(s) from number of unique clicks'),
							Magestore_Customerreward_Model_Rate::VISIT_TO_POINT => Mage::helper('customerreward')->__('Earn point(s) from number of visits')
						),
		));
		
		$this->addColumn('money',array(
			'header'	=> Mage::helper('customerreward')->__('Money/#Clicked'),
			'align'		=> 'left',
			'index'		=> 'money',
			'type'		=> 'number',
			'renderer'	=> 'customerreward/adminhtml_rate_renderer_money',
		));
		
		$this->addColumn('sort_order',array(
			'header'	=> Mage::helper('customerreward')->__('Priority'),
			'align'		=> 'left',
			'index'		=> 'sort_order'
		));
		
        $this->addColumn('action',
            array(
                'header'    =>  Mage::helper('customerreward')->__('Action'),
                'width'     => '70px',
                'type'      => 'action',
                'getter'    => 'getId',
                'actions'   => array(
                    array(
                        'caption'   => Mage::helper('customerreward')->__('Edit'),
                        'url'       => array('base'=> '*/*/edit'),
                        'field'     => 'id'
                    )
                ),
                'filter'    => false,
                'sortable'  => false,
                'index'     => 'stores',
                'is_system' => true,
        ));
	  
      return parent::_prepareColumns();
  }

  public function getRowUrl($row)
  {
      return $this->getUrl('*/*/edit', array('id' => $row->getId()));
  }
  
  public function filterCallback($collection,$column){
  	$value = $column->getFilter()->getValue();
  	if (is_null(@$value)) return;
  	else $collection->addFieldToFilter($column->getIndex(),array('finset' => $value));
  }

}