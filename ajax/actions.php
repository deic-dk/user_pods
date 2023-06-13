<?php

OCP\JSON::checkLoggedIn();
OCP\JSON::checkAppEnabled('user_pods');
OCP\JSON::callCheck();

$util = new OC_Kubernetes_Util();

if($_REQUEST['action']=='create_pod'){
	if(empty($_POST['yaml_file'])){
		OCP\JSON::error(array('data' => array('message'=>'No YAML file specified')));
		exit;
	}
	$yaml_url = $util->rawManifestsURL.trim($_POST['yaml_file']);
	$json = $util->createPod(OCP\User::getUser(), $yaml_url, trim($_POST['public_key']),
		trim($_POST['storage_path']), trim($_POST['file']));
	$status = $json['status'];
	$message = $json['data']['message'];
	$name = $json['data']['name'];
	if($status=='success'){
		OCP\JSON::success(array('data' => array('podName' => $name)));
	}
	else{
		OCP\JSON::error(array('data' => array('message'=>'Problem creating pod. '.$name.': '.$message)));
	}
}
elseif($_REQUEST['action']=='delete_pod') {
	$json = $util->deletePod($_REQUEST['pod_name'], OCP\User::getUser());
	$status = $json['status'];
	$message = $json['data']['message'];
	if($status=='success'){
		OCP\JSON::success(array('message'=>$message, 'pod'=>$_REQUEST['pod_name']));
	}
	else{
		\OC_Log::write('user_pods', "Failed deleting pod. " . serialize($json), \OC_Log::ERROR);
		OCP\JSON::error(array('message' => $message, 'pod' => $_REQUEST['pod_name']));
	}
}
elseif($_REQUEST['action']=='check_manifest'){
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
