<?php
/**
 *   @copyright Copyright (c) 2007 Quality Unit s.r.o.
 *   @author Milos Jancovic
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
 * @package PostAffiliatePro
 */

class Pap_Features_ReCaptcha_Main extends Gpf_Plugins_Handler {
    /**
     * @return Pap_Features_ReCaptcha_Main
     */
    public static function getHandlerInstance() {
        return new Pap_Features_ReCaptcha_Main();
    }

    public function loadSettings(Gpf_Rpc_Form $form) {
        $form->setField(Pap_Settings::RECAPTCHA_PRIVATE_KEY, Gpf_Settings::get(Pap_Settings::RECAPTCHA_PRIVATE_KEY));
        $form->setField(Pap_Settings::RECAPTCHA_PUBLIC_KEY, Gpf_Settings::get(Pap_Settings::RECAPTCHA_PUBLIC_KEY));
        $form->setField(Pap_Settings::RECAPTCHA_ENABLED, Gpf_Settings::get(Pap_Settings::RECAPTCHA_ENABLED));
        $form->setField(Pap_Settings::RECAPTCHA_THEME, Gpf_Settings::get(Pap_Settings::RECAPTCHA_THEME));
        return $form;
    }

    public function saveSettings(Gpf_Rpc_Form $form) {
        Gpf_Settings::set(Pap_Settings::RECAPTCHA_PUBLIC_KEY, $form->getFieldValue(Pap_Settings::RECAPTCHA_PUBLIC_KEY));
        Gpf_Settings::set(Pap_Settings::RECAPTCHA_PRIVATE_KEY, $form->getFieldValue(Pap_Settings::RECAPTCHA_PRIVATE_KEY));
        Gpf_Settings::set(Pap_Settings::RECAPTCHA_ENABLED, $form->getFieldValue(Pap_Settings::RECAPTCHA_ENABLED));
        Gpf_Settings::set(Pap_Settings::RECAPTCHA_THEME, $form->getFieldValue(Pap_Settings::RECAPTCHA_THEME));
    }

    public function loadAccountSettings(Gpf_Rpc_Form $form) {
        $form->setField(Pap_Settings::RECAPTCHA_PRIVATE_KEY, Gpf_Settings::get(Pap_Settings::RECAPTCHA_PRIVATE_KEY));
        $form->setField(Pap_Settings::RECAPTCHA_PUBLIC_KEY, Gpf_Settings::get(Pap_Settings::RECAPTCHA_PUBLIC_KEY));
        $form->setField(Pap_Settings::RECAPTCHA_ENABLED, Gpf_Settings::get(Pap_Settings::RECAPTCHA_ACCOUNT_ENABLED));
        $form->setField(Pap_Settings::RECAPTCHA_THEME, Gpf_Settings::get(Pap_Settings::RECAPTCHA_ACCOUNT_THEME));
        return $form;
    }

    public function saveAccountSettings(Gpf_Rpc_Form $form) {
        Gpf_Settings::set(Pap_Settings::RECAPTCHA_PUBLIC_KEY, $form->getFieldValue(Pap_Settings::RECAPTCHA_PUBLIC_KEY));
        Gpf_Settings::set(Pap_Settings::RECAPTCHA_PRIVATE_KEY, $form->getFieldValue(Pap_Settings::RECAPTCHA_PRIVATE_KEY));
        Gpf_Settings::set(Pap_Settings::RECAPTCHA_ACCOUNT_ENABLED, $form->getFieldValue(Pap_Settings::RECAPTCHA_ENABLED));
        Gpf_Settings::set(Pap_Settings::RECAPTCHA_ACCOUNT_THEME, $form->getFieldValue(Pap_Settings::RECAPTCHA_THEME));
    }

    public function validateCaptchaAccount(Gpf_Rpc_Form $form) {
        if (Gpf_Settings::get(Pap_Settings::RECAPTCHA_ACCOUNT_ENABLED) != Gpf::YES || 
                Gpf_Settings::get(Pap_Settings::RECAPTCHA_PUBLIC_KEY) == '') {
            return;
        }

        $this->validate($form);
    }

    public function validateCaptcha(Gpf_Rpc_Form $form) {
        if (Gpf_Settings::get(Pap_Settings::RECAPTCHA_ENABLED) != Gpf::YES || 
                Gpf_Settings::get(Pap_Settings::RECAPTCHA_PUBLIC_KEY) == '') {
            return;
        }

        $this->validate($form);
    }

    private function validate(Gpf_Rpc_Form $form) {
        require_once('../include/Pap/Features/ReCaptcha/recaptchalib.php');
        if ((is_null($form->getFieldValue("recaptcha_challenge_field")) || $form->getFieldValue("recaptcha_challenge_field") == '') && 
        (is_null($form->getFieldValue("recaptcha_response_field")) || $form->getFieldValue("recaptcha_response_field") == '')) {
            $form->setErrorMessage($this->_("The reCAPTCHA isn't configured correctly") . ': ' . $this->_("wrong public key or check template 'signup_fields.tpl' if contains: {widget id=\"recaptcha\"}"));
            return;
        }
        if (is_null($form->getFieldValue("recaptcha_response_field")) || $form->getFieldValue("recaptcha_response_field") == '') {
            $form->setErrorMessage($this->_("The reCAPTCHA wasn't entered correctly"));
            return;
        }
        $resp = recaptcha_check_answer (Gpf_Settings::get(Pap_Settings::RECAPTCHA_PRIVATE_KEY),
        $_SERVER["REMOTE_ADDR"],
        $form->getFieldValue("recaptcha_challenge_field"),
        $form->getFieldValue("recaptcha_response_field"));
        if (!$resp->is_valid && $resp->error == 'incorrect-captcha-sol') {
            $form->setErrorMessage($this->_("The reCAPTCHA wasn't entered correctly"));
            return;
        }
        if (!$resp->is_valid && $resp->error == 'invalid-site-private-key') {
            $form->setErrorMessage($this->_("The reCAPTCHA isn't configured correctly") . ': ' . $this->_("wrong private key"));
            return;
        }
        if (!$resp->is_valid) {
            $form->setErrorMessage($this->_("The reCAPTCHA isn't configured correctly") . " Error: " . $resp->error);
            return;
        }
    }
    
    public function initJsResource(Gpf_Contexts_Module $module) {
        $module->addJsResource('https://www.google.com/recaptcha/api/js/recaptcha_ajax.js');
    }
    
    public function addApplicationSettings(Pap_ApplicationSettings $appSettings) {
        $appSettings->addValue(Pap_Settings::RECAPTCHA_ENABLED, Gpf_Settings::get(Pap_Settings::RECAPTCHA_ENABLED));
        $appSettings->addValue(Pap_Settings::RECAPTCHA_PUBLIC_KEY, Gpf_Settings::get(Pap_Settings::RECAPTCHA_PUBLIC_KEY));
        $appSettings->addValue(Pap_Settings::RECAPTCHA_THEME, Gpf_Settings::get(Pap_Settings::RECAPTCHA_THEME));
        $appSettings->addValue(Pap_Settings::RECAPTCHA_ACCOUNT_ENABLED, Gpf_Settings::get(Pap_Settings::RECAPTCHA_ACCOUNT_ENABLED));
        $appSettings->addValue(Pap_Settings::RECAPTCHA_ACCOUNT_THEME, Gpf_Settings::get(Pap_Settings::RECAPTCHA_ACCOUNT_THEME));
    }
}
?>
