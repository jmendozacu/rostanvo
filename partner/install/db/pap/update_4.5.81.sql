ALTER TABLE `qu_pap_recurringcommissions` ADD `orderid` VARCHAR( 60 ) NULL DEFAULT NULL AFTER `transid`;
UPDATE qu_pap_recurringcommissions r,
qu_pap_transactions t SET r.orderid = t.orderid WHERE r.transid = t.transid AND r.orderid IS NULL;