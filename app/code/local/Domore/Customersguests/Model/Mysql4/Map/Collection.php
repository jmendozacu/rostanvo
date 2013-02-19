<?php
/**
 * Domore
 *
 *
 * @category    Domore 
 * @package     Domore_Customersguests
 * @author		Alexandr Martynov <joyview@gmail.com>
 */

class Domore_Customersguests_Model_Mysql4_Map_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract {

	protected function _construct() {
		$this->_init ( 'customersguests/map' );
	}
	
    public function groupByEmail()
    {	
		
        $this->getSelect()
            ->group('customer_email');
			
        return $this;
    }
	
    /**
     * Minimize usual count select
     *
     * @return Varien_Db_Select
     */
    public function getSelectCountSql()
    {
        /* @var $countSelect Varien_Db_Select */
        $countSelect = parent::getSelectCountSql();

        $countSelect->resetJoinLeft();
		$countSelect->reset(Zend_Db_Select::GROUP);
		$sql = (string)$countSelect;
		$sql = str_replace('COUNT(*)','COUNT(distinct customer_email)',$sql);
		return $sql;
    }
	
    /**
     * Join table sales_flat_order_address to select for billing and shipping order addresses.
     * Create corillation map
     *
     * @return Mage_Sales_Model_Mysql4_Collection_Abstract
     */
    public function addAddressFields()
    {
        $billingAliasName = 'billing_o_a';
        $shippingAliasName = 'shipping_o_a';
        $joinTable = $this->getTable('sales/order_address');

        $this
            ->addFilterToMap('billing_firstname', $billingAliasName . '.firstname')
            ->addFilterToMap('billing_lastname', $billingAliasName . '.lastname')
            ->addFilterToMap('billing_telephone', $billingAliasName . '.telephone')
            ->addFilterToMap('billing_postcode', $billingAliasName . '.postcode')
            ->addFilterToMap('billing_postcode', $billingAliasName . '.country_id')
            ->addFilterToMap('billing_postcode', $billingAliasName . '.region')
            ->addFilterToMap('billing_city', $billingAliasName . '.city')

            ->addFilterToMap('shipping_firstname', $shippingAliasName . '.firstname')
            ->addFilterToMap('shipping_lastname', $shippingAliasName . '.lastname')
            ->addFilterToMap('shipping_telephone', $shippingAliasName . '.telephone')
            ->addFilterToMap('shipping_postcode', $shippingAliasName . '.postcode')
            ->addFilterToMap('shipping_postcode', $shippingAliasName . '.country_id')
            ->addFilterToMap('shipping_postcode', $shippingAliasName . '.region')
			->addFilterToMap('shipping_city', $shippingAliasName . '.city');

        $this
            ->getSelect()
            ->joinLeft(
                array($billingAliasName => $joinTable),
                "(main_table.entity_id = $billingAliasName.parent_id AND $billingAliasName.address_type = 'billing')",
                array(
                    $billingAliasName . '.firstname',
                    $billingAliasName . '.lastname',
                    $billingAliasName . '.telephone',
                    $billingAliasName . '.postcode',
					$billingAliasName . '.country_id',
					$billingAliasName . '.region',
                    $billingAliasName . '.city'
                )
            )
            ->joinLeft(
                array($shippingAliasName => $joinTable),
                "(main_table.entity_id = $shippingAliasName.parent_id AND $shippingAliasName.address_type = 'shipping')",
                array(
                    $shippingAliasName . '.firstname',
                    $shippingAliasName . '.lastname',
                    $shippingAliasName . '.telephone',
                    $shippingAliasName . '.postcode',
                    $shippingAliasName . '.country_id',
                    $shippingAliasName . '.region',
                    $shippingAliasName . '.city'
                )
            );

        return $this;
    }
	
}
