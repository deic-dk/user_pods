<?php

OCP\JSON::checkLoggedIn();
OCP\JSON::checkAppEnabled('user_pods');
OCP\JSON::callCheck();

$util = new OC_Kubernetes_Util();

if ($_REQUEST['action'] == 'create_pod') {
	if (empty($_POST['yaml_file'])) {
		OCP\JSON::error(array('message' => 'No YAML file specified'));
		exit;
	}
	$yaml_url = $util->rawManifestsURL . trim($_POST['yaml_file']);
	if (!$util::okayCreatePodInput($_POST['input'])) {
		OCP\JSON::error(array('message' => "Bad settings input"));
		exit;
	}
	$response = $util->createPod(OCP\User::getUser(), $yaml_url, $_POST['input']);
	if ($response !== false) {
		OCP\JSON::success($response);
	} else {
		OCP\JSON::error(array('message' => "Failed to create pod"));
	}
} elseif ($_REQUEST['action'] == 'watch_create_pod') {
	$response = $util->watchCreatePod(OCP\User::getUser(), $_POST['pod_name']);
	if ($response !== false) {
		OCP\JSON::success($response);
	} else {
		OCP\JSON::error(array('message' => "Couldn't check for pod ready state"));
	}
} elseif ($_REQUEST['action'] == 'watch_delete_pod') {
	$response = $util->watchDeletePod(OCP\User::getUser(), $_POST['pod_name']);
	if ($response !== false) {
		OCP\JSON::success($response);
	} else {
		OCP\JSON::error(array('message' => "Couldn't check for pod deleted state"));
	}
} elseif ($_REQUEST['action'] == 'delete_pod') {
	$response = $util->deletePod($_REQUEST['pod_name'], OCP\User::getUser());
	if ($response !== false) {
		OCP\JSON::success($response);
	} else {
		OCP\JSON::error(array('message' => "Failed to delete pod."));
	}
} elseif ($_REQUEST['action'] == 'check_manifest') {
	$data = $util->checkManifest($_REQUEST['yaml_file']);
	OCP\JSON::success(array('data' => $data));
} elseif ($_REQUEST['action'] == 'get_pods') {
	$response = $util->getPods(OCP\User::getUser());
	if ($response !== false) {
		OCP\JSON::success(array('data' => $response));
	} else {
		OCP\JSON::error(array('data' => []));
	}
} else {
	OCP\JSON::error(array('message' => 'No action specified'));
}
