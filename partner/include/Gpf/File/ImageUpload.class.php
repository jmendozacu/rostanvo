<?php
/**
 *   @copyright Copyright (c) 2007 Quality Unit s.r.o.
 *   @author Andrej Harsani
 *   @package GwtPhpFramework
 *   @since Version 1.0.0
 *   $Id: BannerUpload.class.php 18513 2008-06-13 15:19:18Z aharsani $
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
class Gpf_File_ImageUpload extends Gpf_File_UploadBase {

    public function __construct() {
        parent::__construct();
        $this->setAllowedFileExtensions(array('jpeg', 'jpg', 'gif', 'png', 'bmp'));
        $this->setRelativeUploadPath(Gpf_Paths::getInstance()->getAccountDirectoryRelativePath() . 
                                     Gpf_Paths::FILES_DIRECTORY);
    }

    /**
     * Upload file to server
     *
     * @param Gpf_Rpc_Params $params
     * @service db_file write
     * @return Gpf_Rpc_Form
     */
    public function upload(Gpf_Rpc_Params $params) {
        $form = new Gpf_Rpc_Form($params);
        try {
            $this->uploadFile();
            $uploadedFile = $this->saveUploadedFile();
            $form->setField('filename', $this->name);
            $form->setField('filetype', $this->type);
            $form->setField('filesize', $this->size);
            $form->setField('fileurl', $uploadedFile);
            $form->setInfoMessage($this->_("File was successfully uploaded"));
        } catch (Exception $e) {
            $form->setErrorMessage($e->getMessage());
        }
        return $form;
    }

    protected function saveUploadedFile() {
        $file = parent::saveUploadedFile();

        $file = str_replace('../' . Gpf_Paths::getInstance()->getAccountDirectoryRelativePath(), 
                            Gpf_Paths::getInstance()->getFullAccountServerUrl(),                        
                            $file);

        return $file;
    }
}

?>
