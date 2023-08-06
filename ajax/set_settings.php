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
$getContainersPassword = $_POST['getContainersPassword'];
$getContainersURL = $_POST['getContainersURL'];
$trustedUser = $_POST['trustedUser'];

OC_Appconfig::setValue('user_pods', 'publicIP', $publicIP);
OC_Appconfig::setValue('user_pods', 'privateIP', $privateIP);
OC_Appconfig::setValue('user_pods', 'storageDir', $storageDir);
OC_Appconfig::setValue('user_pods', 'manifestsURL', $manifestsURL);
OC_Appconfig::setValue('user_pods', 'rawManifestsURL', $rawManifestsURL);
OC_Appconfig::setValue('user_pods', 'nbViewerPrivateURL', $nbViewerPrivateURL);
OC_Appconfig::setValue('user_pods', 'nbViewerPublicURL', $nbViewerPublicURL);
OC_Appconfig::setValue('user_pods', 'jupyterYamlFile', $jupyterYamlFile);
OC_Appconfig::setValue('user_pods', 'getContainersURL', $getContainersURL);
OC_Appconfig::setValue('user_pods', 'getContainersPassword', $getContainersPassword);
OC_Appconfig::setValue('user_pods', 'trustedUser', $trustedUser);

OCP\JSON::success();
