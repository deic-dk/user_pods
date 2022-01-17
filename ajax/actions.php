<?php

OCP\JSON::checkLoggedIn();
OCP\JSON::checkAppEnabled('user_pods');
OCP\JSON::callCheck();

$util = new OC_Kubernetes_Util();

if($_REQUEST['action']=='create_pod') {
	if(empty($_POST['yaml_file'])){
		OCP\JSON::error(array('data' => array('message'=>'No YAML file specified')));
	}
	$yaml_url = $util->rawManifestsURL.trim($_POST['yaml_file']);
	$message = $util->createPod(OCP\User::getUser(), $yaml_url, trim($_POST['public_key']),
			trim($_POST['storage_path']), trim($_POST['file']));
	OCP\JSON::success(array('message'=>$message));
}
elseif($_REQUEST['action']=='delete_pod') {
	$message = $util->deletePod($_REQUEST['pod_name'], OCP\User::getUser());
	OCP\JSON::success(array('message'=>$message, 'pod'=>$_REQUEST['pod_name']));
}
elseif($_REQUEST['action']=='check_manifest') {
	$data = $util->checkManifest($_REQUEST['yaml_file']);
	OCP\JSON::success(array('data' => $data));
}
elseif($_REQUEST['action']=='get_containers') {
	$data = $util->getContainers(OCP\User::getUser(),
			empty($_REQUEST['pod_names'])?null:$_REQUEST['pod_names']);
	OCP\JSON::success(array('data' => $data));
}
else{
	OCP\JSON::error(array('message'=>'No action specified'));
}