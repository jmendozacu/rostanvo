<?php
/**
 * Processes ISecure request
 */

require_once '../../scripts/bootstrap.php';

Gpf_Session::create(new Pap_Tracking_ModuleBase());

$tracker = ISecure_Tracker::getInstance();
$tracker->process();
?>
