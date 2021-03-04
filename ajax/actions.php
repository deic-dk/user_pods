<?php


OCP\JSON::checkLoggedIn();
OCP\JSON::checkAppEnabled('kubernetes_app');
OCP\JSON::callCheck();

if ( isset($_POST['pod_image']) ) {
	$create = OC_Kubernetes_Util::createPod($_POST['pod_image'], OCP\User::getUser());
	OCP\JSON::success();
}
