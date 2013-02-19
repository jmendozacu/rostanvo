<?php
/**
 * Processes Recurly request
 */

require_once '../../scripts/bootstrap.php';

Gpf_Session::create(new Pap_Tracking_ModuleBase());

$tracker = Recurly_Tracker::getInstance();
$tracker->process();
?>
