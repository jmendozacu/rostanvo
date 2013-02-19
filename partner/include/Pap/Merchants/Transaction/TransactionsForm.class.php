<?php
/**
 *   @copyright Copyright (c) 2007 Quality Unit s.r.o.
 *   @package PostAffiliatePro
 *   @author Milos Jancovic
 *   @since Version 1.0.0
 *   $Id: TransactionsForm.class.php 39054 2012-05-23 08:14:49Z mkendera $
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
class Pap_Merchants_Transaction_TransactionsForm extends Gpf_View_FormService {

    const REFUND_MERCHANT_NOTE = 'merchant_note';
    const REFUND_TYPE = 'status';
    const REFUND_FEE = 'fee';

    /**
     * @return Pap_Common_Transaction
     */
    protected function createDbRowObject() {
        return new Pap_Common_Transaction();
    }

    /**
     * @return string
     */
    protected function getDbRowObjectName() {
        return $this->_("Transaction");
    }

    /**
     * @param Gpf_DbEngine_Row $dbRow
     */
    protected function setDefaultDbRowObjectValues(Gpf_DbEngine_Row $dbRow) {
        $dbRow->setPayoutStatus(Pap_Common_Transaction::PAYOUT_UNPAID);
        $dbRow->set(Pap_Db_Table_Transactions::CLICK_COUNT, '1');
    }

     // @service transaction write
     // @service transaction approve_pending
     // @service transaction decline_pending

    /**
     *
     * @service
     * @anonym
     * @param ids, status
     * @return Gpf_Rpc_Action
     */
    public function changeStatus(Gpf_Rpc_Params $params) {
        if (!Gpf_Session::getAuthUser()->hasPrivilege(Pap_Privileges::TRANSACTION, Pap_Privileges::P_WRITE) && 
        !Gpf_Session::getAuthUser()->hasPrivilege(Pap_Privileges::TRANSACTION, Pap_Privileges::P_APPROVE_PENDING) &&
        !Gpf_Session::getAuthUser()->hasPrivilege(Pap_Privileges::TRANSACTION, Pap_Privileges::P_DECLINE_PENDING)) {
            throw new Gpf_Rpc_PermissionDeniedException('Pap_Merchants_Transaction_TransactionsForm', 'changeStatus');
        }
        $action = new Gpf_Rpc_Action($params, $this->_("Status of %s selected transaction(s) was changed"),
        $this->_("Failed to change status of %s selected transaction(s)"));

        $skippedCount = 0;
        
        foreach ($action->getIds() as $id) {
            try {
                if (Gpf_Session::getAuthUser()->hasPrivilege(Pap_Privileges::TRANSACTION, Pap_Privileges::P_WRITE)) {
                    $this->changeTransactionStatus($id, $action->getParam(self::REFUND_TYPE), $action->getParam(self::REFUND_MERCHANT_NOTE));
                } else if (Gpf_Session::getAuthUser()->hasPrivilege(Pap_Privileges::TRANSACTION, Pap_Privileges::P_APPROVE_PENDING) && 
                        $action->getParam(self::REFUND_TYPE) == Pap_Common_Constants::STATUS_APPROVED) {
                    if (!$this->changeTransactionStatus($id, $action->getParam(self::REFUND_TYPE), $action->getParam(self::REFUND_MERCHANT_NOTE), true)) {
                        $skippedCount++;
                    }
                } else if (Gpf_Session::getAuthUser()->hasPrivilege(Pap_Privileges::TRANSACTION, Pap_Privileges::P_DECLINE_PENDING) && 
                        $action->getParam(self::REFUND_TYPE) == Pap_Common_Constants::STATUS_DECLINED) {
                    if (!$this->changeTransactionStatus($id, $action->getParam(self::REFUND_TYPE), $action->getParam(self::REFUND_MERCHANT_NOTE), true)) {
                        $skippedCount++;
                    }
                } else {
                    $skippedCount++;
                }
                $action->addOk();
            } catch (Exception $e) {
                $action->addError();
            }
        }
        if ($skippedCount > 0) {
            $action->setInfoMessage($this->_("Selected transaction(s) was updated, %s transaction(s) of them was skipped, because you have permissions to change only pending transactions.", $skippedCount));
        }
        return $action;
    }

    private function changeTransactionStatus($transactionId, $status, $note, $forOnlyPendingTransactions = false) {
        $transaction = new Pap_Common_Transaction();
        $transaction->setId($transactionId);
        $transaction->load();

        if ($transaction->getStatus() == $status) {
            return true;
        }
        if ($forOnlyPendingTransactions && $transaction->getStatus() != Pap_Common_Constants::STATUS_PENDING) {
            return false;
        }

        try {
            Gpf_Plugins_Engine::extensionPoint('PostAffiliate.TransactionForm.changeTransactionStatus', $transaction);
        } catch (Gpf_Exception $e) {
            return false;
        }

        $transaction->setStatus($status);
        $transaction->setMerchantNote($note);
        $transaction->update(array(Pap_Db_Table_Transactions::R_STATUS, Pap_Db_Table_Transactions::MERCHANTNOTE, Pap_Db_Table_Transactions::DATE_APPROVED));
        return true;
    }

    /**
     *
     * @service transaction write
     * @return Gpf_Rpc_Action
     */
    public function makeRefundChargebackByParams(Gpf_Rpc_Params $params) {
        $transactionsGrid = new Pap_Merchants_Transaction_TransactionsGrid();
        $ransactionsResponse = $transactionsGrid->getRows($params);

        $transactionsRecordSet = new Gpf_Data_RecordSet();
        $transactionsRecordSet->loadFromObject($ransactionsResponse->rows);

        $ids = Array();
        foreach ($transactionsRecordSet as $transactionRrecord) {
            $ids[] = $transactionRrecord->get(Pap_Db_Table_Transactions::TRANSACTION_ID);
        }

        $refundParams = new Gpf_Rpc_Params();
        $refundParams->add(Gpf_Rpc_Action::IDS, $ids);
        $refundParams->add(self::REFUND_MERCHANT_NOTE, $params->get(self::REFUND_MERCHANT_NOTE));
        $refundParams->add(self::REFUND_TYPE, $params->get(self::REFUND_TYPE));
        $refundParams->add(self::REFUND_FEE, $params->get(self::REFUND_FEE));
        return $this->makeRefundChargeback($refundParams);
    }

    // @service transaction write
    // @service transaction refund

    /**
     *
     * @service
     * @anonym
     * @param ids, status
     * @return Gpf_Rpc_Action
     */
    public function makeRefundChargeback(Gpf_Rpc_Params $params) {
        if (!Gpf_Session::getAuthUser()->hasPrivilege(Pap_Privileges::TRANSACTION, Pap_Privileges::P_WRITE) && 
        !Gpf_Session::getAuthUser()->hasPrivilege(Pap_Privileges::TRANSACTION, Pap_Privileges::P_REFUND)) {
            throw new Gpf_Rpc_PermissionDeniedException('Pap_Merchants_Transaction_TransactionsForm', 'makeRefundChargeback');
        }
        $action = new Gpf_Rpc_Action($params, $this->_("Refund / chargeback of selected transaction(s) was successfully made"),
        $this->_("Failed to make refund / chargeback of selected transaction(s)"));

        $note = $action->getParam(self::REFUND_MERCHANT_NOTE);
        foreach ($action->getIds() as $id) {
            try {
                $transaction = new Pap_Common_Transaction();
                $transaction->processRefundChargeback($id, $action->getParam(self::REFUND_TYPE), $note, '',
                $action->getParam(self::REFUND_FEE), $this->isMultitierRefund($action));
                $action->addOk();
            } catch (Exception $e) {
                $action->addError();
            }
        }

        return $action;
    }

    private function isMultitierRefund(Gpf_Rpc_Action $action) {
        if ($action->getParam('refund_multitier') == Gpf::YES) {
            return true;
        }
        return false;
    }

    /**
     *
     * @service transaction write
     * @param $fields
     * @return Gpf_Rpc_Action
     */
    public function saveFields(Gpf_Rpc_Params $params) {
        $action = new Gpf_Rpc_Action($params);
        $action->setErrorMessage($this->_('Failed to save %s field(s)'));
        $action->setInfoMessage($this->_('%s field(s) successfully saved'));

        $fields = new Gpf_Data_RecordSet();
        $fields->loadFromArray($action->getParam("fields"));

        foreach ($fields as $field) {
            $dbRow = $this->createDbRowObject();
            $dbRow->setPrimaryKeyValue($field->get('id'));
            $dbRow->load();
            $dbRow->set($field->get("name"), $field->get("value"));
            $dbRow->save();
            $action->addOk();
        }

        return $action;
    }

    /**
     * @service transaction read
     * @return Gpf_Rpc_Form
     */
    public function load(Gpf_Rpc_Params $params) {
        return $this->loadSetChannelCode(parent::load($params));
    }

    /**
     * @override
     * @return true/false
     */
    protected function checkBeforeSave(Gpf_DbEngine_RowBase $row, Gpf_Rpc_Form $form, $operationType = self::EDIT) {
        $this->setAccountIdFromCampaign($row);

        if ($operationType == self::ADD) {
            if ($form->existsField(Pap_Db_Table_Transactions::R_TYPE) &&
            Pap_Db_Table_CommissionTypes::isSpecialType($form->getFieldValue(Pap_Db_Table_Transactions::R_TYPE))) {
                return true;
            }
            try {
                $commissionType = new Pap_Db_CommissionType();
                try {
                    $commissionType->setId($form->getFieldValue('commtypeid'));
                    $commissionType->load();
                } catch (Gpf_Data_RecordSetNoRowException $e) {
                    $commissionType = $this->getCommTypeFromCampaignAndRtype($form->getFieldValue(Pap_Db_Table_Transactions::CAMPAIGNID), $form->getFieldValue(Pap_Db_Table_Transactions::R_TYPE));
                }
                if ($commissionType->getStatus() == Pap_Db_CommissionType::STATUS_DISABLED) {
                    $form->setErrorMessage($this->getCommissionTypeDisabledErrorMessage());
                    return false;
                }
                $row->set(Pap_Db_Table_Transactions::R_TYPE, $commissionType->getType());
            } catch (Gpf_DbEngine_NoRowException $e) {
                $form->setErrorMessage($this->getCommissionTypeNotExistErrorMessage());
                return false;
            } catch (Gpf_DbEngine_TooManyRowsException $e) {
                $form->setErrorMessage($this->_('To many commission types'));
                return false;
            }
        }

        return true;
    }

    private function setAccountIdFromCampaign(Pap_Common_Transaction $transaction) {
        try {
            $campaign = $this->createCampaign($transaction->getCampaignId());
        } catch (Gpf_Exception $e) {
        }

        if ($campaign === null) {
            return;
        }

        $transaction->setAccountId($campaign->getAccountId());
    }

    /**
     * @return Pap_Common_Campaign
     */
    protected function createCampaign($campaignId) {
        return Pap_Common_Campaign::getCampaignById($campaignId);
    }

    private function getField($fields, $name) {
        foreach ($fields as $field) {
            if ($field[0] == $name) {
                return $field[1];
            }
        }
    }

    private function setField($fields, $name, $value) {
        $cnt = 0;
        foreach ($fields as $field) {
            if ($field[0] == $name) {
                $fields[$cnt][1] = $value;
                return $fields;
            }
            $cnt ++;
        }
    }

    /**
     * @param Gpf_Rpc_Form $form
     * @return Gpf_Rpc_Form
     */
    private function loadSetChannelCode(Gpf_Rpc_Form $form) {
        $form->setField(Pap_Db_Table_Transactions::CHANNEL, $this->loadChannelCode($form->getFieldValue(Pap_Db_Table_Transactions::CHANNEL)));
        return $form;
    }

    private function loadChannelCode($channelId) {
        $channel = new Pap_Db_Channel();
        $channel->setId($channelId);
        try {
            $channel->load();
        } catch (Gpf_Exception $e) {
            return $channelId;
        }
        return $channel->getValue();
    }

    private function processChannel(Gpf_Rpc_Params $params) {
        $fields = $params->get("fields");
        $channelCode = $this->getField($fields, 'channel');
        $user = $this->getField($fields, 'userid');
        $channel = new Pap_Db_Channel();
        $channel->setValue($channelCode);
        $channel->setPapUserId($user);
        try {
            $channel->loadFromData(array(Pap_Db_Table_Channels::VALUE, Pap_Db_Table_Channels::USER_ID));
        } catch (Gpf_Exception $e) {
            Gpf_Log::error('Unable to load channel from channel code during manual saving transaction');
            return $params;
        }
        $fields = $this->setField($fields, 'channel', $channel->get(Pap_Db_Table_Channels::ID));
        $params->set('fields', $fields);
        return $params;
    }

    /**
     * @service transaction add
     * @return Gpf_Rpc_Form
     */
    public function add(Gpf_Rpc_Params $params) {
        $params = $this->processChannel($params);
        $form = new Gpf_Rpc_Form($params);
        if ($this->isMultiTierTransaction($form)) {
            try {
                return $this->processMultiTier($form);
            } catch (Gpf_Exception $e) {
                $form->setErrorMessage($e->getMessage());
                return $form;
            }
        }
        $form = parent::add($params);
        return $form;
    }

    protected function isMultiTierTransaction(Gpf_Rpc_Form $form) {
        try {
            $commTypeId = $form->getFieldValue(Pap_Db_Table_Transactions::COMMISSIONTYPEID);
            $rtype = $this->getCommType($commTypeId)->getType();
        } catch (Gpf_Data_RecordSetNoRowException $e) {
            $rtype = $this->getRTypeFromForm($form);
        } catch (Gpf_DbEngine_NoRowException $e) {
            $rtype = $this->getRTypeFromForm($form);
        }
        if (Pap_Db_Table_CommissionTypes::isSpecialType($rtype)) {
            return false;
        }
        if ($form->getFieldValue('multiTier') != Gpf::YES) {
            return false;
        }

        return true;
    }

    private function getRTypeFromForm(Gpf_Rpc_Form $form) {
        if ($form->existsField(Pap_Db_Table_Transactions::R_TYPE)) {
            return $form->getFieldValue(Pap_Db_Table_Transactions::R_TYPE);
        } else {
            return $form->getFieldValue(Pap_Db_Table_Transactions::COMMISSIONTYPEID);
        }
    }

    /**
     * @return Pap_Db_CommissionType
     */
    protected function getCommType($commType) {
        $commObj = new Pap_Db_CommissionType();
        $commObj->setId($commType);
        $commObj->load();
        return $commObj;
    }

    /**
     * @return Pap_Db_CommissionType
     */
    protected function getCommTypeFromCampaignAndRtype($campaignId, $rtype) {
        $commObj = new Pap_Db_CommissionType();
        $commObj->setCampaignId($campaignId);
        $commObj->setType($rtype);
        $commObj->loadFromData(array(Pap_Db_Table_CommissionTypes::CAMPAIGNID, Pap_Db_Table_CommissionTypes::TYPE));
        return $commObj;
    }

    protected function getAndSetRType(Gpf_Rpc_Form $form, Gpf_DbEngine_RowBase $dbRow) {
        if ($form->existsField(Pap_Db_Table_Transactions::R_TYPE)) {
            $rtype = $form->getFieldValue(Pap_Db_Table_Transactions::R_TYPE);
            $form->setField(Pap_Db_Table_Transactions::R_TYPE, $rtype);
            $dbRow->set(Pap_Db_Table_Transactions::R_TYPE, $rtype);
            return;
        }
        try {
            $commType = $form->getFieldValue(Pap_Db_Table_Transactions::COMMISSIONTYPEID);
            $commTypeObj = $this->getCommType($commType);
            $rtype = $commTypeObj->getType();
        } catch (Gpf_Data_RecordSetNoRowException $e) {
            if ($form->existsField(Pap_Db_Table_Transactions::COMMISSIONTYPEID)) {
                $rtype = $form->getFieldValue(Pap_Db_Table_Transactions::COMMISSIONTYPEID);
            }
        } catch (Gpf_DbEngine_NoRowException $e) {
            if ($form->existsField(Pap_Db_Table_Transactions::COMMISSIONTYPEID)) {
                $rtype = $form->getFieldValue(Pap_Db_Table_Transactions::COMMISSIONTYPEID);
            }
        }
        $form->setField(Pap_Db_Table_Transactions::R_TYPE, $rtype);
        $dbRow->set(Pap_Db_Table_Transactions::R_TYPE, $rtype);
    }

    protected function fillSave(Gpf_Rpc_Form $form, Gpf_DbEngine_RowBase $dbRow) {
        parent::fillSave($form, $dbRow);
        $this->getAndSetRType($form, $dbRow);
    }

    protected function fillAdd(Gpf_Rpc_Form $form, Gpf_DbEngine_RowBase $dbRow) {
        parent::fillAdd($form, $dbRow);
        $this->getAndSetRType($form, $dbRow);
    }

    /**
     * @service transaction write
     * @return Gpf_Rpc_Form
     */
    public function save(Gpf_Rpc_Params $params) {
        $params = $this->processChannel($params);
        return parent::save($params);
    }

    /**
     * @service transaction delete
     * @return Gpf_Rpc_Action
     */
    public function deleteRows(Gpf_Rpc_Params $params) {
        return parent::deleteRows($params);
    }

    protected function getCampaignObject() {
        return new Pap_Common_Campaign();
    }

    protected function getUserObject() {
        return new Pap_Common_User();
    }

    protected function getRecognizeCommSettingsObject() {
        return new Pap_Tracking_Common_RecognizeCommSettings();
    }

    protected function getSaveAllCommissionsObject() {
        return new Pap_Tracking_Common_SaveAllCommissions();
    }

    protected function getContexts() {
        return Pap_Contexts_Action::getContextInstance();
    }

    public function addMultiTierTransaction($totalCost, $campaignId, $userId, $commissionTypeId, $status, Pap_Common_Transaction $transaction) {
        $context = $this->getContexts();

        $context->setDoCommissionsSave(true);
        $context->setManualAddMode(true);

        $context->setRealTotalCost($totalCost);

        $context->setTransactionObject($transaction);

        $context->setDateCreated($transaction->getDateInserted());

        $campaign = $this->getCampaignObject();
        $campaign->setId($campaignId);
        $campaign->load();
        $context->setCampaignObject($campaign);

        $context->setAccountId($campaign->getAccountId(), Pap_Contexts_Tracking::ACCOUNT_RECOGNIZED_FROM_CAMPAIGN);

        $user = $this->getUserObject();
        $user->setId($userId);
        $user->load();
        $context->setUserObject($user);

        try {
            $commType = $this->getCommType($commissionTypeId);
            if ($commType->getStatus() == Pap_Db_CommissionType::STATUS_DISABLED) {
                throw new Gpf_Exception($this->getCommissionTypeDisabledErrorMessage());
            }
        } catch (Gpf_DbEngine_NoRowException $e) {
            throw new Gpf_Exception($this->getCommissionTypeNotExistErrorMessage());
        }
        $context->setCommissionTypeObject($commType);

        $visitorAffiliateCacheCompoundContext = new Pap_Common_VisitorAffiliateCacheCompoundContext(null,
        $context);
        Gpf_Plugins_Engine::extensionPoint('Tracker.action.recognizeParametersStarted', $visitorAffiliateCacheCompoundContext);

        $commSettings = $this->getRecognizeCommSettingsObject();
        $commSettings->recognize($context, $status);

        $commissionObject = $this->getSaveAllCommissionsObject();
        $commissionObject->save($context);
        Gpf_Plugins_Engine::extensionPoint('Tracker.action.afterSaveCommissions', $context);
    }


    /**
     *
     * @param $form
     * @return Gpf_Rpc_Form
     */
    protected function processMultiTier(Gpf_Rpc_Form $form) {
        $transaction = new Pap_Common_Transaction();
        $form->fill($transaction);

        $this->addMultiTierTransaction($form->getFieldValue(Pap_Db_Table_Transactions::TOTAL_COST),
        $form->getFieldValue(Pap_Db_Table_Transactions::CAMPAIGN_ID),
        $form->getFieldValue(Pap_Db_Table_Transactions::USER_ID),
        $form->getFieldValue(Pap_Db_Table_Transactions::COMMISSIONTYPEID),
        $form->getFieldValue(Pap_Db_Table_Transactions::R_STATUS),
        $transaction);

        $form->setInfoMessage($this->_('Transaction was successfully added'));
        return $form;
    }

    private function getCommissionTypeDisabledErrorMessage() {
        return $this->_('Commission type is disabled');
    }

    private function getCommissionTypeNotExistErrorMessage() {
        return $this->_('Commission type not exist');
    }
}

?>
