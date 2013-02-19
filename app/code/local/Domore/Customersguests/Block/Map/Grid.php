<?php
/**
 * Customersguests Grid
 *
 * @author      Alexandr Martynov <joyview@gmail.com>
 */

class Domore_Customersguests_Block_Map_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
	
	public function __construct()
	{
		parent::__construct();
		$this->setId( 'customersguestsGrid' );
		$this->setDefaultSort( 'id' );
		//   $this->setSaveParametersInSession(true);
	}
	
	protected function _getStore()
	{
		$storeId = ( int ) $this->getRequest()->getParam( 'store', 0 );
		return Mage::app()->getStore( $storeId );
	}
	
	protected function _prepareCollection()
	{
		$collection = Mage::getResourceModel( 'customersguests/map_collection' );
		$collection->addAddressFields();
//		$collection->addNameToSelect();
		$collection->groupByEmail();
//		echo $collection->getSelect(); exit();
		$this->setCollection( $collection );
		parent::_prepareCollection();
		return $this;
	}
	
	protected function _prepareColumns()
	{

		
		$this->addColumn( 'entity_id', array (
            'header'    => Mage::helper('customer')->__('ID'),
            'width'     => '50px',
            'index'     => 'entity_id',
            'type'  => 'number',
			));

		$this->addColumn('firstname', array(
            'header'    => Mage::helper('customer')->__('First Name'),
            'index'     => 'firstname'
        ));
        $this->addColumn('lastname', array(
            'header'    => Mage::helper('customer')->__('Last Name'),
            'index'     => 'lastname'
        ));

		$this->addColumn( 'customer_email', array (
				'header' => Mage::helper( 'customersguests' )->__( 'Email' ), 
				'index' => 'customer_email'));

				
        $groups = Mage::getResourceModel('customer/group_collection')
            ->load()
            ->toOptionHash();

        $this->addColumn('group', array(
            'header'    =>  Mage::helper('customer')->__('Group'),
            'width'     =>  '100',
            'index'     =>  'customer_group_id',
            'type'      =>  'options',
            'options'   =>  $groups,
        ));
		
        $this->addColumn('Telephone', array(
            'header'    => Mage::helper('customer')->__('Telephone'),
            'width'     => '100',
            'index'     => 'telephone'
        ));
		
        $this->addColumn('postcode', array(
            'header'    => Mage::helper('customer')->__('ZIP'),
            'width'     => '90',
            'index'     => 'postcode',
        ));

        $this->addColumn('country_id', array(
            'header'    => Mage::helper('customer')->__('Country'),
            'width'     => '100',
            'type'      => 'country',
            'index'     => 'country_id',
        ));

        $this->addColumn('region', array(
            'header'    => Mage::helper('customer')->__('State/Province'),
            'width'     => '100',
            'index'     => 'region',
        ));
		
        $this->addColumn('city', array(
            'header'    => Mage::helper('customer')->__('City'),
            'width'     => '100',
            'index'     => 'city',
        ));

        $this->addColumn('customer_since', array(
            'header'    => Mage::helper('customer')->__('Customer Since'),
            'type'      => 'datetime',
            'align'     => 'center',
            'index'     => 'created_at',
            'gmtoffset' => true
        ));
		
		
        $this->addExportType('*/*/exportCsv', Mage::helper('customer')->__('CSV'));
        $this->addExportType('*/*/exportXml', Mage::helper('customer')->__('XML'));		
		return parent::_prepareColumns();
	}
	
	public function getGridUrl()
	{
		return $this->getUrl( '*/*/*/', array ('_current' => true ) );
	}
	

}
