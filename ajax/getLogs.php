<?php

OCP\User::checkLoggedIn();
OCP\App::checkAppEnabled('user_pods');

$user = OC_User::getUser();
$file = isset($_GET['file']) ? $_GET['file'] : null;

if(!empty($file) && !empty($user)){
	OC_Kubernetes_Util::getLogs($file, $user);
}
