<?php
/**
 *   @copyright Copyright (c) 2012 Quality Unit s.r.o.
 *   @package GwtPhpFramework
 *   @author Juraj Simon
 *
 *   Licensed under the Quality Unit, s.r.o. Standard End User License Agreement,
 *   Version 1.0 (the "License"); you may not use this file except in compliance
 *   with the License. You may obtain a copy of the License at
 *   http://www.qualityunit.com/licenses/license
 */

class Gpf_Currency_Helper extends Gpf_Object {

    /**
     * daily currencies : http://www.ecb.europa.eu/stats/eurofxref/eurofxref-daily.xml
     * historical records (very big): http://www.ecb.europa.eu/stats/eurofxref/eurofxref-hist.xml
     */
    private function loadCurrencyRate($sourceCurrency, $targetcurrency, $date, $type = Gpf_Db_CurrencyRate::TYPE_DAILY) {
        $sql = new Gpf_SqlBuilder_SelectBuilder();
        $sql->select->addAll(Gpf_Db_Table_CurrencyRates::getInstance());
        $sql->from->add(Gpf_Db_Table_CurrencyRates::getName());
        $sql->where->add(Gpf_Db_Table_CurrencyRates::SOURCE_CURRENCY, '=', $sourceCurrency);
        $sql->where->add(Gpf_Db_Table_CurrencyRates::TARGET_CURRENCY, '=', $targetcurrency);
        $sql->where->add(Gpf_Db_Table_CurrencyRates::VALID_FROM, '<=', $date);
        $sql->where->add(Gpf_Db_Table_CurrencyRates::VALID_TO, '>=', $date);
        $sql->where->add(Gpf_Db_Table_CurrencyRates::TYPE, '=', $type);
        Gpf_Log::debug($sql->toString());
        return $sql->getOneRow()->get(Gpf_Db_Table_CurrencyRates::RATE);
    }
    
    
    public function getCurrentAvgEurRate($targetcurrency, Gpf_DateTime_Range $range) {
        $sql = new Gpf_SqlBuilder_SelectBuilder();
        $sql->select->add('avg('.Gpf_Db_Table_CurrencyRates::RATE.')', Gpf_Db_Table_CurrencyRates::RATE);
        $sql->from->add(Gpf_Db_Table_CurrencyRates::getName());
        $sql->where->add(Gpf_Db_Table_CurrencyRates::SOURCE_CURRENCY, '=', 'EUR');
        $sql->where->add(Gpf_Db_Table_CurrencyRates::TARGET_CURRENCY, '=', $targetcurrency);
        $sql->where->add(Gpf_Db_Table_CurrencyRates::TYPE, '=', Gpf_Db_CurrencyRate::TYPE_DAILY);
        
        $dateCondition = new Gpf_SqlBuilder_CompoundWhereCondition();
        
        $coumpoundCondWithin = new Gpf_SqlBuilder_CompoundWhereCondition();
        $coumpoundCondWithin->add(Gpf_Db_Table_CurrencyRates::VALID_FROM, '>=', $range->getFrom()->toDateTime());
        $coumpoundCondWithin->add(Gpf_Db_Table_CurrencyRates::VALID_TO, '<=', $range->getTo()->toDateTime());
        $dateCondition->addCondition($coumpoundCondWithin, 'OR');
        $coumpoundCondBefore = new Gpf_SqlBuilder_CompoundWhereCondition();
        $coumpoundCondBefore->add(Gpf_Db_Table_CurrencyRates::VALID_FROM, '<=', $range->getFrom()->toDateTime());
        $coumpoundCondBefore->add(Gpf_Db_Table_CurrencyRates::VALID_TO, '>=', $range->getFrom()->toDateTime());
        $dateCondition->addCondition($coumpoundCondBefore, 'OR');
        $coumpoundCondAfter = new Gpf_SqlBuilder_CompoundWhereCondition();
        $coumpoundCondAfter->add(Gpf_Db_Table_CurrencyRates::VALID_FROM, '<=', $range->getTo()->toDateTime());
        $coumpoundCondAfter->add(Gpf_Db_Table_CurrencyRates::VALID_TO, '>=', $range->getTo()->toDateTime());
        $dateCondition->addCondition($coumpoundCondAfter, 'OR');
        
        $sql->where->addCondition($dateCondition);
        Gpf_Log::debug($sql->toString());
        Gpf_Log::debug('Avg rate: ' . $sql->getOneRow()->get(Gpf_Db_Table_CurrencyRates::RATE));
        return $sql->getOneRow()->get(Gpf_Db_Table_CurrencyRates::RATE);
    }
    
    public function getCurrentEurRate($targetcurrency) {
        $today = new Gpf_DateTime();
        Gpf_Log::debug('Getting currency for ' . $targetcurrency . ' on ' . $today->toDate());
        try {
            return $this->loadCurrencyRate('EUR', $targetcurrency, $today->toDateTime());
        } catch (Gpf_DbEngine_NoRowException $e) {
            Gpf_Log::debug('Rate for ' . $targetcurrency . ' in ' . $today->toDate() . ' not found, loading latest...');
        }
        $rawXmlData = file_get_contents('http://www.ecb.europa.eu/stats/eurofxref/eurofxref-daily.xml');
        $xml = new SimpleXMLElement($rawXmlData);
        $attrs = $xml->{"Cube"}->{"Cube"}->attributes();
        $time = $attrs['time'];
        foreach ($xml->{"Cube"}->{"Cube"}->{'Cube'} as $dailyRate) {
            $rateSettings = $dailyRate->attributes();
            Gpf_Log::debug('Currency ' . $rateSettings['currency'] . ' loaded from XML');
            if ($rateSettings['currency'] == $targetcurrency) {
                $this->saveDailyRate($today, $targetcurrency, (Double)$rateSettings['rate']);
                return (Double)$rateSettings['rate'];
            }
        }
    }

    /**
     * @return Gpf_Db_CurrencyRate
     */
    private function saveDailyRate(Gpf_DateTime $date, $targetCurrncy, $rate) {
        Gpf_Log::debug('Saving rate for ' . $targetCurrncy . ' on ' . $date->toDate() . ', rate=' . $rate);
        $currency = new Gpf_Db_CurrencyRate();
        $currency->setValidFrom($date->toDate() . ' 00:00:00');
        $currency->setValidTo($date->toDate() . ' 23:59:59');
        $currency->setSourceCurrency('EUR');
        $currency->setTargetCurrency($targetCurrncy);
        $currency->setRate($rate);
        $currency->setType(Gpf_Db_CurrencyRate::TYPE_DAILY);
        $currency->save();
        return $currency;
    }

    public function loadHistoricalRates() {
        $rawXmlData = file_get_contents('http://www.ecb.europa.eu/stats/eurofxref/eurofxref-hist.xml');
        $xml = new SimpleXMLElement($rawXmlData);
        $lastDate = null;
        $lastRates = null;
        Gpf_Log::debug('Loading to db...');
        foreach ($xml->{"Cube"}->{"Cube"} as $dailyRates) {
            $attr = $dailyRates->attributes();
            $date = new Gpf_DateTime($attr['time']);
            Gpf_Log::debug('Loading rates for ' . $date->toDate());
            $dayBefore = new Gpf_DateTime($attr['time']);
            $dayBefore->addDay(1);
            if ($lastDate !== null && $dayBefore->toDate() != $lastDate->toDate()) {
                Gpf_Log::debug('Last date was ' . $lastDate->toDate() . '! Extending last saved rates validFrom parameters...');
                foreach($lastRates as $rate) {
                    Gpf_Log::debug('Extending validFrom for currency ' . $rate->getValidFrom() . ' - ' . $rate->getValidTo() . ' - ' . $rate->getTargetCurrency() . ' to value ' . $dayBefore->toDate() . ' 00:00:00');
                    $rate->setValidFrom($dayBefore->toDate() . ' 00:00:00');
                    $rate->update(array(Gpf_Db_Table_CurrencyRates::VALID_FROM));
                }
            }
            Gpf_Log::debug('Saving rates for ' . $date->toDate());
            $lastRates = array();
            foreach ($dailyRates->{"Cube"} as $currencyRate) {
                $info = $currencyRate->attributes();
                Gpf_Log::debug('Saving EUR to ' . $info['currency'] . ', rate=' . $info['rate']);
                $rate = $this->saveDailyRate($date, (String)$info['currency'], (Double)$info['rate']);
                $lastRates[] = $rate;
            }
            $lastDate = new Gpf_DateTime($attr['time']);
        }
        Gpf_Log::debug('Load complete');
    }


}
