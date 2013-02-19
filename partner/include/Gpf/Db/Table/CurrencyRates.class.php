<?php
/**
 *   @copyright Copyright (c) 2007 Quality Unit s.r.o.
 *   @author Andrej Harsani
 *   @package GwtPhpFramework
 *   @since Version 1.0.0
 *   $Id: Accounts.class.php 28865 2010-07-21 08:24:14Z iivanco $
 *
 *   Licensed under the Quality Unit, s.r.o. Dual License Agreement,
 *   Version 1.0 (the "License"); you may not use this file except in compliance
 *   with the License. You may obtain a copy of the License at
 *   http://www.qualityunit.com/licenses/gpf
 *
 */

/**
 * @package GwtPhpFramework
 */
class Gpf_Db_Table_CurrencyRates extends Gpf_DbEngine_Table {
    const ID = 'rateid';
    const VALID_FROM = 'valid_from';
    const VALID_TO = 'valid_to';
    const SOURCE_CURRENCY = 'source_currency';
    const TARGET_CURRENCY = 'target_currency';
    const RATE = 'rate';
    const TYPE = 'type';

    private static $instance;

    public static function getInstance() {
        if(self::$instance === null) {
            self::$instance = new self;
        }
        return self::$instance;
    }

    protected function initName() {
        $this->setName('g_currencyrates');
    }

    public static function getName() {
        return self::getInstance()->name();
    }

    protected function initColumns() {
        $this->createPrimaryColumn(self::ID, self::INT, 8, true);
        $this->createColumn(self::VALID_FROM, self::DATETIME);
        $this->createColumn(self::VALID_TO, self::DATETIME);
        $this->createColumn(self::SOURCE_CURRENCY, self::CHAR, 10);
        $this->createColumn(self::TARGET_CURRENCY, self::CHAR, 10);
        $this->createColumn(self::RATE, self::INT);
        $this->createColumn(self::TYPE, self::CHAR, 1);
    }
}

?>
