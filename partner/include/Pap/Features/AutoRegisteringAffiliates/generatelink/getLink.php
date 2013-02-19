<?php

if (!isset($_REQUEST['email']) || $_REQUEST['email'] == '') {
    echo 'Email cannot be empty.';
    return;
}

require_once '../../../../../scripts/bootstrap.php';

$email = $_REQUEST['email'];
echo Pap_Features_AutoRegisteringAffiliates_Main::getAffiliateLink($email);

?>
