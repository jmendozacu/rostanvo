<?php
/**
 *   @copyright Copyright (c) 2007 Quality Unit s.r.o.
 *   @author Martin Pullmann
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
 * @package PostAffiliatePro plugins
 */
class Recurly_Tracker extends Pap_Tracking_CallbackTracker {

    const NEWPAYMENT = 'successful_payment_notification';
    const NEWSUBSCR = 'new_subscription_notification';
    const RECURRING = 'renewed_subscription_notification';

    /**
     * @return Recurly_Tracker
     */
    public function getInstance() {
        $tracker = new Recurly_Tracker();
        $tracker->setTrackerName("Recurly");
        return $tracker;
    }

    protected function updateTransactionStatus($orderId, $status) {
        $this->debug('Updating transactions with orderid='.$orderId.' to status='.$status.' started...');
        $transactions = $this->getAllTransactionIdsWithOrderId($this->getOrderID(), false);
        foreach ($transactions as $transaction) {
            $transaction->setStatus($status);
            $transaction->save();
        }
        $this->debug('Updating transactions ended.');
    }

    protected function registerCommission() {
        $this->debug("Start registering sales");
        $this->debug("Sale status received: ".$this->getType());
        if (($this->getType() == self::NEWSUBSCR) OR ($this->getType() == self::RECURRING) OR ($this->getType() == self::NEWPAYMENT)) {
            $this->processWholeCartAsOneTransaction();
        }
    }

    private function processWholeCartAsOneTransaction() {
        $this->debug("Whole cart as one transaction...");
        $saleTracker = new Pap_Tracking_ActionTracker();
        $sale = $saleTracker->createSale();
        $sale->setOrderId($this->getOrderID()."_".$this->getData1());
        $sale->setTotalCost($this->getTotalCost());
        $sale->setProductId($this->getProductID());
        $sale->setAffiliateID($this->getAffiliateID());
        $cookie = $this->parseCookie(stripslashes($this->getCookie()));
        $this->setVisitorAndAccount($saleTracker, $this->getAffiliateID(), $this->getCampaignID(), $cookie);
        $this->registerAllSales($saleTracker);
    }

    /**
     *
     * @param $orderId
     * @param $includeRefund
     * @return Gpf_DbEngine_Row_Collection<Pap_Common_Transaction>
     */
    private function getAllTransactionIdsWithOrderId($orderId, $includeRefund){
        $status = array(Pap_Common_Constants::STATUS_APPROVED, Pap_Common_Constants::STATUS_PENDING);
        $types = array(Pap_Common_Constants::TYPE_SALE, Pap_Common_Constants::TYPE_ACTION, Pap_Common_Constants::TYPE_LEAD);
        if ($includeRefund == true) {
            $types[] = Pap_Common_Constants::TYPE_REFUND;
        }

        $select = new Gpf_SqlBuilder_SelectBuilder();

        $select->select->addAll(Pap_Db_Table_Transactions::getInstance());
        $select->from->add(Pap_Db_Table_Transactions::getName());
        $select->where->add(Pap_Db_Table_Transactions::ORDER_ID, "=", $orderId);

        $select->where->add(Pap_Db_Table_Transactions::R_TYPE, "IN", $types);
        $select->where->add(Pap_Db_Table_Transactions::R_STATUS, "IN", $status);

        $transaction = new Pap_Common_Transaction();
        return $transaction->loadCollectionFromRecordset($select->getAllRows());
    }

    public function isRecurring() {
        if ($this->getType() == self::RECURRING) {
            return true;
        }
        return false;
    }

    public function checkStatus() {
        $this->debug("Checking type '".$this->getType()."'");
        if (($this->getType() == self::NEWPAYMENT)) {
            $this->debug('New payment notification, continue to register order...');
            return true;
        }
        if (($this->getType() == self::NEWSUBSCR)) {
            $this->debug('New order notification, continue to register order...');
            return true;
        }
        if (($this->getType() == self::RECURRING)) {
            $this->debug('Recurring order notification, continue to process order...');
            return true;
        }
        return false;
    }

    protected function readXmlData() {
        $post_data = file_get_contents('php://input');
        if (get_magic_quotes_gpc()) {
            $post_data = stripslashes($post_data);
        }
        return $post_data;
    }

    protected function getXmlElementByName($name, $elements) {
        foreach ($elements as $element) {
            if ($element->getName() == $name) {
                return $element;
            }
        }
        return false;
    }

    public function readRequestVariables() {
        $input = $this->readXmlData();

        if (Gpf_Settings::get(Recurly_Config::RESEND_URL) != "") {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, Gpf_Settings::get(Recurly_Config::RESEND_URL));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_TIMEOUT, 60);
            //curl_setopt($ch, CURLOPT_USERAGENT, $defined_vars['HTTP_USER_AGENT']);
            curl_setopt($ch, CURLOPT_HTTPHEADER, Array("Content-Type: text/xml"));
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $input);
            curl_exec($ch);

            /*
             $ch = curl_init();
             curl_setopt($ch, CURLOPT_URL, Gpf_Settings::get(Recurly_Config::RESEND_URL));
             curl_setopt($ch, CURLOPT_POST, 1);
             curl_setopt($ch, CURLOPT_TIMEOUT, 60);
             curl_setopt($ch, CURLOPT_HTTPHEADER, Array("Content-Type: text/xml"));
             curl_setopt($ch, CURLOPT_POSTFIELDS, $_POST);
             curl_exec($ch);
             */
        }

        $this->debug("Input get: " . $input);
        try {
            $xml = new SimpleXMLElement($input);
        } catch (Exception $e) {
            $this->setPaymentStatus("Failed");
            $this->debug('Wrong XML format!');
            return false;
        }

        // read last tag to find out what kind of request this is, e.g. </new_subscription_notification>
        $status = strrpos($input,"</");
        $status = substr($input,$status+2,strlen($input)-1);
        $status = substr($status,0,strrpos($status,">"));

        $this->setType($status);

        if ($this->getType() == self::NEWPAYMENT) {
            $totalcost_name = "amount_in_cents";
            $this->setData1((string)$xml->{"transaction"}->{"invoice_number"});
        }
        else {
            $totalcost_name = "total_amount_in_cents";
            $this->setProductID((string)$xml->{"transaction"}->{"plan_code"});
        }
        $this->setTransactionID((string)$xml->{"account"}->{"account_code"});
        $this->setTotalCost((string)$xml->{"transaction"}->{$totalcost_name}/100)*(((string)$xml->{"transaction"}->{"quantity"})?(string)$xml->{"transaction"}->{"quantity"}:1);

        // get original Affiliate
        $status = array(Pap_Common_Constants::STATUS_APPROVED, Pap_Common_Constants::STATUS_PENDING);
        $types = array(Pap_Common_Constants::TYPE_SALE, Pap_Common_Constants::TYPE_ACTION, Pap_Common_Constants::TYPE_LEAD);

        $select = new Gpf_SqlBuilder_SelectBuilder();
        $select->select->addAll(Pap_Db_Table_Transactions::getInstance());
        $select->from->add(Pap_Db_Table_Transactions::getName());
        $select->where->add(Pap_Db_Table_Transactions::ORDER_ID, "=", $this->getOrderID());
        $select->where->add(Pap_Db_Table_Transactions::R_TYPE, "IN", $types);
        $select->where->add(Pap_Db_Table_Transactions::R_STATUS, "IN", $status);
        $transaction = new Pap_Common_Transaction();
        $transaction->fillFromSelect($select);

        if (($transaction->getUserId() == null) OR ($transaction->getUserId() == "")) {
            $this->debug('No affiliate found for order ID: '.$this->getOrderID());
        }
        else {
            $this->setAccountId($transaction->getAccountId());
            $this->setAffiliateID($transaction->getUserId());
            $this->setProductID($transaction->getProductId());
            $this->setCampaignId($transaction->getCampaignId());
        }
    }

    public function getOrderID() {
        return $this->getTransactionID();
    }
}
?>
