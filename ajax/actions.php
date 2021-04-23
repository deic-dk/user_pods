<?php

OCP\JSON::checkLoggedIn();
OCP\JSON::checkAppEnabled('kubernetes');
OCP\JSON::callCheck();

$pod = $_POST['pod'];
$uid = OCP\User::getUser();
if ( isset ($pod)){
	$result = OC_Kubernetes::deletePod($pod, $uid);
	OCP\JSON::success();
}

