<?php

$publicIP  = OC_Appconfig::getValue('user_pods', 'publicIP');
$privateIP = OC_Appconfig::getValue('user_pods', 'privateIP');
$storageDir = OC_Appconfig::getValue('user_pods', 'storageDir');
$manifestsURL = OC_Appconfig::getValue('user_pods', 'manifestsURL');
$rawManifestsURL = OC_Appconfig::getValue('user_pods', 'rawManifestsURL');

OCP\JSON::success(array(
		'publicIP' => $publicIP,
		'privateIP' => $privateIP,
		'storageDir' => $storageDir,
		'manifestsURL' => $manifestsURL,
		'rawManifestsURL' => $rawManifestsURL
));
