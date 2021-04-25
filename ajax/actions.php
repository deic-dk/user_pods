<?php

OCP\JSON::checkLoggedIn();
OCP\JSON::checkAppEnabled('kubernetes_app');

$user = OCP\User::getUser();

if (isset($_POST['pod_image']) && isset($_POST['github'])) {
	$create = OC_Kubernetes_Util::createPod($_POST['pod_image'], $_POST['ssh'], $_POST['storage'], $_POST['github'], $user);
	OCP\JSON::success();
}
if (isset($_POST['pod_name'])) {
	$delete = OC_Kubernetes_Util::deletePod($_POST['pod_name'], $user);
	OCP\JSON::success();
}
if (isset($_POST['yaml_file'])) {
	$included = OC_Kubernetes_Util::checkImage($_POST['yaml_file'], $_POST['dockerhub'], $_POST['github']);
	OCP\JSON::success(array('data' => array('included' => $included)));
}
if (isset($_GET['pod'])) {
	OC_Kubernetes_Util::getLogs($_GET['pod'], $user);
}
