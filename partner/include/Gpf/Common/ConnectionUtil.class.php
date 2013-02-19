<?php
/**
 *   @copyright Copyright (c) 2011 Quality Unit s.r.o.
 *   @author Juraj Simon
 *   @package GwtPhpFramework
 *   @since Version 1.0.0
 *
 *   Licensed under the Quality Unit, s.r.o. Dual License Agreement,
 *   Version 1.0 (the "License"); you may not use this file except in compliance
 *   with the License. You may obtain a copy of the License at
 *   http://www.qualityunit.com/licenses/gpf
 */

class Gpf_Common_ConnectionUtil extends Gpf_Object{
    /**
     *
     * @service
     * @anonym
     * @return Gpf_Rpc_Data
     */
    public function ping(Gpf_Rpc_Params $params) {
        $sql = new Gpf_SqlBuilder_SelectBuilder();
        $sql->select->addAll(Gpf_Db_Table_AuthUsers::getInstance());
        $sql->from->add(Gpf_Db_Table_AuthUsers::getName());
        $count = $sql->getAllRows()->getSize();
        if ($count == 0) {
            throw new Gpf_Exception($this->_('Ping failed'));
        }
        $data = new Gpf_Rpc_Data();
        $data->setValue('status', 'OK');
        return $data;
    }
    
    public static function forwardPostCurlAsync($toUrl) {
        try {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $toUrl);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_FRESH_CONNECT, true);
            curl_setopt($ch, CURLOPT_TIMEOUT_MS, 2000);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $_REQUEST);
            if (curl_exec($ch)===false) {
				Gpf_Log::debug('Curl error: ' . curl_error($ch) . ', code: ' . curl_errno($ch));
			}
            curl_close($ch);
        } catch (Exception $e) {
        }
    }
}
?>
