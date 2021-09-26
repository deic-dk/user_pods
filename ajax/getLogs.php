<?php

OCP\User::checkLoggedIn();
OCP\App::checkAppEnabled('user_pods');

$util = new OC_Kubernetes_Util();

$user = OC_User::getUser();
$file = isset($_GET['file']) ? $_GET['file'] : null;

if(!empty($file) && !empty($user)){
	$util->getLogs($file, $user);
}
