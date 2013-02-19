<?php
/**
 * Processes PagosOnline request
 */

require_once '../../scripts/bootstrap.php';

Gpf_Session::create(new Pap_Tracking_ModuleBase());

$tracker = PagosOnline_Tracker::getInstance();
$tracker->process();
?>
