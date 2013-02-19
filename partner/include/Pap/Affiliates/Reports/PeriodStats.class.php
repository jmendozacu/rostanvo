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
class Pap_Affiliates_Reports_PeriodStats extends Pap_Merchants_HomeData {

    protected function getTemplate() {
        return "period_stats";
    }

    protected function modifyStatsParams(Pap_Stats_Params $statsParams) {
        $statsParams->setAffiliateId(Gpf_Session::getAuthUser()->getPapUserId());
        return $statsParams;
    }

    protected function fillDataToTemplate(Gpf_Templates_Template $tmpl, Gpf_Rpc_Params $params) {
        $tmpl = parent::fillDataToTemplate($tmpl, $params);
        $tmpl->assign('subaffiliatesCount', $this->subaffiliatesCount());
        $todayParams = $this->getStatsParams(Gpf_Data_Filter::RANGE_TODAY);
        $tmpl->assign('todaySubaffiliatesCount', $this->subaffiliatesCount($todayParams));
        $thisMonthParams = $this->getStatsParams(Gpf_Data_Filter::RANGE_THIS_MONTH);
        $tmpl->assign('thisMonthSubaffiliatesCount', $this->subaffiliatesCount($thisMonthParams));
        return $tmpl;
    }

    private function subaffiliatesCount(Pap_Stats_Params $params = null) {
        $stats = new Pap_Affiliates_Reports_SubAffiliateStats($params);
        return $subaffiliatesCount = $stats->getNumberOfSubaffiliates();
    }
}

?>
