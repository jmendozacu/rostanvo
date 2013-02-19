<?php

class pap_update_4_2_7 extends Gpf_Object {
    public function execute() {
        $this->createDatabase()->execute('ALTER TABLE qu_pap_commissiontypes ADD countrycodes TEXT NULL DEFAULT NULL');
        $this->createDatabase()->execute('ALTER TABLE qu_pap_commissiontypes ADD parentcommtypeid varchar(8) NULL DEFAULT NULL;');
        $this->createDatabase()->execute("ALTER TABLE qu_pap_commissiontypes ADD COLUMN savezerocommission CHAR(1) NULL DEFAULT 'Y';");

        $template = new Pap_Mail_PayDayReminder_PayDayReminder();
        $template->setup(Gpf_Session::getAuthUser()->getAccountId());

        $this->createDatabase()->execute('ALTER TABLE qu_pap_commissiontypes DROP countrycodes');
        $this->createDatabase()->execute('ALTER TABLE qu_pap_commissiontypes DROP parentcommtypeid');
        $this->createDatabase()->execute('ALTER TABLE qu_pap_commissiontypes DROP savezerocommission');
    }   
}
?>
