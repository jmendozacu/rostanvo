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
 * @package GwtPhpFramework
 */
class ISecure_Config extends Gpf_Plugins_Config {
    const CUSTOM_FIELD_ID = 'ISecureCustomFieldId';
    const DISCOUNT_TAX = 'ISecureDiscountTax';
    const REGISTER_AFFILIATE = 'ISecureRegisterAffiliate';
    const TEST_MODE = 'ISecureTestMode';
    const APPROVE_AFFILIATE = 'ISecureApproveAffiliate';
    const PROCESS_WHOLE_CART_AS_ONE_TRANSACTION = 'ISecureProcessWholeCartAsOneTransaction';

    protected function initFields() {
        $this->addTextBox($this->_("Custom field number (1-5)"), self::CUSTOM_FIELD_ID, $this->_("Custom field number that can be used by %s.", Gpf_Settings::get(Pap_Settings::BRANDING_QUALITYUNIT_PAP)));
        $this->addCheckBox($this->_("Discount tax"), self::DISCOUNT_TAX, $this->_('Discounts tax from total cost value.'));
        $this->addCheckBox($this->_("Register new affiliate with every occured event"), self::REGISTER_AFFILIATE, $this->_('When this is checked, new affiliate will be created with every event (based on data received from the order.'));
        $this->addCheckBox($this->_("Process whole cart as one transaction"), self::PROCESS_WHOLE_CART_AS_ONE_TRANSACTION, $this->_('Processes all items from the order as one transaction.'));
        $this->addCheckBox($this->_("Approve affiliate after successful payment"), self::APPROVE_AFFILIATE, $this->_('When this is checked, every matched affiliate (based on the cardholder email) who is pending will be approved after successful payment.'));
        $this->addCheckBox($this->_("Test mode"), self::TEST_MODE, $this->_('Skip back verification.'));
    }

    /**
     * @anonym
     * @service custom_field_id write
     * @param Gpf_Rpc_Params $params
     * @service save
     * @return Gpf_Rpc_Form
     */
    public function save(Gpf_Rpc_Params $params) {
        $form = new Gpf_Rpc_Form($params);
        Gpf_Settings::set(self::CUSTOM_FIELD_ID, $form->getFieldValue(self::CUSTOM_FIELD_ID));
        Gpf_Settings::set(self::DISCOUNT_TAX, $form->getFieldValue(self::DISCOUNT_TAX));
        Gpf_Settings::set(self::REGISTER_AFFILIATE, $form->getFieldValue(self::REGISTER_AFFILIATE));
        Gpf_Settings::set(self::TEST_MODE, $form->getFieldValue(self::TEST_MODE));
        Gpf_Settings::set(self::APPROVE_AFFILIATE, $form->getFieldValue(self::APPROVE_AFFILIATE));
        Gpf_Settings::set(self::PROCESS_WHOLE_CART_AS_ONE_TRANSACTION, $form->getFieldValue(self::PROCESS_WHOLE_CART_AS_ONE_TRANSACTION));
        $form->setInfoMessage($this->_('ISecure settings saved'));
        return $form;
    }

    /**
     * @anonym
     * @service custom_field_id read
     * @param Gpf_Rpc_Params $params
     * @return Gpf_Rpc_Form
     */
    public function load(Gpf_Rpc_Params $params) {
        $form = new Gpf_Rpc_Form($params);
        $form->addField(self::CUSTOM_FIELD_ID, Gpf_Settings::get(self::CUSTOM_FIELD_ID));
        $form->addField(self::DISCOUNT_TAX, Gpf_Settings::get(self::DISCOUNT_TAX));
        $form->addField(self::REGISTER_AFFILIATE, Gpf_Settings::get(self::REGISTER_AFFILIATE));
        $form->addField(self::TEST_MODE, Gpf_Settings::get(self::TEST_MODE));
        $form->addField(self::PROCESS_WHOLE_CART_AS_ONE_TRANSACTION, Gpf_Settings::get(self::PROCESS_WHOLE_CART_AS_ONE_TRANSACTION));
        $form->addField(self::APPROVE_AFFILIATE, Gpf_Settings::get(self::APPROVE_AFFILIATE));
        return $form;
    }
}

?>
