<?php
/**
 * Domore
 *
 *
 * @category    Domore 
 * @package     Domore_Customerssales
 * @author		Alexandr Martynov <joyview@gmail.com>
 */

class Domore_Customerssales_Model_Mysql4_Map extends Mage_Core_Model_Mysql4_Abstract {
	/**
	 * Initialize main table and table id field
	 */
	protected function _construct() {
		$this->_init ( 'customerssales/map', 'id' );
	}
}
