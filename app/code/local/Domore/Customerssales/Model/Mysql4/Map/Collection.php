<?php
/**
 * Domore
 *
 *
 * @category    Domore 
 * @package     Domore_Customerssales
 * @author		Alexandr Martynov <joyview@gmail.com>
 */

class Domore_Customerssales_Model_Mysql4_Map_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract {

	protected function _construct() {
		$this->_init ( 'customerssales/map' );
	}

    public function groupByEmail()
    {
        $this->getSelect()
            ->group('customer_email');

        return $this;
    }
	
	public function addFilterWhereStateNotAbandoned() {
		$this->getSelect()
			->where('state <> ?','abandoned');	
	}
	
	public function addWhereStatusComplete() {
		$this->getSelect()
			->where('status = ?','complete');
	}
	
	public function addAmountField() {
		$this->joinProductTablesForSizeCount();
		$this->addFieldToSelect(new Zend_Db_Expr('`main_table`.*, SUM(base_total_paid) AS report_amount, SUM(`ml15`.qty_ordered) AS ml5count, SUM(`ml5`.qty_ordered) AS ml15count'));
		$this->getSelect()->order('report_amount DESC');
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
     * Add ml count
     *
     */
    public function joinProductTablesForSizeCount()
    {
        $joinTable = $this->getTable('sales/order_item');
		$ml15 = 'ml15';
		$ml5 = 'ml5';

        $this
            ->getSelect()
            ->joinLeft(
                array($ml15 => $joinTable),
                "(main_table.entity_id = $ml15.order_id AND $ml15.sku = 'bioserum15ml')"
            )
            ->joinLeft(
                array($ml5 => $joinTable),
                "(main_table.entity_id = $ml5.order_id AND $ml5.sku = 'bioserum5ml')"
            );

        return $this;
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
            ->addFilterToMap('billing_country_id', $billingAliasName . '.country_id')
            ->addFilterToMap('billing_region', $billingAliasName . '.region')
            ->addFilterToMap('billing_city', $billingAliasName . '.city')
            ->addFilterToMap('billing_street', $billingAliasName . '.street')

            ->addFilterToMap('shipping_firstname', $shippingAliasName . '.firstname')
            ->addFilterToMap('shipping_lastname', $shippingAliasName . '.lastname')
            ->addFilterToMap('shipping_telephone', $shippingAliasName . '.telephone')
            ->addFilterToMap('shipping_postcode', $shippingAliasName . '.postcode')
            ->addFilterToMap('shipping_country_id', $shippingAliasName . '.country_id')
            ->addFilterToMap('shipping_region', $shippingAliasName . '.region')
			->addFilterToMap('shipping_city', $shippingAliasName . '.city')
			->addFilterToMap('shipping_street', $shippingAliasName . '.street');

        $this
            ->getSelect()
            ->joinLeft(
                array($billingAliasName => $joinTable),
                "(main_table.entity_id = $billingAliasName.parent_id AND $billingAliasName.address_type = 'billing')",
                array(
                    $billingAliasName . '.firstname as billing_firstname',
                    $billingAliasName . '.lastname as billing_lastname',
                    $billingAliasName . '.telephone as billing_telephone',
                    $billingAliasName . '.postcode as billing_postcode',
					$billingAliasName . '.country_id as billing_country_id',
					$billingAliasName . '.region as billing_region',
                    $billingAliasName . '.city as billing_city',
                    $billingAliasName . '.street as billing_street'
                )
            )
            ->joinLeft(
                array($shippingAliasName => $joinTable),
                "(main_table.entity_id = $shippingAliasName.parent_id AND $shippingAliasName.address_type = 'shipping')",
                array(
                    $shippingAliasName . '.firstname as shipping_firstname',
                    $shippingAliasName . '.lastname as shipping_lastname',
                    $shippingAliasName . '.telephone as shipping_telephone',
                    $shippingAliasName . '.postcode as shipping_postcode',
                    $shippingAliasName . '.country_id as shipping_country_id',
                    $shippingAliasName . '.region as shipping_region',
                    $shippingAliasName . '.city as shipping_city',
                    $shippingAliasName . '.street as shipping_street'
                )
            );

        return $this;
    }
	
}
