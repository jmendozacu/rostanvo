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
class Pap_Merchants_Reports_QuickReportData extends Pap_Common_ServerTemplatePanel {

    /**
     * @var Pap_Stats_Params
     */
    protected $statsParams;

    protected function getTemplate() {
        return "quick_report_content";
    }

    /**
     * @service quick_stats read
     * @param $fields
     */
    public function load(Gpf_Rpc_Params $params) {
        $data = new Gpf_Rpc_Data($params);

        $this->statsParams = $this->getStatsParams();
        $this->statsParams->initFrom($data->getFilters());

        $this->fillData($data, $params);
        return $data;
    }

    /**
     * @return Pap_Stats_Params
     */
    protected function getStatsParams() {
        return new Pap_Stats_Params();
    }

    protected function fillDataToTemplate(Gpf_Templates_Template $tmpl, Gpf_Rpc_Params $params) {
        $tmpl->assign('sumTransaction', new Pap_Merchants_Reports_QuickReportDataTransactionsSum());

        $tmpl->assign('clicks', new Pap_Stats_Clicks($this->statsParams));

        $tmpl->assign('impressions', new Pap_Stats_Impressions($this->statsParams));

        $tmpl->assign('sales', new Pap_Stats_Sales($this->statsParams));

        $tmpl->assign('transactionTypes', new Pap_Stats_TransactionTypeStatsFirstTier($this->statsParams));

        return $tmpl;
    }
}
?>
