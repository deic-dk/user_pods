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
	// Notice: $user_ip is currently passed on but eventually not used by run_pod.
	// Passing source IP to a pod does not work in this version of Kubernetes,
	// so changing /etc/hosts.allow inside the pod will not work,
	// since all incoming requests will have the source IP of the gateway.
	$allowed_ip = trim($_POST['allowed_ip']);
	$pod_type = empty($_POST['type'])?'':trim($_POST['type']);
	$yaml_url = $util->rawManifestsURL.trim($_POST['yaml_file']);
	$json = $util->createPod(OCP\User::getUser(), $yaml_url, trim($_POST['public_key']),
			trim($_POST['mount_root']), trim($_POST['mount_path']), trim($_POST['cvmfs_repos']),
			trim($_POST['file']), trim($_POST['setup_script']), trim($_POST['peers']), $allowed_ip, $pod_type);
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
elseif($_REQUEST['action']=='set_allowed_ips') {
	$json = $util->setAllowedIps($_REQUEST['pod_name'], $_REQUEST['ips'], OCP\User::getUser());
	$status = $json['status'];
	$message = $json['data']['message'];
	if($status=='success'){
		OCP\JSON::success(array('message'=>$message, 'pod'=>$_REQUEST['pod_name']));
	}
	else{
		\OC_Log::write('user_pods', "Failed updating pod. " . serialize($json), \OC_Log::ERROR);
		OCP\JSON::error(array('message' => $message, 'pod' => $_REQUEST['pod_name']));
	}
}
elseif($_REQUEST['action']=='check_manifest'){
	$data = $util->checkManifest($_REQUEST['yaml_file']);
	if(!empty($data)){
		OCP\JSON::success(array('data' => $data));
	}
	else{
		OCP\JSON::error(array('message'=>'Not allowed'));
	}
}
elseif($_REQUEST['action']=='get_containers') {
	$data = $util->getContainers(OCP\User::getUser(),
			empty($_REQUEST['pod_names'])?null:$_REQUEST['pod_names']);
	OCP\JSON::success(array('data' => $data));
}
else{
	OCP\JSON::error(array('message'=>'No action specified'));
}
