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
class Pap_Merchants_Reports_TrafficStatsData extends Pap_Common_Overview_OverviewBase {

    /**
     *
     * @service traffic_stats read
     * @param $data
     */
    public function load(Gpf_Rpc_Params $params) {
        $data = new Gpf_Rpc_Data($params);
        $filters = $this->checkFilters($data);
        $statsParameters = new Pap_Stats_Params();
        $statsParameters->initFrom($filters);

        $imps = new Pap_Stats_Impressions($statsParameters);
        $clicks = new Pap_Stats_Clicks($statsParameters);
        $sales = new Pap_Stats_Sales($statsParameters);
        $actions = new Pap_Stats_Actions($statsParameters);
        $transactions = new Pap_Stats_Transactions($statsParameters);

        $data->setValue("countImpressions", $imps->getCount()->getAll());
        $data->setValue("countClicks", $clicks->getCount()->getAll());
        $data->setValue("countSales", $sales->getCount()->getAll() + $actions->getCount()->getAll());
        $data->setValue("sumSales", $sales->getTotalCost()->getAll() + $actions->getTotalCost()->getAll());
        $data->setValue("sumCommissions", $transactions->getCommission()->getAll());

        return $data;
    }

    private function checkFilters(Gpf_Rpc_Data $data) {
        $filters = $data->getFilters();
        if ($filters->getSize() == 0 || count($filters->getFilter("datetime")) == 0) {
            throw new Exception($this->_("Filter does not contain date parameters"));
        }
        return $filters;
    }

    /**
     * @service traffic_stats read
     * @param $data
     */
    public function getClicksData(Gpf_Rpc_Params $params) {
        $data = new Gpf_Rpc_Data($params);
        $filters = $this->checkFilters($data);
        $statsParameters = new Pap_Stats_Params();
        $statsParameters->initFrom($filters);
         
        $clicks = new Pap_Stats_Clicks($statsParameters);

        $data->setValue("clicks", $clicks->getCount()->getAll());
        $data->setValue("clicksDeclined", $clicks->getCount()->getDeclined());
        $data->setValue("clicksRaw", $clicks->getCount()->getRaw());
        $data->setValue("clicksUnique", $clicks->getCount()->getUnique());

        return $data;
    }

    /**
     * @service traffic_stats read
     * @param $data
     */
    public function getImpressionsData(Gpf_Rpc_Params $params) {
        $data = new Gpf_Rpc_Data($params);
        $filters = $this->checkFilters($data);
        $statsParameters = new Pap_Stats_Params();
        $statsParameters->initFrom($filters);

        $imps = new Pap_Stats_Impressions($statsParameters);

        $data->setValue("impressions", $imps->getCount()->getAll());
        $data->setValue("impressionsRaw", $imps->getCount()->getRaw());
        $data->setValue("impressionsUnique", $imps->getCount()->getUnique());

        return $data;
    }

    /**
     * @service traffic_stats read
     * @param $data
     */
    public function getTransactionsData(Gpf_Rpc_Params $params) {
        $data = new Gpf_Rpc_Data($params);
        $filters = $this->checkFilters($data);
        $statsParameters = new Pap_Stats_Params();
        $statsParameters->initFrom($filters);

        $transactions = new Pap_Stats_Transactions($statsParameters);

        $data->setValue("transactionsCount", $transactions->getCount()->getAll());
        $data->setValue("transactionsCountApproved", $transactions->getCount()->getApproved());
        $data->setValue("transactionsCountDeclined", $transactions->getCount()->getDeclined());
        $data->setValue("transactionsCountPaid", $transactions->getCount()->getPaid());
        $data->setValue("transactionsCountPending", $transactions->getCount()->getPending());

        $data->setValue("transactionsTotalCost", $transactions->getTotalCost()->getAll());
        $data->setValue("transactionsTotalCostApproved", $transactions->getTotalCost()->getApproved());
        $data->setValue("transactionsTotalCostDeclined", $transactions->getTotalCost()->getDeclined());
        $data->setValue("transactionsTotalCostPaid", $transactions->getTotalCost()->getPaid());
        $data->setValue("transactionTotalCostPending", $transactions->getTotalCost()->getPending());

        $data->setValue("transactionsCommission", $transactions->getCommission()->getAll());
        $data->setValue("transactionsCommissionApproved", $transactions->getCommission()->getApproved());
        $data->setValue("transactionsCommissionDeclined", $transactions->getCommission()->getDeclined());
        $data->setValue("transactionsCommissionPaid", $transactions->getCommission()->getPaid());
        $data->setValue("transactionsCommissionPending", $transactions->getCommission()->getPending());

        return $data;
    }
}
?>
