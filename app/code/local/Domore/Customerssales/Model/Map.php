<?php

/**
 * Customerssales Map Model 
 *
 *
 * @author      Alexandr Martynov <joyview@gmail.com>
 */

class Domore_Customerssales_Model_Map extends Mage_Core_Model_Abstract
{
	/**
	 * Init resource model and id field
	 */
	protected function _construct() {
		$this->_init ( 'customerssales/map' );
		parent::_construct ();
	}

}
