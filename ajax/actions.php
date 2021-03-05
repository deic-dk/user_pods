<?php


OCP\JSON::checkLoggedIn();
OCP\JSON::checkAppEnabled('kubernetes_app');
OCP\JSON::callCheck();

if ( isset($_POST['pod_image']) ) {
	$create = OC_Kubernetes_Util::createPod($_POST['pod_image'], $_POST['ssh'], OCP\User::getUser());
	OCP\JSON::success();
}
if (isset($_POST['pod_name']) ) {
	$delete = OC_Kubernetes_Util::deletePod($_POST['pod_name'], OCP\User::getUser());
	OCP\JSON::success();
}
