<?php
/**
 *   @copyright Copyright (c) 2007 Quality Unit s.r.o.
 *   @author Milos Jancovic
 *   @package PostAffiliatePro
 *   @since Version 1.0.0
 *   $Id: UpdateManager.class.php 18026 2008-05-14 08:07:20Z mbebjak $
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
class Gpf_Log_DeleteEventsTask extends Gpf_Log_DeleteTaskBase {

    public function getName() {
        return $this->_('Delete events');
    }

    protected function execute() {
        $maxLogCount = Gpf_Settings::get(Gpf_Settings_Gpf::AUTO_DELETE_EVENTS_RECORDS_NUM);
        if (($maxLogCount > 0) && ($this->getLogsCount() >=  $maxLogCount)) {
            $logs = Gpf_Db_Table_Logs::getInstance();
            $logs->truncate();
            $this->setDone();
            return;
        }
        if (Gpf_Settings::get(Gpf_Settings_Gpf::AUTO_DELETE_EVENTS) <= 0) {
            $this->setDone();
            return;
        }
        do {      
            $this->checkInterruption();
            $statement = $this->deleteEvents();      
        } while ($statement->affectedRows() > 0);
        $this->setDone();
    }
    
    private function getLogsCount() {
        $select = new Gpf_SqlBuilder_SelectBuilder();
        $select->select->add('count(*)', 'numrows');
        $select->from->add(Gpf_Db_Table_Logs::getName());
        
        $row = $select->getOneRow();
        
        return $row->get('numrows');
    }

    private function deleteEvents() {
        $delete = new Gpf_SqlBuilder_DeleteBuilder();
        $delete->from->add(Gpf_Db_Table_Logs::getName());
        $delete->where->add(Gpf_Db_Table_Logs::CREATED, '<', $this->getLastDate(Gpf_Settings::get(Gpf_Settings_Gpf::AUTO_DELETE_EVENTS))->toDateTime());
        $delete->limit->set('', Gpf_Log_DeleteTaskBase::DELETE_LIMIT);
        return $delete->delete();
    }
}
