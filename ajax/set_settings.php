<?php

$publicIP = $_POST['publicIP'];
$privateIP = $_POST['privateIP'];
$storageDir = $_POST['storageDir'];
$manifestsURL = $_POST['manifestsURL'];
$rawManifestsURL = $_POST['rawManifestsURL'];
$nbViewerPrivateURL = $_POST['nbViewerPrivateURL'];
$nbViewerPublicURL = $_POST['nbViewerPublicURL'];
$nbViewerPublicURL = $_POST['nbViewerPublicURL'];
$jupyterYamlFile = $_POST['jupyterYamlFile'];

OC_Appconfig::setValue('user_pods', 'publicIP', $publicIP);
OC_Appconfig::setValue('user_pods', 'privateIP', $privateIP);
OC_Appconfig::setValue('user_pods', 'storageDir', $storageDir);
OC_Appconfig::setValue('user_pods', 'manifestsURL', $manifestsURL);
OC_Appconfig::setValue('user_pods', 'rawManifestsURL', $rawManifestsURL);
OC_Appconfig::setValue('user_pods', 'nbViewerPrivateURL', $nbViewerPrivateURL);
OC_Appconfig::setValue('user_pods', 'nbViewerPublicURL', $nbViewerPublicURL);
OC_Appconfig::setValue('user_pods', 'jupyterYamlFile', $jupyterYamlFile);

OCP\JSON::success();
