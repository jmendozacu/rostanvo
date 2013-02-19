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
class PagosOnline_Tracker extends Pap_Tracking_CallbackTracker {

    const PENDING = "1";
    const APPROVED = "4";

    /**
     * @return PagosOnline_Tracker
     */
    public function getInstance() {
        $tracker = new PagosOnline_Tracker();
        $tracker->setTrackerName("PagosOnline");
        return $tracker;
    }

    public function checkStatus() {
        // check payment status
        if (($this->getPaymentStatus() != self::PENDING) || ($this->getPaymentStatus() != self::APPROVED)) {
            $this->debug('Payment status is NOT COMPLETED. Transaction: '.$this->getTransactionID().', status: '.$this->getPaymentStatus());
            return false;
        }

        // check callback validity
        $result = $this->sendBackVerification();
        $this->setStatus($result);
        return true;
    }

    protected function sendBackVerification() {
        $postvars = '';

        foreach ($_POST as $key => $value) {
            $value = stripslashes(stripslashes($value));
            $postvars .= "$key=$value; ";
        }

        $this->debug("  PagosOnline callback: POST variables: $postvars");

        return "A";
    }

    protected function discountFromTotalcost ($totalcost, $value) {
        if (($value != '') && (is_numeric($value))) {
            return $totalcost - $value;
        }
        return $totalcost;
    }

    /**
     *  @return Pap_Tracking_Request
     */
    protected function getRequestObject() {
        return Pap_Contexts_Action::getContextInstance()->getRequestObject();
    }

    public function readRequestVariables() {
        $request = $this->getRequestObject();
        $cookieValue = stripslashes($request->getPostParam('extra'.Gpf_Settings::get(PagosOnline_Config::CUSTOM_NUMBER)));
        if ($request->getRequestParameter('pap_custom') != '') {
            $cookieValue = stripslashes($request->getRequestParameter('pap_custom'));
        }
        try {
            $customSeparator = Gpf_Settings::get(PagosOnline_Config::CUSTOM_SEPARATOR);
            if ($customSeparator != '') {
                $explodedCookieValue = explode($customSeparator, $cookieValue, 2);
                if (count($explodedCookieValue) == 2) {
                    $cookieValue = $explodedCookieValue[1];
                }
            }
        } catch (Gpf_Exception $e) {
        }

        $this->setCookie($cookieValue);
        $this->setTotalCost($this->adjustTotalCost($request));
        $this->setTransactionID($request->getPostParam('refVenta'));
        if ($request->getPostParam('referenciaItem') != "") {
            $prod = $request->getPostParam('referenciaItem');
        }
        else {
            $prod = $request->getPostParam('descripcion');
        }
        $this->setProductID($prod);
        $this->setPaymentStatus($request->getPostParam('estado_pol'));
        $this->setCurrency($request->getPostParam('moneda'));
    }

    public function getOrderID() {
        return $this->getTransactionID();
    }

    private function adjustTotalCost(Pap_Tracking_Request $request) {
        $totalCost = $request->getPostParam('valor');
        $this->debug('Original totalcost: '.$totalCost);
        if (Gpf_Settings::get(PagosOnline_Config::DISCOUNT_FEE)==Gpf::YES) {
            $totalCost = $totalCost - $request->getPostParam('valorAdicional');
            $this->debug('Discounting fee ('.$request->getPostParam('valorAdicional').') from totalcost.');
        }
        if (Gpf_Settings::get(PagosOnline_Config::DISCOUNT_TAX)==Gpf::YES) {
            $totalCost = $totalCost - $request->getPostParam('iva');
            $this->debug('Discounting tax (IVA) ('.$request->getPostParam('iva').') from totalcost.');
        }

        $this->debug('Totalcost after discounts: '.$totalCost);
        return $totalCost;
    }
}
?>
