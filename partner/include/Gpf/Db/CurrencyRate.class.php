<?php
/**
 *   @copyright Copyright (c) 2011 Quality Unit s.r.o.
 *   @author Juraj Simon
 *   @package GwtPhpFramework
 *
 *   Licensed under the Quality Unit, s.r.o. Dual License Agreement,
 *   Version 1.0 (the "License"); you may not use this file except in compliance
 *   with the License. You may obtain a copy of the License at
 *   http://www.qualityunit.com/licenses/gpf
 */

class Gpf_Db_CurrencyRate extends Gpf_DbEngine_Row {
    const TYPE_DAILY = 'D';
    const TYPE_MONTHLY = 'M';
    const TYPE_QUARTERLY = 'Q';
    
    function init() {
        $this->setTable(Gpf_Db_Table_CurrencyRates::getInstance());
        parent::init();
    }

    public function setId($id) {
        $this->set(Gpf_Db_Table_CurrencyRates::ID, $id);
    }

    public function setValidFrom($validFrom) {
        $this->set(Gpf_Db_Table_CurrencyRates::VALID_FROM, $validFrom);
    }

    public function getValidTo() {
        return $this->get(Gpf_Db_Table_CurrencyRates::VALID_TO);
    }

    public function getValidFrom() {
        return $this->get(Gpf_Db_Table_CurrencyRates::VALID_FROM);
    }

    public function setValidTo($validTo) {
        $this->set(Gpf_Db_Table_CurrencyRates::VALID_TO, $validTo);
    }

    public function setSourceCurrency($srcCurrency) {
        $this->set(Gpf_Db_Table_CurrencyRates::SOURCE_CURRENCY, $srcCurrency);
    }

    public function getTargetCurrency() {
        return $this->get(Gpf_Db_Table_CurrencyRates::TARGET_CURRENCY);
    }

    public function setTargetCurrency($targetCurrency) {
        $this->set(Gpf_Db_Table_CurrencyRates::TARGET_CURRENCY, $targetCurrency);
    }

    public function getRate() {
        return $this->get(Gpf_Db_Table_CurrencyRates::RATE);
    }

    public function setRate($rate) {
        $this->set(Gpf_Db_Table_CurrencyRates::RATE, $rate);
    }
    
    public function getType() {
        return $this->get(Gpf_Db_Table_CurrencyRates::TYPE);
    }

    public function setType($type) {
        $this->set(Gpf_Db_Table_CurrencyRates::TYPE, $type);
    }

}
?>
