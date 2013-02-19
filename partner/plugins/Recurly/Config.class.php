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
class Recurly_Config extends Gpf_Plugins_Config {
    const RESEND_URL = 'RecurlyResendURL';

    protected function initFields() {
        $this->addTextBox($this->_("Resend push notifications"), self::RESEND_URL, $this->_("(BETA!) Enter the full URL where do you want to resend received data from Recurly. This is used in case you are using third party application that uses push notifications too. E.g. membership handler."));
    }

    /**
     * @anonym
     * @service resend_url write
     * @param Gpf_Rpc_Params $params
     * @return Gpf_Rpc_Form
     */
    public function save(Gpf_Rpc_Params $params) {
        $form = new Gpf_Rpc_Form($params);
        Gpf_Settings::set(self::RESEND_URL, $form->getFieldValue(self::RESEND_URL));
        $form->setInfoMessage($this->_('Recurly plugin settings saved'));
        return $form;
    }

    /**
     * @anonym
     * @service resend_url read
     * @param Gpf_Rpc_Params $params
     * @return Gpf_Rpc_Form
     */
    public function load(Gpf_Rpc_Params $params) {
        $form = new Gpf_Rpc_Form($params);
        $form->addField(self::RESEND_URL, Gpf_Settings::get(self::RESEND_URL));
        return $form;
    }
}

?>
