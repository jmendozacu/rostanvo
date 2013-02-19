<?php
/**
 *   @copyright Copyright (c) 2007 Quality Unit s.r.o.
 *   @author Michal Bebjak
 *   @package GwtPhpFramework
 *   @since Version 1.0.0
 *   $Id: File.class.php 22679 2008-12-05 12:54:31Z vzeman $
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
class Gpf_Io_Ftp extends Gpf_Object {

    private $username;
    private $password;
    private $hostname;
    private $directory;

    private $ftpStream;

    public function __construct() {
        $this->username = Gpf_Settings::get(Gpf_Settings_Gpf::FTP_USERNAME);
        $this->password = Gpf_Settings::get(Gpf_Settings_Gpf::FTP_PASSWORD);
        $this->hostname = Gpf_Settings::get(Gpf_Settings_Gpf::FTP_HOSTNAME);
        $this->directory = Gpf_Settings::get(Gpf_Settings_Gpf::FTP_DIRECTORY);
    }

    public function setParams($hostname, $directory, $username, $password) {
        $this->hostname = $hostname;
        $this->directory = $directory;
        $this->username = $username;
        $this->password = $password;
    }

    /**
     * @throws Gpf_Exception
     */
    public function connect() {
        $this->ftpStream = @ftp_connect($this->hostname, 21, 15);
        Gpf_Log::debug('Opening ftp stream: ' . $this->hostname);
        if ($this->ftpStream == null) {
            throw new Gpf_Exception($this->_('Can not connect to FTP server.'));
        }
        Gpf_Log::debug('Ftp - triyng to connect with name: ' . $this->username);
        if (@ftp_login($this->ftpStream, $this->username, $this->password) == false) {
            throw new Gpf_Exception($this->_('Wrong username / password'));
        }
        Gpf_Log::debug('Changing main dir: ' . $this->directory);
        if (@ftp_chdir($this->ftpStream, $this->directory) == false) {
            throw new Gpf_Exception($this->_('Can not change to main directory'));
        }
    }

    public function enablePassiveMode($enable) {
        if ($enable) {
            Gpf_Log::debug('Enabling passive mode: ' . $enable);
        } else {
            Gpf_Log::debug('Disabling passive mode: ' . $enable);
        }
        ftp_pasv($this->ftpStream, $enable);
    }

    public function getSize($file) {
        return ftp_size($this->ftpStream, $file);
    }

    public function getFileListInDir($dirname) {
        if (($fileList = ftp_nlist($this->ftpStream, $dirname)) == false) {
            throw new Gpf_Exception($this->_('Directory %s does not exist', $dirname));
        }
        Gpf_Log::debug('Getting files list: ' . print_r($fileList, true));
        return $fileList;
    }

    public function getFileList($dirname) {
        if (($fileList = ftp_nlist($this->ftpStream, Gpf_Paths::INSTALL_DIR)) == false) {
            throw new Gpf_Exception($this->_('Directory %s does not exist', Gpf_Paths::INSTALL_DIR));
        }
        Gpf_Log::debug('Getting files list: ' . print_r($fileList, true));
        return $fileList;
    }

    /**
     * @param $dirname
     * @return Directory name on success or FALSE
     */
    public function mkdir($dirname) {
        return @ftp_mkdir($this->ftpStream, $dirname);
    }

    /**
     * @param $filename
     * @return boolean
     */
    public function delete($filename) {
        Gpf_Log::debug('Deleting ' . $filename);
        return @ftp_delete($this->ftpStream, $filename);
    }
    
    public function chdir($dir) {
        ftp_chdir($this->ftpStream, $dir);
    }

    /**
     * @param $oldname
     * @param $newname
     * @return boolean
     */
    public function rename($oldname, $newname) {
        return @ftp_rename($this->ftpStream, $oldname, $newname);
    }
    
    public function execute($command) {
        ftp_exec($this->ftpStream, $command);
    }

    public function createFile($filename, $content) {
        Gpf_Log::debug('Writing content to tmp file');
        $tempFile = tmpfile();
        if ($tempFile === false) {
            Gpf_Log::error('Unable to create TMP file!');
        }
        fwrite($tempFile, $content);
        fseek($tempFile, 0);
        $result = ftp_fput($this->ftpStream, $filename, $tempFile, FTP_BINARY);
        if ($result === false) {
            Gpf_Log::error('Unable to uplaod file to ftp');
        }
        fclose($tempFile);
    }

    public function close() {
        @ftp_close($this->ftpStream);
    }
}

?>
