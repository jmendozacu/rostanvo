<?php
/**
 *   @copyright Copyright (c) 2007 Quality Unit s.r.o.
 *   @author Milos Jancovic
 *   @package GwtPhpFramework
 *   @since Version 1.0.0
 *   $Id: NoCorrectImportFileException.class.php 19079 2008-07-10 13:40:20Z vzeman $
 *
 *   Licensed under the Quality Unit, s.r.o. Dual License Agreement,
 *   Version 1.0 (the "License"); you may not use this file except in compliance
 *   with the License. You may obtain a copy of the License at
 *   http://www.qualityunit.com/licenses/gpf
 *
 */

/**
 * @package GwtPhpFramework
 */
class Gpf_Csv_NoCorrectImportFileException extends Gpf_Exception {
    public function __construct($message) {
        parent::__construct('Incorrect import file: ' . $message );
    }
    
    protected function logException() {
    	Gpf_Log::critical($this->getMessage(), 'Import');
    }
}
?>
