<?php
/**
 *   @copyright Copyright (c) 2007 Quality Unit s.r.o.
 *   @author Andrej Harsani
 *   @package GwtPhpFramework
 *   @since Version 1.0.0
 *   $Id: ConstraintException.class.php 18002 2008-05-13 18:31:39Z aharsani $
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
class Gpf_DbEngine_Row_ConstraintException extends Gpf_Exception {
    private $fieldCode;
    
    public function __construct($fieldCode, $message) {
        parent::__construct($message);
        $this->fieldCode = $fieldCode;    
    }
    
    protected function logException() {
    }
    
    public function getFieldCode() {
        return $this->fieldCode;
    }
}

?>
