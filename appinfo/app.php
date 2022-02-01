<?php

OC::$CLASSPATH['OC_Kubernetes_Util'] ='apps/user_pods/lib/util.php';

OCP\App::addNavigationEntry(
	array( 'id'    => 'user_pods',
		'order' => 6,
		'icon'  => OCP\Util::imagePath( 'user_pods', 'kubernetes.png' ),
		'href'  => OCP\Util::linkTo( 'index.php/apps/user_pods' , 'index.php' ),
		'name'  => 'Pods'
	)
);

OCP\App::registerAdmin('user_pods', 'settings');
if(!isset($_SERVER['REQUEST_URI']) || strpos(ltrim($_SERVER['REQUEST_URI'], "/"),
		ltrim(OC::$WEBROOT."/index.php/apps/user_pods/","/"))!==0){
	OCP\Util::addScript('user_pods','fileactions');
	OCP\Util::addStyle('user_pods','nbviewer');
}
