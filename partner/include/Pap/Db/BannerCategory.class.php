<?php
/**
 *   @copyright Copyright (c) 2007 Quality Unit s.r.o.
 *   @author Matej Kendera
 *   @package PostAffiliatePro
 *   @since Version 1.0.0
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
class Pap_Db_BannerCategory extends Gpf_DbEngine_Row {

    private $name;
    private $state;

    function init() {
        $this->setTable(Pap_Db_Table_BannersCategories::getInstance());
        parent::init();
    }

    public function setId($value) {
        $this->set(Pap_Db_Table_BannersCategories::CATEGORYID, $value);
    }

    public function setDescription($value) {
        $this->set(Pap_Db_Table_BannersCategories::DESCRIPTION, $value);
    }

    public function setImage($value) {
        $this->set(Pap_Db_Table_BannersCategories::IMAGE, $value);
    }

    public function getName($value) {
        return $this->name;
    }
}

?>
