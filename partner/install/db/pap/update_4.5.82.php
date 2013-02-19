<?php
class pap_update_4_5_82 {
	public function execute() {
		$mobileNotifications = new Pap_Mobile_NotificationTask();
		$mobileNotifications->insertTask();
	}
}
?>
