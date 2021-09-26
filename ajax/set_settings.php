<?php

$publicIP = $_POST['publicIP'];
$privateIP = $_POST['privateIP'];
$storageDir = $_POST['storageDir'];
$manifestsURL = $_POST['manifestsURL'];
$rawManifestsURL = $_POST['rawManifestsURL'];

$ret = true;

OC_Appconfig::setValue('user_pods', 'publicIP', $publicIP) || empty($publicIP) || $ret = false;
OC_Appconfig::setValue('user_pods', 'privateIP', $privateIP) || empty($privateIP) || $ret = false;;
OC_Appconfig::setValue('user_pods', 'storageDir', $storageDir) || empty($storageDir) || $ret = false;;
OC_Appconfig::setValue('user_pods', 'manifestsURL', $manifestsURL) || empty($manifestsURL) || $ret = false;;
OC_Appconfig::setValue('user_pods', 'rawManifestsURL', $rawManifestsURL) || empty($rawManifestsURL) || $ret = false;;

if($ret){
	OCP\JSON::success();
}
else{
	OCP\JSON::error();
}
