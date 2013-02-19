<?php
/**
 * Customerssales Grid
 *
 * @author      Alexandr Martynov <joyview@gmail.com>
 */

class Domore_Customerssales_Block_Map_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
	
	public function __construct()
	{
		parent::__construct();
		$this->setId( 'customerssalesGrid' );
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
		$collection = Mage::getResourceModel( 'customerssales/map_collection' );
		$collection->addAmountField();
		$collection->addAddressFields();
		$collection->addFilterWhereStateNotAbandoned();
		//$collection->addWhereStatusComplete();
		$collection->groupByEmail();
		//echo $collection->getSelect(); exit();
		$this->setCollection( $collection );
		parent::_prepareCollection();
		return $this;
	}
	
	protected function _prepareColumns()
	{
	
		$this->addColumn('amount', array(
            'header'    => Mage::helper('customer')->__('Amount'),
            'index'     => 'report_amount',
			'type'      => 'currency',
			'currency' => 'base_currency_code'
        ));
		
		$this->addColumn('billing_firstname', array(
            'header'    => Mage::helper('customer')->__('First Name'),
            'index'     => 'billing_firstname'
        ));
        $this->addColumn('billing_lastname', array(
            'header'    => Mage::helper('customer')->__('Last Name'),
            'index'     => 'billing_lastname'
        ));

		$this->addColumn( 'customer_email', array (
				'header' => Mage::helper( 'customerssales' )->__( 'Email' ), 
				'index' => 'customer_email'));
		
        $this->addColumn('billing_telephone', array(
            'header'    => Mage::helper('customer')->__('Telephone'),
            'width'     => '100',
            'index'     => 'billing_telephone'
        ));
		
        $this->addColumn('billing_postcode', array(
            'header'    => Mage::helper('customer')->__('ZIP'),
            'width'     => '90',
            'index'     => 'billing_postcode',
        ));

        $this->addColumn('billing_country_id', array(
            'header'    => Mage::helper('customer')->__('Country'),
            'width'     => '100',
            'type'      => 'country',
            'index'     => 'billing_country_id',
        ));

        $this->addColumn('billing_region', array(
            'header'    => Mage::helper('customer')->__('State/Province'),
            'width'     => '100',
            'index'     => 'billing_region',
        ));
		
        $this->addColumn('billing_city', array(
            'header'    => Mage::helper('customer')->__('City'),
            'width'     => '100',
            'index'     => 'billing_city',
        ));
		
        $this->addColumn('billing_street', array(
            'header'    => Mage::helper('customer')->__('Street'),
            'index'     => 'billing_street',
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
