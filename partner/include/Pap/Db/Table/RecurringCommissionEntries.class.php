<?php
/**
 *   @copyright Copyright (c) 2007 Quality Unit s.r.o.
 *   @package PostAffiliatePro
 *   @author Michal Bebjak
 *   @since Version 1.0.0
 *
 *   Licensed under the Quality Unit, s.r.o. Standard End User License Agreement,
 *   Version 1.0 (the "License"); you may not use this file except in compliance
 *   with the License. You may obtain a copy of the License at
 *   http://www.qualityunit.com/licenses/license
 *
 */

/**
 * @package PostAffiliatePro
 */
class Pap_Db_Table_RecurringCommissionEntries extends Gpf_DbEngine_Table {
	const ID = 'recurringcommissionentryid';
	const RECURRING_COMMISSION_ID = 'recurringcommissionid';
	const USERID = 'userid';
	const TIER = 'tier';
	const COMMISSION = 'commission';

	private static $instance;
	    
	/**
	 * @return Pap_Db_Table_RecurringCommissionEntries
	 */
	public static function getInstance() {
	    if(self::$instance === null) {
	        self::$instance = new self;
	    }
	    return self::$instance;
	}
	    
	protected function initName() {
	    $this->setName('pap_recurringcommissionentries');
	}
    
	public static function getName() {
        return self::getInstance()->name();
    }
	
	protected function initColumns() {
		$this->createPrimaryColumn(self::ID, self::CHAR, 8, true);
		$this->createColumn(self::RECURRING_COMMISSION_ID, self::CHAR, 8);
		$this->createColumn(self::USERID, self::CHAR, 8);
		$this->createColumn(self::TIER, self::INT);
		$this->createColumn(self::COMMISSION, self::INT);
	}
}
?>
