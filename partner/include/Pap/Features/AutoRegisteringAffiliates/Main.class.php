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
class Pap_Features_AutoRegisteringAffiliates_Main extends Gpf_Plugins_Handler {

    const AUTO_REGISTERED_AFFILIATE = 'auto_registered_aff';
    const AUTO_REGISTERED_AFFILIATE_REGISTRATION_EMAIL_SENT = 'auto_registered_aff_reg_email_sent';

    private static $instance = false;

    /**
     * @return Pap_Features_AutoRegisteringAffiliates_Main
     */
    private function __construct() {
    }

    public static function getHandlerInstance() {
        if(!self::$instance)  {
            return self::$instance = new Pap_Features_AutoRegisteringAffiliates_Main();
        }
        return self::$instance;
    }

    public function initSettings($context) {
        $context->addDbSetting(Pap_Features_AutoRegisteringAffiliates_Config::REGISTRATION_NOTIFICATION_EVERY_SALE, Gpf::NO);
    }

    public static function getAffiliateLink($refid) {
        $mainSiteUrl = Gpf_Settings::get(Pap_Settings::MAIN_SITE_URL);

        $user = new Pap_Affiliates_User();
        $user->setRefId($refid);

        if(Pap_Tracking_ClickTracker::getInstance()->getLinkingMethod() == Pap_Tracking_ClickTracker::LINKMETHOD_ANCHOR
        && Gpf_Settings::get(Pap_Settings::SUPPORT_SHORT_ANCHOR_LINKING) == GPF::YES) {
            $affiliateLink = $mainSiteUrl . "#" . $user->getRefId();
        } else {
            $affiliateLink = Pap_Tracking_ClickTracker::getInstance()->getClickUrl(null, $user, $mainSiteUrl);
        }
        return $affiliateLink;
    }

    public static function getBannerCode($refid, $bannerId) {
        $user = new Pap_Affiliates_User();
        $user->setRefId($refid);

        $bannerFactory = new Pap_Common_Banner_Factory();
        $bannerObj = $bannerFactory->getBanner($bannerId);
        return $bannerObj->getCode($user);
    }



    public function afterSaveTransactionSendAffiliateNotification(Pap_Common_Transaction $transaction) {
        if ($transaction->getTier() != '1' || $transaction->getStatus() != Pap_Common_Constants::STATUS_APPROVED || ($transaction->getType() != Pap_Common_Constants::TYPE_SALE && $transaction->getType() != Pap_Common_Constants::TYPE_ACTION)) {
            return;
        }
        $user = Pap_Affiliates_User::loadFromId($transaction->getUserId());
        if ($user->getStatus() != Pap_Common_Constants::STATUS_APPROVED || !$this->isUserAutomaticallyRegistered($user)) {
            return;
        }
        if (!$this->isRegistrationEmailSent($user) || (!$this->wasUserLoggedIn($user) && Gpf::YES == Gpf_Settings::get(Pap_Features_AutoRegisteringAffiliates_Config::REGISTRATION_NOTIFICATION_EVERY_SALE))) {
            Gpf_Log::info('AutoRegisteringAffiliates - afterSaveFirstApprovedTransaction - sendApprovedAffiliateFirstSaleEmail to userid: ' . $user->getId() . ' (' . $user->getUserName() .')' );
            $this->sendApprovedAffiliateFirstSaleEmail($user);
        }
    }

    public function disableOnSaleNotification(Gpf_Plugins_ValueContext $disableChangeStatusNotificationEmail) {
        $data = $disableChangeStatusNotificationEmail->getArray();
        /**
         * @var Pap_Common_User
         */
        $user = $data[0];
        if ($this->isUserAutomaticallyRegistered($user) && !$this->wasUserLoggedIn($user)) {
            $disableChangeStatusNotificationEmail->set(true);
        }
    }

    public function disableOnChangeStatusTransactionNotification(Gpf_Plugins_ValueContext $disableNewSaleNotificationEmail) {
        $data = $disableNewSaleNotificationEmail->getArray();
        /**
         * @var Pap_Common_User
         */
        $user = $data[0];
        if ($this->isUserAutomaticallyRegistered($user) && !$this->wasUserLoggedIn($user)) {
            $disableApprovalEmailNewUserSignup->set(true);
        }
    }


    public function firstTimeUserApprovedEmail(Pap_Affiliates_User $user) {
        if ($this->isUserAutomaticallyRegistered($user) && $this->getNumberOfSalesActions($user->getId()) > 0) {
            Gpf_Log::info('AutoRegisteringAffiliates - firstTimeUserApprovedEmailAndSalesExists - sendApprovedAffiliateFirstSaleEmail to userid: ' . $user->getId() . ' (' . $user->getUserName() .')' );
            $this->sendApprovedAffiliateFirstSaleEmail($user);
        }
    }

    public function newUserSignupDisableApprovalMail(Gpf_Plugins_ValueContext $disableApprovalEmailNewUserSignup) {
        $data = $disableApprovalEmailNewUserSignup->getArray();
        /**
         * @var Pap_Affiliates_User
         */
        $user = $data[0];
        if ($this->isUserAutomaticallyRegistered($user)) {
            $disableApprovalEmailNewUserSignup->set(true);
        }
    }

    public function setAffiliateByEmail(Gpf_Plugins_ValueContext $valueContext) {
        $data = $valueContext->getArray();
        $userEmail = $data[0];
        /**
         * @var unknown_type
         */
        $trackingContext = $data[1];

        $valueContext->set($this->createUserFromEmail($userEmail, $trackingContext));
    }

    private function wasUserLoggedIn(Pap_Common_User $user) {
        try {
            Gpf_Db_Table_UserAttributes::getSetting(Gpf_Auth_Service::TIME_OFFSET, $user->getAccountUserId());
            return true;
        } catch (Gpf_DbEngine_NoRowException $e) {
            return false;
        }
        return false;
    }

    private function sendApprovedAffiliateFirstSaleEmail(Pap_Affiliates_User $user) {
        $mail = new Pap_Mail_AutoRegisteringAffiliateOnFirstSale();
        $mail->setUser($user);
        $mail->addRecipient($user->getEmail());
        $mail->sendNow();

        Gpf_Db_Table_UserAttributes::setSetting(self::AUTO_REGISTERED_AFFILIATE_REGISTRATION_EMAIL_SENT, Gpf::YES, $user->getAccountUserId());
    }

    /**
     * @param $email
     * @param $context
     * @return Pap_Affiliates_User
     */
    private function createUserFromEmail($email, $context) {
        $context->debug('Loading affiliate by email');
        $emailValidator = new Gpf_Rpc_Form_Validator_EmailValidator();
        if (!$emailValidator->validate($email)) {
            if (!$emailValidator->validate(urldecode($email))) {
                $context->debug('    AutoRegisteringAffiliates - Creating affiliate stopped, not valid email address: ' . $email);
                return null;
            }
            $email = urldecode($email);
        }
        try {
            return Pap_Affiliates_User::loadFromUsername($email);
        } catch (Gpf_Exception $e) {
            if (!Pap_Common_User::isUsernameUnique($email)) {
                $context->debug('    AutoRegisteringAffiliates - Creating affiliate stopped, email address is used for another user: ' . $email);
                return;
            }
            $signupForm = new Pap_Signup_AffiliateForm();
            $user = $signupForm->getDbRowObjectWithDefaultValues();
            $user->setUserName($email);
            $user->setFirstName('');
            $user->setLastName(substr($email, 0, strpos($email, '@')));
            //$user->setSendNotification(false);
            $user->save();
            $signupForm->setDefaultEmailNotificationsSettings($user);

            $signupContext = Pap_Contexts_Signup::getContextInstance();
            $signupContext->setUserObject($user);
            $merchantNotificationEmails = new Pap_Signup_SendNotificationEmails();
            $merchantNotificationEmails->process($signupContext);

            $context->debug('    AutoRegisteringAffiliates - New Affiliate created successfully, email: ' . $email);

            Gpf_Db_Table_UserAttributes::setSetting(self::AUTO_REGISTERED_AFFILIATE, Gpf::YES, $user->getAccountUserId());
        }

        return Pap_Affiliates_User::loadFromUsername($email);
    }

    private function getNumberOfSalesActions($userId) {
        $select = new Gpf_SqlBuilder_SelectBuilder();

        $select->select->add('count(transid)', 'countColumn');
        $select->from->add(Pap_Db_Table_Transactions::getName());
        $select->where->add(Pap_Db_Table_Transactions::USER_ID, '=', $userId);
        $select->where->add(Pap_Db_Table_Transactions::TIER, '=', '1');
        $select->where->add(Pap_Db_Table_Transactions::R_STATUS, '=', Pap_Common_Constants::STATUS_APPROVED);

        $compoundWhere = new Gpf_SqlBuilder_CompoundWhereCondition();
        $compoundWhere->add(Pap_Db_Table_Transactions::R_TYPE, '=', Pap_Common_Constants::TYPE_SALE, 'OR');
        $compoundWhere->add(Pap_Db_Table_Transactions::R_TYPE, '=', Pap_Common_Constants::TYPE_ACTION, 'OR');

        $select->where->addCondition($compoundWhere);

        $recordSet = new Gpf_Data_RecordSet();
        $recordSet->load($select);

        foreach($recordSet as $record) {
            return $record->get('countColumn');
        }
        return 0;
    }

    private function isUserAutomaticallyRegistered(Pap_Common_User $user) {
        try {
            if (Gpf::YES == Gpf_Db_Table_UserAttributes::getSetting(self::AUTO_REGISTERED_AFFILIATE, $user->getAccountUserId())) {
                return true;
            }
        } catch (Gpf_DbEngine_NoRowException $e) {
            return false;
        }
        return false;
    }

    private function isRegistrationEmailSent(Pap_Common_User $user) {
        try {
            if (Gpf::YES == Gpf_Db_Table_UserAttributes::getSetting(self::AUTO_REGISTERED_AFFILIATE_REGISTRATION_EMAIL_SENT, $user->getAccountUserId())) {
                return true;
            }
        } catch (Gpf_DbEngine_NoRowException $e) {
            return false;
        }
        return false;
    }
}
?>
