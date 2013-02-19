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
 * @package PostAffiliatePro plugins
 */
class Pap_Features_AutoRegisteringAffiliates_Config extends Gpf_Plugins_Config {

    const REGISTRATION_NOTIFICATION_EVERY_SALE = 'AutoRegisteringAffiliates_erysalenotification';
    
    protected function initFields() {
        $this->addCheckBox($this->_('Send registration notification on every sale'), self::REGISTRATION_NOTIFICATION_EVERY_SALE, $this->_('Automatically registered affiliate receive registration notification only when first sale is created and approved. If this checkbox is checked, registration email is sent on every approved sale while affiliate login to affiliate panel.'));
    }

    /**
     * @anonym
     * @service custom_separator write
     * @param Gpf_Rpc_Params $params
     * @return Gpf_Rpc_Form
     */
    public function save(Gpf_Rpc_Params $params) {
        $form = new Gpf_Rpc_Form($params);

        Gpf_Settings::set(self::REGISTRATION_NOTIFICATION_EVERY_SALE,
        $form->getFieldValue(self::REGISTRATION_NOTIFICATION_EVERY_SALE));

        $form->setInfoMessage($this->_('Settings saved'));
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
        $form->addField(self::REGISTRATION_NOTIFICATION_EVERY_SALE, Gpf_Settings::get(self::REGISTRATION_NOTIFICATION_EVERY_SALE));
        return $form;
    }
}

?>
