<?php
/**
 *   @copyright Copyright (c) 2007 Quality Unit s.r.o.
 *   @package PostAffiliatePro
 *   @author Milos Jancovic
 *   @since Version 1.0.0
 *   $Id: InvoiceFormatForm.class.php 16653 2008-03-25 10:42:12Z mjancovic $
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
class Pap_Merchants_Reports_QuickReportDataTransactionsSum extends Pap_Stats_Data_Object {
    /**
     * @var Pap_Stats_Data_Commission
     */
    private $commission, $count, $totalCost;

    public function __construct() {
        parent::__construct();
        $this->clear();
    }

    public function clear() {
        $this->commission = new Pap_Stats_Data_Commission();
        $this->count = new Pap_Stats_Data_Commission();
        $this->totalCost = new Pap_Stats_Data_Commission();
    }

    public function add(Pap_Stats_Transactions $transactions) {
        $this->commission->add($transactions->getCommission());
        $this->count->add($transactions->getCount());
        $this->totalCost->add($transactions->getTotalCost());
    }

    /**
     * @return Pap_Stats_Data_Commission
     */
    public function getCommission() {
        return $this->commission;
    }

    /**
     * @return Pap_Stats_Data_Commission
     */
    public function getTotalCost() {
        return $this->totalCost;
    }

    /**
     * @return Pap_Stats_Data_Commission
     */
    public function getCount() {
        return $this->count;
    }

    protected function getValueNames() {
        return array('count', 'commission', 'totalCost');
    }
}
?>
