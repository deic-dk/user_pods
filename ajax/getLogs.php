<?php
OCP\User::checkLoggedIn();
OCP\APP::checkAppEnabled('kubernetes');


$uid = OCP\User::getUser();
$pod = isset($_GET['pod']) ? $_GET['pod'] : null;


	$result = OC_Kubernetes::getLogs($pod, $uid);
	

  	header('Content-Type: application/octet-stream');
	header('Content-Disposition: attachment; filename="'.basename($result).'"');
	header('Content-Length: ' . filesize($result));
	readfile($result); 
      
  

