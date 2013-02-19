<?php
/**
 *   @copyright Copyright (c) 2007 Quality Unit s.r.o.
 *   @author Milos Jancovic
 *   @package GwtPhpFramework
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
class LifetimeCommissions_Config extends Gpf_Plugins_Config {

    const LIFETIME_COMMISSION_COOKIE_LIMIT_ACTIVE = 'LifetimeCommissionCookieLimitActive';

    const AFTER_COOKIE_LIMIT_LIFETIME_REFERRER = 'N';
    const AFTER_COOKIE_LIMIT_DO_NOT_SAVE = 'Y';
    const AFTER_COOKIE_LIMIT_UNREFERRED_AFFILIATE = 'U';

    protected function initFields() {
        $radioValues = array(
            self::AFTER_COOKIE_LIMIT_LIFETIME_REFERRER => $this->_('Save sales to lifetime referrer also after cookie lifetime'),
            self::AFTER_COOKIE_LIMIT_DO_NOT_SAVE => $this->_('Do not save sales after cookie lifetime'),
            self::AFTER_COOKIE_LIMIT_UNREFERRED_AFFILIATE => $this->_('Save as unreferred affiliate, if it is configured in tracking settings'),
        );
        $this->addRadioBox($this->_('After cookie limit:'), self::LIFETIME_COMMISSION_COOKIE_LIMIT_ACTIVE, $radioValues);
    }
    
    /**
     * @anonym
     * @service lifetime_comm_settings write
     * @param Gpf_Rpc_Params $params
     * @return Gpf_Rpc_Form
     */
    public function save(Gpf_Rpc_Params $params) {
        $form = new Gpf_Rpc_Form($params);
        Gpf_Settings::set(self::LIFETIME_COMMISSION_COOKIE_LIMIT_ACTIVE, $form->getFieldValue(self::LIFETIME_COMMISSION_COOKIE_LIMIT_ACTIVE));
        $form->setInfoMessage($this->_('Lifetime commission settings saved'));
        return $form;
    }

    /**
     * @anonym
     * @service lifetime_comm_settings read
     * @param Gpf_Rpc_Params $params
     * @return Gpf_Rpc_Form
     */
    public function load(Gpf_Rpc_Params $params) {
        $form = new Gpf_Rpc_Form($params);
        $form->addField(self::LIFETIME_COMMISSION_COOKIE_LIMIT_ACTIVE, Gpf_Settings::get(self::LIFETIME_COMMISSION_COOKIE_LIMIT_ACTIVE));
        return $form;
    }
}

?>
