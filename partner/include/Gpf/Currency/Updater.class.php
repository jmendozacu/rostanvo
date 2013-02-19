<?php
/**
 *   @copyright Copyright (c) 2007 Quality Unit s.r.o.
 *   @package DownloadProtect
 *   @author Andrej Harsani
 *   @since Version 1.0.0
 *   $Id: AuthUser.class.php 19104 2008-07-14 08:23:51Z mfric $
 *
 *   Licensed under the Quality Unit, s.r.o. Standard End User License Agreement,
 *   Version 1.0 (the "License"); you may not use this file except in compliance
 *   with the License. You may obtain a copy of the License at
 *   http://www.qualityunit.com/licenses/license
 *
 */

/**
 * @package DownloadProtect
 */
class Gpf_Currency_Updater extends Gpf_Tasks_LongTask {
    protected function execute() {
        Gpf_Log::info('Executing Currency updater task');
        $this->updateDailyCurrencies();
        $this->setDone();
        Gpf_Log::info('End of Currency updater task');
    }
    
    private function updateDailyCurrencies() {
        $helper = new Gpf_Currency_Helper();
        $helper->getCurrentEurRate('USD');
    }
    
    public function getName() {
        return 'Currencies updater';
    }
}
?>
