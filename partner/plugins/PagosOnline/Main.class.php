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
class PagosOnline_Main extends Gpf_Plugins_Handler {

    /**
     * @return PagosOnline_Main
     */
    public static function getHandlerInstance() {
        return new PagosOnline_Main();
    }

    public function initSettings($context) {
        $context->addDbSetting(PagosOnline_Config::CUSTOM_NUMBER, '');
        $context->addDbSetting(PagosOnline_Config::CUSTOM_SEPARATOR, '');
        $context->addDbSetting(PagosOnline_Config::DISCOUNT_TAX, '');
        $context->addDbSetting(PagosOnline_Config::DISCOUNT_FEE, '');
    }
}
?>
