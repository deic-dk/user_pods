<?php

OCP\JSON::checkLoggedIn();
OCP\JSON::checkAppEnabled('user_pods');
OCP\JSON::callCheck();

$util = new OC_Kubernetes_Util();

if($_REQUEST['action']=='create_pod') {
	if(empty($_POST['yaml_file'])){
		OCP\JSON::error(array('message'=>'No YAML file specified'));
        exit;
	}
	$yaml_url = $util->rawManifestsURL.trim($_POST['yaml_file']);
	list('error' => $error, 'pod_name' => $pod_name) = $util->createPod(OCP\User::getUser(), $yaml_url, $_POST['input']);
	//	file_put_contents("joshualog.txt", "error length: " . strlen($error) . ", podname: $pod_name");
	if (strlen($error) == 0 && strlen($pod_name) != 0) {
		OCP\JSON::success(array('pod_name' => $pod_name));
	}
	else {
		OCP\JSON::error(array('message'=>$error));
	}
}
elseif($_REQUEST['action']=='delete_pod') {
	$message = $util->deletePod($_REQUEST['pod_name'], OCP\User::getUser());
    if ($message === '<h1>OK</h1>') {
        OCP\JSON::success(array('message'=>$message, 'pod'=>$_REQUEST['pod_name']));
    }
    else {
        OCP\JSON::error(array('message' => $message, 'pod' => $_REQUEST['pod_name']));
    }
}
elseif($_REQUEST['action']=='check_manifest') {
	$data = $util->checkManifest($_REQUEST['yaml_file']);
	OCP\JSON::success(array('data' => $data));
}
elseif($_REQUEST['action']=='get_pods') {
	list('data' => $data, 'code' => $code) = $util->getPods(OCP\User::getUser());
	if ($code == 200) {
		OCP\JSON::success(array('data' => $data));
	} else {
		OCP\JSON::error(array('data' => []));
	}
}
else{
	OCP\JSON::error(array('message'=>'No action specified'));
}
