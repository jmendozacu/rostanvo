<?php
/**
 *   @copyright Copyright (c) 2007 Quality Unit s.r.o.
 *   @author Matej Kendera
 *   @package PostAffiliatePro
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
class Pap_Stats_Computer_TransactionsHigherTiers extends Pap_Stats_Computer_Transactions {

    protected function initSelectClause() {
        $this->selectBuilder->select->add(Pap_Db_Table_Transactions::R_STATUS, "status", 't');
        $this->selectBuilder->select->add(Pap_Db_Table_Transactions::PAYOUT_STATUS, "payoutstatus", 't');
        $this->selectBuilder->select->add("sum(if(t.".Pap_Db_Table_Transactions::TIER.">1,1,0))", "cnt");
        $this->selectBuilder->select->add("sum(t.".Pap_Db_Table_Transactions::COMMISSION.")", "commission");
        $this->selectBuilder->select->add("sum(if(t.tier>1,t.".Pap_Db_Table_Transactions::TOTAL_COST.",0))", "totalcost");

        Gpf_Plugins_Engine::extensionPoint('PostAffiliate.Pap_Stats_Computer_Transactions.initSelectClause', $this->selectBuilder->select);
    }
}
?>
