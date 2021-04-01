<?php

OCP\JSON::checkLoggedIn();
OCP\JSON::checkAppEnabled('user_pods');
OCP\JSON::callCheck();

if ( isset($_POST['pod_image']) ) {
	$create = OC_Kubernetes_Util::createPod($_POST['pod_image'], $_POST['ssh'], $_POST['storage'], OCP\User::getUser());
	OCP\JSON::success();
}
if (isset($_POST['pod_name']) ) {
	$delete = OC_Kubernetes_Util::deletePod($_POST['pod_name'], OCP\User::getUser());
	OCP\JSON::success();
}
if (isset($_POST['yaml_file'])) {
	$included = OC_Kubernetes_Util::checkImage($_POST['yaml_file']);
	OCP\JSON::success(array('data' => array('included'=>$included)));
}
