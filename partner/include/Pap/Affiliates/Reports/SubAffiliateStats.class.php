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
class Pap_Affiliates_Reports_SubAffiliateStats extends Gpf_Object {

    /**
     * @var Pap_Stats_Params
     */
    private $params;

    public function __construct(Pap_Stats_Params $params = null) {
        if ($params == null) {
            $params = new Pap_Stats_Params();
        }
        $this->params = $params;
    }

    /**
     * @service sub_aff_stats read
     * @param $fields
     */
    public function load(Gpf_Rpc_Params $rpcParams) {
        $data = new Gpf_Rpc_Data($rpcParams);

        $data->setValue("numberOfSubaffiliates", $this->getNumberOfSubaffiliates());

        return $data;
    }

    public function getNumberOfSubaffiliates($userid = null) {
        $result = new Gpf_Data_RecordSet();
        $selectBuilder = new Gpf_SqlBuilder_SelectBuilder();
        $selectBuilder->select->add('COUNT(u.userid)', 'amount');

        $selectBuilder->from->add(Pap_Db_Table_Users::getName(), "u");
        $selectBuilder->from->addInnerJoin(Gpf_Db_Table_Users::getName(), "gu", "u.accountuserid = gu.accountuserid");
        $selectBuilder->from->addInnerJoin(Gpf_Db_Table_AuthUsers::getName(), "au", "gu.authid = au.authid");

        $selectBuilder->where->add('u.deleted', '=', Gpf::NO);
        if ($userid === null) {
            $selectBuilder->where->add('u.parentuserid', '=', Gpf_Session::getAuthUser()->getPapUserId());
        } else {
            $selectBuilder->where->add('u.parentuserid', '=', $userid);
        }
        $selectBuilder->where->add('gu.rstatus', '<>', 'D');
        if ($this->params->isDateFromDefined()) {
            $selectBuilder->where->add('u.' . Pap_Stats_Table::DATEINSERTED, '>=', $this->params->getDateFrom()->toDateTime());
        }
        if ($this->params->isDateToDefined()) {
            $selectBuilder->where->add('u.' . Pap_Stats_Table::DATEINSERTED, '<=', $this->params->getDateTo()->toDateTime());
        }
        $selectBuilder->limit->set(0, 1);

        $result->load($selectBuilder);

        if($result->getSize() == 0) {
            return 0;
        }

        foreach($result as $record) {
            return $record->get('amount');
        }
    }
}

?>
