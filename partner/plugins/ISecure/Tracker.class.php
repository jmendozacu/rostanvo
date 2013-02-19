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
 *
 *   plugin responses based on:
 *   https://www.internetsecure.com/Elavon/ShowPage.asp?page=XMLA&q=2
 *
 */

/**
 * @package PostAffiliatePro plugins
 */
class ISecure_Tracker extends Pap_Tracking_CallbackTracker {

    const CALLBACK_STATUS_VERIFIED = 'VERIFIED';

    protected $doubleColonProducts;

    private function getTransactionIdFromOrderId($orderId) {
        $transaction = new Pap_Common_Transaction();
        try {
            $output = $transaction->getFirstRecordWith(Pap_Db_Table_Transactions::ORDER_ID, $orderId, array(Pap_Common_Constants::STATUS_APPROVED, Pap_Common_Constants::STATUS_PENDING));
            $this->debug('Transaction '.$orderId.' will be marked as refund');
        } catch (Gpf_DbEngine_NoRowException $e) {
            $this->debug('Parent transaction not found by orderId '.$orderId);
            return false;
        }
        return $output->getId();
    }

    /**
     * @return ISecure_Tracker
     */
    public function getInstance() {
        $tracker = new ISecure_Tracker();
        $tracker->setTrackerName("ISecure");
        return $tracker;
    }

    protected function refundChargeback() {
        $transaction = new Pap_Common_Transaction();
        $items = explode("|", $this->doubleColonProducts);
        for ($i = 0; $i<count($items); $i++) {
            $transId = $this->getTransactionIdFromOrderId($this->getOrderID()."_".$i);
            if ($transId !== false)
            $transaction->processRefundChargeback($transId, Pap_Db_Transaction::TYPE_REFUND, '', $this->getOrderID()."_".$i, 0, true);
        }
    }

    public function checkStatus() {
        if ($this->getPaymentStatus() == "01000") { // refund approved
            try {
                $this->refundChargeback();
                $this->debug('Refund complete, ending processing.');
            } catch (Gpf_Exception $e) {
                $this->debug('Error ocured during transaction register:' . $e->getMessage());
            }
            return false;
        }

        // check payment status
        if ($this->getPaymentStatus() != "90000") { // sale approved
            $this->debug('Payment status is NOT COMPLETED. Transaction: '.$this->getTransactionID().', payer email: '.$this->getEmail().', status: '.$this->getPaymentStatus());
            $this->setStatus(true);
            return true;
        }
        return true;
    }

    protected function discountFromTotalcost ($totalcost, $value) {
        if (($value != '') && (is_numeric($value))) {
            return $totalcost - $value;
        }
        return $totalcost;
    }

    protected function computeTotalCost(Pap_Tracking_Request $request) {
        if ($request->getPostParam('xxxAmount') != '') {
            return $this->adjustTotalCost($request->getPostParam('xxxAmount'), $request);
        }
        return $this->adjustTotalCost(0, $request);
    }

    /**
     *  @return Pap_Tracking_Request
     */
    protected function getRequestObject() {
        return Pap_Contexts_Action::getContextInstance()->getRequestObject();
    }

    public function readRequestVariables() {
        $postvars = '';
        foreach ($_POST as $key => $value) {
            $value = stripslashes(stripslashes($value));
            $postvars .= "$key=$value; ";
        }
        $this->debug("  callback POST variables: $postvars");

        $request = $this->getRequestObject();
        $customVar = Gpf_Settings::get(ISecure_Config::CUSTOM_FIELD_ID);
        $this->debug("  looking for custom field: xxxVar".$customVar);
        $cookieValue = stripslashes($request->getPostParam('xxxVar'.$customVar));
        if ($cookieValue != "") {
            $this->debug("  custom field found with value: ".$cookieValue);
        }
        else {
            $this->debug("  custom value xxxVar".$customVar." not found in POST!");
        }

        $this->doubleColonProducts = $request->getPostParam('DoubleColonProducts');
        $this->setCookie($cookieValue);
        $this->setTotalCost($this->computeTotalCost($request));
        $this->setTransactionID($request->getPostParam('SalesOrderNumber'));
        $this->setPaymentStatus($request->getPostParam('Page'));

        $prods = explode("|",$this->doubleColonProducts);
        $prod_string = "";
        foreach ($prods as $prod) {
            $item = explode("::",$prod);
            $prod_string .= $item[3].", ";
        }
        $this->setProductID(substr($prod_string,0,strlen($prod_string)-2));
        $this->setEmail($request->getPostParam('xxxEmail'));
        $this->setData1($request->getPostParam('xxxName'));

        /* IP change
         // in case you want to measure real IP address, set xxxVar5 variable in
         // your payment form, to send visitor's IP address and uncomment this
         // IF statement:
         if (($request->getPostParam('xxxVar5') != "") && ($request->getPostParam('xxxVar5') != 0)) {
         $_SERVER['HTTP_X_FORWARDED_FOR'] = $request->getPostParam('xxxVar5');
         } */

        if ($request->getPostParam('mc_currency') != 0)
        $this->setCurrency($request->getPostParam('mc_currency'));

        $this->readRequestAffiliateVariables($request);

        $this->readAdditionalVariables($request);
    }

    public function readRequestAffiliateVariables(Pap_Tracking_Request $request) {
        $name = explode(" ",$request->getPostParam('xxxName'));
        $this->setUserFirstName($name[0]);
        $this->setUserLastName($name[1]);
        $this->setUserEmail($request->getPostParam('xxxEmail'));
        $this->setUserCity($request->getPostParam('xxxCity'));
        $this->setUserAddress($request->getPostParam('xxxAddress'));
    }

    public function readAdditionalVariables(Pap_Tracking_Request $request) {
        if ($this->getData1() == '') {
            $this->setData1($request->getRequestParameter('data1'));
        }
        if ($this->getData2() == '') {
            $this->setData2($request->getRequestParameter('data2'));
        }
        if ($this->getData3() == '') {
            $this->setData3($request->getRequestParameter('data3'));
        }
        if ($this->getData4() == '') {
            $this->setData4($request->getRequestParameter('data4'));
        }
        if ($this->getData5() == '') {
            $this->setData5($request->getRequestParameter('data5'));
        }
        if ($this->getCouponCode() == '') {
            $this->setCoupon($request->getRequestParameter('coupon_code'));
        }
        if ($this->getChannelId() == '') {
            $this->setChannelId($request->getRequestParameter('channelId'));
        }
    }

    protected function processAccountIdAndVisitorId(Pap_Tracking_ActionTracker $saleTracker, $cookie) {
        $this->debug('Cookie found: '.$cookie);
        parent::processAccountIdAndVisitorId($saleTracker, $cookie);
        if (Gpf_Settings::get(ISecure_Config::APPROVE_AFFILIATE) == Gpf::YES) {
            $this->debug('Automatic approval of affiliates with sale is enabled');
            $userId = $this->computeAffiliateId($saleTracker->getVisitorId(), $saleTracker->getAccountId());
            try {
                $affiliate = new Pap_Common_User();
                $affiliate->setId($userId);
                $affiliate->load();
                if ($affiliate->getStatus() == Pap_Common_Constants::STATUS_PENDING) {
                    $affiliate->setStatus(Pap_Common_Constants::STATUS_APPROVED);
                    $affiliate->update();
                }
            } catch (Gpf_Exception $e) {
                $this->debug('Error occured during approving affiliate with id=' . $userId);
            }
        }
    }

    public function getOrderID() {
        return $this->getTransactionID();
    }

    protected function isAffiliateRegisterAllowed() {
        return (Gpf_Settings::get(ISecure_Config::REGISTER_AFFILIATE) == Gpf::YES);
    }

    protected function prepareSales(Pap_Tracking_ActionTracker $saleTracker) {
        if (Gpf_Settings::get(ISecure_Config::PROCESS_WHOLE_CART_AS_ONE_TRANSACTION) == GPF::NO) {
            $this->prepareSeparateCartItems($saleTracker);
        } else {
            parent::prepareSales($saleTracker);
        }
    }

    private function prepareSeparateCartItems(Pap_Tracking_ActionTracker $saleTracker) {
        $this->debug('Per item registration started...');
        $request = $this->getRequestObject();
        $Items = explode("|",$this->doubleColonProducts);

        for ($i=0; $i<count($Items)-1; $i++) {
            $item = explode("::",$Items[$i]); // Price::Qty::Code::Description::Flags::
            $sale = $saleTracker->createSale();
            $sale->setTotalCost($item[0]*$item[1]);
            $sale->setOrderID($this->getOrderID()."_".$i);
            $sale->setProductID($item[2]);
            $sale->setData1($this->getData1());
            $sale->setData2($this->getData2());
            $sale->setData3($this->getData3());
            $sale->setData4($this->getData4());
            $sale->setData5($this->getData5());
            $sale->setCurrency($this->getCurrency());
            $sale->setChannelId($this->getChannelId());
            if ($this->getStatus()!='') {
                $sale->setStatus($this->getStatus());
            }
            if ($this->getAffiliateID() != '' && $this->getCampaignID() != '') {
                $sale->setAffiliateID($this->getAffiliateID());
                $sale->setCampaignID($this->getCampaignID());
            }

            $this->setVisitorAndAccount($saleTracker, $this->getAffiliateID(), $this->getCampaignID(), $this->getCookie());
        }
    }

    private function adjustTotalCost($originalTotalCost, Pap_Tracking_Request $request, $index = '') {
        $totalCost = $originalTotalCost;
        $this->debug('Original totalcost: '.$totalCost);
        if (Gpf_Settings::get(ISecure_Config::DISCOUNT_TAX)==Gpf::YES) {
            $tax = $request->getPostParam('xxxTotalTax');
            if (!empty($tax)) {
                $totalCost = $this->discountFromTotalcost($totalCost, $tax);
                $tax = $totaltax;
            }
            else {
                $Items = explode("|",$this->doubleColonProducts);
                if (strpos($Items[count($Items)-1][4],"TAX") !== false ) {
                    $totalCost = $this->discountFromTotalcost($totalCost, $Items[count($Items)-1][0]);
                    $tax = $Items[count($Items)-1][0];
                }
                else {
                    $tax = 0;
                }
            }
            $this->debug('Discounting tax ('.$tax.') from totalcost.');
        }
        $this->debug('Totalcost after discounts: '.$totalCost);
        return $totalCost;
    }
}
?>
