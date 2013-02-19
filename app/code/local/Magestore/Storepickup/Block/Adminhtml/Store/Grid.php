<?php

class Magestore_Storepickup_Block_Adminhtml_Store_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
  public function __construct()
  {
      parent::__construct();
      $this->setId('storeGrid');
      $this->setDefaultSort('store_id');
      $this->setDefaultDir('DESC');
      $this->setSaveParametersInSession(true);
  }

  protected function _prepareCollection()
  {
      $collection = Mage::getModel('storepickup/store')->getCollection();
      $this->setCollection($collection);
      return parent::_prepareCollection();
  }

  protected function _prepareColumns()
  {
      $this->addColumn('store_id', array(
          'header'    => Mage::helper('storepickup')->__('ID'),
          'align'     =>'right',
          'width'     => '50px',
          'index'     => 'store_id',
      ));

      $this->addColumn('store_name', array(
          'header'    => Mage::helper('storepickup')->__('Store Name'),
          'align'     =>'left',
          'index'     => 'store_name',
      ));
	  
      $this->addColumn('country', array(
          'header'    => Mage::helper('storepickup')->__('Country'),
          'align'     => 'left',
          'index'     => 'country',
          'type'      => 'options',
          'options'   => Mage::helper('storepickup/location')->getListCountry(),
      ));		  

		  $this->addColumn('state', array(
			  'header'    => Mage::helper('storepickup')->__('State'),
			  'align'     => 'left',
			  'index'     => 'state',
		  ));	  
	  
      $this->addColumn('city', array(
          'header'    => Mage::helper('storepickup')->__('City'),
          'align'     => 'left',
          'index'     => 'city',
      ));

      // $this->addColumn('suburb_id', array(
          // 'header'    => Mage::helper('storepickup')->__('Suburb'),
          // 'align'     => 'left',
          // 'index'     => 'suburb_id',
          // 'type'      => 'options',
          // 'options'   => Mage::helper('storepickup/location')->getListSuburb(),
      // ));	 

      $this->addColumn('store_latitude', array(
          'header'    => Mage::helper('storepickup')->__('Latitude'),
          'align'     =>'left',
		  'width'     => '50px',
          'index'     => 'store_latitude',
      ));

      $this->addColumn('store_longitude', array(
          'header'    => Mage::helper('storepickup')->__('Longitude'),
          'align'     =>'left',
		  'width'     => '50px',
          'index'     => 'store_longitude',
      ));	  

      $this->addColumn('status', array(
          'header'    => Mage::helper('storepickup')->__('Status'),
          'align'     => 'left',
          'width'     => '80px',
          'index'     => 'status',
          'type'      => 'options',
          'options'   => array(
              1 => 'Enabled',
              2 => 'Disabled',
          ),
      ));
	  
        $this->addColumn('action',
            array(
                'header'    =>  Mage::helper('storepickup')->__('Action'),
                'width'     => '100',
                'type'      => 'action',
                'getter'    => 'getId',
                'actions'   => array(
                    array(
                        'caption'   => Mage::helper('storepickup')->__('Edit'),
                        'url'       => array('base'=> '*/*/edit'),
                        'field'     => 'id'
                    )
                ),
                'filter'    => false,
                'sortable'  => false,
                'index'     => 'stores',
                'is_system' => true,
        ));
		
		$this->addExportType('*/*/exportCsv', Mage::helper('storepickup')->__('CSV'));
		$this->addExportType('*/*/exportXml', Mage::helper('storepickup')->__('XML'));
	  
      return parent::_prepareColumns();
  }

    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('storepickup_id');
        $this->getMassactionBlock()->setFormFieldName('storepickup');

        $this->getMassactionBlock()->addItem('delete', array(
             'label'    => Mage::helper('storepickup')->__('Delete'),
             'url'      => $this->getUrl('*/*/massDelete'),
             'confirm'  => Mage::helper('storepickup')->__('Are you sure?')
        ));

        $statuses = Mage::getSingleton('storepickup/status')->getOptionArray();

        array_unshift($statuses, array('label'=>'', 'value'=>''));
        $this->getMassactionBlock()->addItem('status', array(
             'label'=> Mage::helper('storepickup')->__('Change status'),
             'url'  => $this->getUrl('*/*/massStatus', array('_current'=>true)),
             'additional' => array(
                    'visibility' => array(
                         'name' => 'status',
                         'type' => 'select',
                         'class' => 'required-entry',
                         'label' => Mage::helper('storepickup')->__('Status'),
                         'values' => $statuses
                     )
             )
        ));
        return $this;
    }

  public function getRowUrl($row)
  {
      return $this->getUrl('*/*/edit', array('id' => $row->getId()));
  }

}