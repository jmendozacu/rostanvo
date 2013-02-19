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
 * @package PostAffiliate
 */
class Pap_Features_AutoRegisteringAffiliates_Definition extends Gpf_Plugins_Definition  {
    public function __construct() {
        $this->codeName = 'AutoRegisteringAffiliates';
        $this->name = $this->_('Auto-Registering Affiliates');
        $this->description = $this->_('This feature allows to use (non)affiliate email address as affiliate parameter in sale/click tracking code. If email is not registered, new affiliate will be created automatically and he get notification about registration when he reach first approved commission. Notifications about sales will be disabled for this affiliate while he first login into affiliate panel. For generating affiliate link or banner code for new affiliates you can use HTML forms in Configuration > Affiliate signup > Auto registering forms.'). 
        '<br/><a href="'.Gpf_Application::getKnowledgeHelpUrl('987859-Auto-Registering-Affiliates').'" target="_blank">'.$this->_('More help in our Knowledge Base').'</a>';
        $this->version = '1.0.0';
        $this->configurationClassName = 'Pap_Features_AutoRegisteringAffiliates_Config';
        $this->pluginType = self::PLUGIN_TYPE_FEATURE;

        $this->addImplementation('Core.defineSettings', 'Pap_Features_AutoRegisteringAffiliates_Main', 'initSettings');
        $this->addImplementation('Tracker.RecognizeAffiliate.getUserById', 'Pap_Features_AutoRegisteringAffiliates_Main', 'setAffiliateByEmail');
        $this->addImplementation('PostAffiliate.affiliate.sendNewUserSignupApprovedMail', 'Pap_Features_AutoRegisteringAffiliates_Main', 'newUserSignupDisableApprovalMail');
        $this->addImplementation('PostAffiliate.affiliate.firsttimeApproved', 'Pap_Features_AutoRegisteringAffiliates_Main', 'firstTimeUserApprovedEmail');
        $this->addImplementation('Pap_Tracking_Action_SendTransactionNotificationEmails.sendOnNewSaleNotificationToDirectAffiliate', 'Pap_Features_AutoRegisteringAffiliates_Main', 'disableOnSaleNotification');
        $this->addImplementation('Pap_Tracking_Action_SendTransactionNotificationEmails.sendOnChangeStatusNotificationToAffiliate', 'Pap_Features_AutoRegisteringAffiliates_Main', 'disableOnChangeStatusTransactionNotification');
        $this->addImplementation('PostAffiliate.Transaction.afterSave', 'Pap_Features_AutoRegisteringAffiliates_Main', 'afterSaveTransactionSendAffiliateNotification');
    }

    public function onActivate() {
        $this->insertMailTemplateToDb();
    }

    public function onDeactivate() {
        $this->deleteMailTemplateFromDb();
    }

    private function insertMailTemplateToDb() {
        $template = new Pap_Mail_AutoRegisteringAffiliateOnFirstSale();
        $template->setup(Gpf_Session::getAuthUser()->getAccountId());
    }

    private function deleteMailTemplateFromDb() {
        $dbTemplate = new Gpf_Db_MailTemplate();
        $dbTemplate->setAccountId(Gpf_Session::getAuthUser()->getAccountId());
        $dbTemplate->setClassName('Pap_Mail_AutoRegisteringAffiliateOnFirstSale');
        try {
            $dbTemplate->loadFromData();
        } catch (Gpf_DbEngine_NoRowException $e) {
            Gpf_Log::warning('Mail template Pap_Mail_AutoRegisteringAffiliateOnFirstSale was not found during deactivation of '.$this->name.' feature. It should be there.');
            return;
        }
        $dbTemplate->delete();
    }
}
?>
