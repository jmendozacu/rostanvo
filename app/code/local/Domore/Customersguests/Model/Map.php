<?php

/**
 * Customersguests Map Model 
 *
 *
 * @author      Alexandr Martynov <joyview@gmail.com>
 */

class Domore_Customersguests_Model_Map extends Mage_Core_Model_Abstract
{
	/**
	 * Init resource model and id field
	 */
	protected function _construct() {
		$this->_init ( 'customersguests/map' );
		parent::_construct ();
	}

}
