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
class Pap_Merchants_ApplicationGadgets_PendingTasksGadgets extends Gpf_Object {
    const PENDING = "P";
	
    /**
     *
     * @service pending_task read
     * @param $fields
     */
    public function load(Gpf_Rpc_Params $params) {
        $data = new Gpf_Rpc_Data($params);
        
        $data->setValue("pendingAffiliates", $this->getPendingAffiliatesCount());
        $data->setValue("pendingDirectLinks", $this->getPendingDirectLinksCount());
        
        $transactionsInfo = $this->getPendingTransactionsInfo();
        
        $data->setValue("pendingCommissions", $transactionsInfo->get("pendingCommissions"));
        $data->setValue("totalCommissions", $transactionsInfo->get("totalCommissions"));
        $data->setValue("unsentEmails", $this->getUnsentEmails());

        return $data;
    }
    
    public function getPendingAffiliatesCount() {
    	$select = new Gpf_SqlBuilder_SelectBuilder();
    	$select->select->add(Gpf_Db_Table_Users::ID);
    	$select->from->add(Gpf_Db_Table_Users::getName());
    	$select->where->add(Gpf_Db_Table_Users::STATUS, "=", self::PENDING);
    	$select->where->add(Gpf_Db_Table_Users::ROLEID, "=", Pap_Application::DEFAULT_ROLE_AFFILIATE);

    	Gpf_Plugins_Engine::extensionPoint('AffiliateNetwork.modifyWhere', new Gpf_Common_SelectBuilderCompoundRecord($select, new Gpf_Data_Record(array())));

    	$result = $select->getAllRows();
    	
    	return $result->getSize();
    }
    
    /**
     * @return Gpf_Data_Record
     */
    public function getPendingTransactionsInfo() {
        $select = new Gpf_SqlBuilder_SelectBuilder();
        $select->select->add("SUM(IF(".Pap_Db_Table_Transactions::R_STATUS." = '".self::PENDING."',1,0))", "pendingCommissions");
        $select->select->add("SUM(IF(".Pap_Db_Table_Transactions::R_STATUS." = '".self::PENDING."',".
        Pap_Db_Table_Transactions::COMMISSION.",0))", "totalCommissions");
        $select->from->add(Pap_Db_Table_Transactions::getName());

        Gpf_Plugins_Engine::extensionPoint('AffiliateNetwork.modifyWhere', new Gpf_Common_SelectBuilderCompoundRecord($select, new Gpf_Data_Record(array())));

        $result = $select->getOneRow();
        
        return $result;
    }
    
    public function getPendingDirectLinksCount() {
        $select = new Gpf_SqlBuilder_SelectBuilder();
        $select->select->add('l.'.Pap_Db_Table_DirectLinkUrls::ID);
        $select->from->add(Pap_Db_Table_DirectLinkUrls::getName(), 'l');
        $select->from->addLeftJoin(Pap_Db_Table_Banners::getName(), 'b', 'b.'.Pap_Db_Table_Banners::ID.' = l.'.Pap_Db_Table_DirectLinkUrls::BANNER_ID);
        $select->from->addLeftJoin(Pap_Db_Table_Campaigns::getName(), 'c', 'c.'.Pap_Db_Table_Campaigns::ID.' = l.'.Pap_Db_Table_DirectLinkUrls::CAMPAIGN_ID);
        $select->where->add('l.'.Pap_Db_Table_DirectLinkUrls::STATUS, "=", self::PENDING);

        Gpf_Plugins_Engine::extensionPoint('AffiliateNetwork.directLinksModifyWhere', $select);

        $result = $select->getAllRows();
        
        return $result->getSize();
    }
    
    private function getUnsentEmails() {
        $select = new Gpf_SqlBuilder_SelectBuilder();
        $select->select->add('COUNT(o.'.Gpf_Db_Table_MailOutbox::ID.')', 'unsentEmails');
        $select->from->add(Gpf_Db_Table_MailOutbox::getName(), 'o');
        $select->from->addLeftJoin(Gpf_Db_Table_MailAccounts::getName(), 'ma', 'ma.'.Gpf_Db_Table_MailAccounts::ID.' = o.'.Gpf_Db_Table_MailOutbox::MAILACCOUNTID);
        $select->where->add('o.'.Gpf_Db_Table_MailOutbox::STATUS, '=', Gpf_Db_Table_MailOutbox::STATUS_PENDING);

        Gpf_Plugins_Engine::extensionPoint('AffiliateNetwork.modifyWhere', 
        new Gpf_Common_SelectBuilderCompoundRecord($select, new Gpf_Data_Record(array('columnPrefix'), array('ma'))));

        $result = $select->getOneRow();
        
        return $result->get('unsentEmails');
    }
}

?>
