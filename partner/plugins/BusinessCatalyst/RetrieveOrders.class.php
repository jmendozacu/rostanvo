<?php
/**
 *   @copyright Copyright (c) 2009 Quality Unit s.r.o.
 *   @author Maros Galik
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
class BusinessCatalyst_RetrieveOrders extends BusinessCatalyst_Retrieve {

    private $lastDateUpdate = '';
    private $lastProcessedEntityId = 0;

    public function retrieve() {
        $this->sendRequest();
    }

    private function sendRequest() {

        Pap_Contexts_Action::getContextInstance()->debug('BusinessCatalyst check started');

        $this->lastDateUpdate = Gpf_Settings::get(BusinessCatalyst_Config::BC_LAST_CHECK);
        $this->lastProcessedEntityId = Gpf_Settings::get(BusinessCatalyst_Config::BC_LAST_ENTITY_ID);

        $xmlRequest = $this->getXmlRequest(
        Gpf_Settings::get(BusinessCatalyst_Config::LOGIN),
        Gpf_Settings::get(BusinessCatalyst_Config::PASSWORD),
        Gpf_Settings::get(BusinessCatalyst_Config::SITE_ID),
        $this->lastDateUpdate);

        $headers = $this->getPostHeader(strlen($xmlRequest), 'OrderList_Retrieve');
        $response = $this->executeCurl($xmlRequest, $headers);

        if (strpos($response, 'ERROR: ') !== false) {
            Pap_Contexts_Action::getContextInstance()->debug('Stopped synchronization by: '.$response);
            return;
        }

        if (strpos($response, '<faultcode>soap:Server</faultcode><faultstring>Server was unable to process request') !== false) {
            Pap_Contexts_Action::getContextInstance()->debug('No data to process ('.$response.')');
            return;
        }

        Pap_Contexts_Action::getContextInstance()->debug('BusinessCatalyst Request: '.$xmlRequest);
        Pap_Contexts_Action::getContextInstance()->debug('BusinessCatalyst Response: '.$response);

        $parsedXml = $this->parseXmlResponse($response);

        $xml = new SimpleXMLElement($parsedXml);
        $orders = $xml->xpath('//OrderDetails');

        foreach ($orders as $order) {
            $this->processOrder($order);
        }

        Pap_Contexts_Action::getContextInstance()->debug('BusinessCatalyst check ended');
    }

    private function processOrder($xmlOrder) {
        $entityId = (int)$xmlOrder->entityId;
        $lastDateUpdate = (string)$xmlOrder->lastUpdateDate;

        if (strtotime($lastDateUpdate) <= strtotime($this->lastDateUpdate)) {
            Pap_Contexts_Action::getContextInstance()->debug('Processing old entity ID '.$entityId.' skipped.');
            return;
        }

        Pap_Contexts_Action::getContextInstance()->debug('Processing order; getting visitorid from actual xml: '.$xmlOrder->asXML());
        $visitorId = $this->parseVisitorId($xmlOrder->asXML(), Gpf_Settings::get(BusinessCatalyst_Config::PAP_CUSTOM_FIELD_NAME));

        if ($visitorId == null || $visitorId == '') {
            $retrieveCase = new BusinessCatalyst_RetrieveCase();
            $visitorId = $retrieveCase->retrieveVisitorId($entityId);
            Pap_Contexts_Action::getContextInstance()->debug('Processing order; processing visitorid - after laoad entity request: '.$visitorId);
        }

        if ($visitorId == null || $visitorId == '') {
            Pap_Contexts_Action::getContextInstance()->debug('No visitorId found');
            return;
        }

        Pap_Contexts_Action::getContextInstance()->debug('Processing order; cookie:'.
        $visitorId.'; entityId:'.(string)$xmlOrder->entityId.' orderId:'.(string)$xmlOrder->orderId.'; totalCost:'.(string)$xmlOrder->totalOrderAmount);
        Pap_Contexts_Action::getContextInstance()->debug('Processing order; datetime-lastupdate:'.$lastDateUpdate);

        $tracker = new BusinessCatalyst_Tracker();
        $tracker->setTransactionID((string)$xmlOrder->orderId);
        $tracker->setTotalCost((string)$xmlOrder->totalOrderAmount);
        $tracker->setCookie($visitorId);
        $tracker->setDateTime(BusinessCatalyst_Config::getPapDateFormat($lastDateUpdate));
        $tracker->process();

        $this->lastDateUpdate = $lastDateUpdate;
        $this->lastProcessedEntityId = $entityId;

        Pap_Contexts_Action::getContextInstance()->debug('BusinessCatalyst save last update time: '.$this->lastDateUpdate.' and entity id: '.$this->lastProcessedEntityId);
        Gpf_Settings::set(BusinessCatalyst_Config::BC_LAST_CHECK, $this->lastDateUpdate);
        Gpf_Settings::set(BusinessCatalyst_Config::BC_LAST_ENTITY_ID, $this->lastProcessedEntityId);
    }

    private function parseXmlResponse($xml) {
        $endLength = strlen('</OrderList_RetrieveResult>');

        $begin = strpos($xml, '<OrderList_RetrieveResult>');
        $end = strpos($xml, '</OrderList_RetrieveResult>');

        $parsedXml = substr($xml, $begin, $end - $begin + $endLength);
        return $parsedXml;
    }

    private function getXmlRequest($username, $password, $siteId, $lastUpdateDate) {
        $body = '<?xml version="1.0" encoding="utf-8"?>
		<soap:Envelope xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/">
			<soap:Body>
				<OrderList_Retrieve xmlns="http://tempuri.org/CatalystDeveloperService/CatalystCRMWebservice">
					<username>'.$username.'</username>
					<password>'.$password.'</password>
					<siteId>'.$siteId.'</siteId>
					<lastUpdateDate>'.$lastUpdateDate.'</lastUpdateDate>
					<recordStart>0</recordStart>
					<moreRecords>false</moreRecords>
				</OrderList_Retrieve>
			</soap:Body>
		</soap:Envelope>';
        return $body;
    }

    private function parseVisitorId($xml, $customFieldName) {
        $fieldName = '<fieldName>'.$customFieldName.'</fieldName>';

        if (strpos(strtolower($xml), strtolower($fieldName)) === false) { //no custom field found in this XML
            Pap_Contexts_Action::getContextInstance()->debug('BusinessCatalyst: no custom field found');
            return '';
        }

        if (!strstr($xml, '<fieldValue>') && strstr($xml, '<fieldValue/>')) {
            Pap_Contexts_Action::getContextInstance()->debug('BusinessCatalyst: custom field value is empty');
            return '';
        }

        $begin = strpos(strtolower($xml), strtolower($fieldName)) + strlen($fieldName);
        $begin = strpos(strtolower($xml), strtolower('<fieldValue>'), $begin) + strlen('<fieldValue>');
        $end = strpos(strtolower($xml), strtolower('</fieldValue>'), $begin);
        $fieldValue = substr($xml, $begin, $end - $begin);

        if (($visitorIdEnd = strpos($fieldValue, ';')) !== false) {
            $fieldValue = substr($fieldValue, 0, $visitorIdEnd);
        }

        return $fieldValue;
    }
}

?>
