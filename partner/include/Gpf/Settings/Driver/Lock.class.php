<?php
/**
 *   @copyright Copyright (c) 2007 Quality Unit s.r.o.
 *   @author Matej Kendera, Juraj Simon
 *   @package GwtPhpFramework
 *   @since Version 1.0.0
 *   $Id: Settings.class.php 18002 2008-05-13 18:31:39Z aharsani $
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
class Gpf_Settings_Driver_Lock implements Gpf_Settings_Driver_Locker {

    /**
     * @return Gpf_Io_File
     */
    public function lock($fileName, $operation = LOCK_EX) {
        $file = new Gpf_Io_File($fileName);
        if ($operation == LOCK_EX) {
            $file->setFileMode('a');
        }
        $count = 0;
        while ($count <= Gpf_Settings_Base::MAX_RETRIES) {
            if(!$file->isExists()){
                return $file;
            }
            if(flock($file->getFileHandler(), $operation | LOCK_NB)){
                return $file;
            }
            usleep(round(rand(0, 100)*1000));
            $count++;
        }
        throw new Gpf_Exception('File can not be locked: ' . $fileName);
    }

    /**
     * @param Gpf_Io_File
     */
    public function unlock(Gpf_Io_File $file) {
        if(!$file->isExists()){
            return;
        }
        flock($file->getFileHandler(), LOCK_UN);
    }
}
?>
