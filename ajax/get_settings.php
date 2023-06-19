<?php

$publicIP  = OC_Appconfig::getValue('user_pods', 'publicIP');
$privateIP = OC_Appconfig::getValue('user_pods', 'privateIP');
$storageDir = OC_Appconfig::getValue('user_pods', 'storageDir');
$manifestsURL = OC_Appconfig::getValue('user_pods', 'manifestsURL');
$rawManifestsURL = OC_Appconfig::getValue('user_pods', 'rawManifestsURL');
$nbViewerPrivateURL = OC_Appconfig::getValue('user_pods', 'nbViewerPrivateURL');
$nbViewerPublicURL = OC_Appconfig::getValue('user_pods', 'nbViewerPublicURL');
$jupyterYamlFile = OC_Appconfig::getValue('user_pods', 'jupyterYamlFile');
$getContainersPassword = OC_Appconfig::getValue('user_pods', 'getContainersPassword');

OCP\JSON::success(array(
		'publicIP' => $publicIP,
		'privateIP' => $privateIP,
		'storageDir' => $storageDir,
		'manifestsURL' => $manifestsURL,
		'rawManifestsURL' => $rawManifestsURL,
		'nbViewerPrivateURL' => $nbViewerPrivateURL,
		'nbViewerPublicURL' => $nbViewerPublicURL,
		'jupyterYamlFile' => $jupyterYamlFile,
		'getContainersPassword' => $getContainersPassword
));
