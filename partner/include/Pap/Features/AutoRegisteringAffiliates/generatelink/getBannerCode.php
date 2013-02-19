<?php

if (!isset($_REQUEST['email']) || $_REQUEST['email'] == '') {
    echo 'Email cannot be empty.';
    return;
}

require_once '../../../../../scripts/bootstrap.php';

$email = $_REQUEST['email'];

if (isset($_REQUEST['bannerid']) && $_REQUEST['bannerid'] != '') {
    $bannerid = $_REQUEST['bannerid'];

    Gpf_Session::create(new Pap_Tracking_ModuleBase(), null, false);
    try {
        $bannerCode = Pap_Features_AutoRegisteringAffiliates_Main::getBannerCode($email, $bannerid);
    } catch (Gpf_DbEngine_NoRowException $e) {
        echo 'Error: banner id doesn\'t exist: ' . $bannerid;
        return;
    }

    echo htmlspecialchars($bannerCode);
} else {
    echo Pap_Features_AutoRegisteringAffiliates_Main::getAffiliateLink($email);
}
?>
