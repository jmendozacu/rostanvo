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
class PagosOnline_Config extends Gpf_Plugins_Config {
    const CUSTOM_NUMBER = 'PagosOnlineCustomNumber';
    const CUSTOM_SEPARATOR = 'PagosOnlineCustomSeparator';
    const DISCOUNT_TAX = 'PagosOnlineDiscountTax'; // iva
    const DISCOUNT_FEE = 'PagosOnlineDiscountFee'; // valorAdicional

    protected function initFields() {
        $this->addTextBox($this->_('Custom field number'), self::CUSTOM_NUMBER, $this->_('The number of custom field that you are using for integration (1 or 2). In case you are already using both custom fields for another purposes, you have to use method with adding custom details to an existing field - you have to define the separator value as well. See PagosOnline integration method.'));
        $this->addTextBox($this->_('Custom value separator'), self::CUSTOM_SEPARATOR, $this->_('Custom value separator should be only set in case custom field is already used by another script. See PagosOnline integration method.'));
        $this->addCheckBox($this->_('Discount tax'), self::DISCOUNT_TAX, $this->_('Discounts tax from total cost value.'));
        $this->addCheckBox($this->_('Discount fee'), self::DISCOUNT_FEE, $this->_('Discounts fee from total cost value.'));
    }

    /**
     * @anonym
     * @service custom_separator write
     * @param Gpf_Rpc_Params $params
     * @return Gpf_Rpc_Form
     */
    public function save(Gpf_Rpc_Params $params) {
        $form = new Gpf_Rpc_Form($params);
        Gpf_Settings::set(self::CUSTOM_NUMBER, $form->getFieldValue(self::CUSTOM_NUMBER));
        Gpf_Settings::set(self::CUSTOM_SEPARATOR, $form->getFieldValue(self::CUSTOM_SEPARATOR));
        Gpf_Settings::set(self::DISCOUNT_TAX, $form->getFieldValue(self::DISCOUNT_TAX));
        Gpf_Settings::set(self::DISCOUNT_FEE, $form->getFieldValue(self::DISCOUNT_FEE));
        if (Gpf_Settings::get(self::CUSTOM_NUMBER) == '') {
            $form->setErrorMessage($this->_('You have to specify Custom field number!'));
        }
        $form->setInfoMessage($this->_('PagosOnline settings saved'));
        return $form;
    }

    /**
     * @anonym
     * @service custom_separator read
     * @param Gpf_Rpc_Params $params
     * @return Gpf_Rpc_Form
     */
    public function load(Gpf_Rpc_Params $params) {
        $form = new Gpf_Rpc_Form($params);
        $form->addField(self::CUSTOM_NUMBER, Gpf_Settings::get(self::CUSTOM_NUMBER));
        $form->addField(self::CUSTOM_SEPARATOR, Gpf_Settings::get(self::CUSTOM_SEPARATOR));
        $form->addField(self::DISCOUNT_TAX, Gpf_Settings::get(self::DISCOUNT_TAX));
        $form->addField(self::DISCOUNT_FEE, Gpf_Settings::get(self::DISCOUNT_FEE));
        return $form;
    }
}

?>
