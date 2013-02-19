<?php
/**
 *   @copyright Copyright (c) 2007 Quality Unit s.r.o.
 *   @author Andrej Harsani
 *   @package GwtPhpFramework
 *   @since Version 1.0.0
 *   $Id: UpdateBuilder.class.php 38213 2012-03-28 08:42:02Z mkendera $
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
class Gpf_SqlBuilder_UpdateBuilder extends Gpf_SqlBuilder_ModifyBuilder {
    public $tableName;

    /**
     * @var Gpf_SqlBuilder_FromClause
     */
    public $from;
    /**
     * @var Gpf_SqlBuilder_SetClause
     */
    public $set;
    /**
     * @var Gpf_SqlBuilder_WhereClause
     */
    public $where;
    /**
     * @var Gpf_SqlBuilder_UpdateLimitClause
     */
    public $limit;

    function __construct() {
        $this->from = new Gpf_SqlBuilder_FromClause();
        $this->set = new Gpf_SqlBuilder_SetClause();
        $this->where = new Gpf_SqlBuilder_WhereClause();
        $this->limit = new Gpf_SqlBuilder_UpdateLimitClause();
    }

    public function initSelect() {
        if(!empty($this->tableName)) {
            $this->from->add($this->tableName);
        }
    }

    public function toString() {
        if($this->from->isEmpty()) {
            return '';
        }
        return "UPDATE ".
        $this->from->toString() .
        $this->set->toString().
        $this->where->toString().
        $this->limit->toString();
    }

    public function update() {
        return $this->execute();
    }

    public function updateOne() {
        $this->executeOne();
    }
}

?>
