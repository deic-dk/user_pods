<?php

require_once 'lib/base.php';
require_once('apps/chooser/appinfo/apache_note_user.php');

\OC_Util::setupFS();

$file = $_GET["file"];
$dir = $_GET["dir"];
// If $owner is set, we were called by /themes/deic_theme_oc7/apps/files/js/filelist.php
// using linkTo() defined there.
// I.e. $dir is a directory shared with me.
$user_id = OCP\USER::getUser();
$id = isset($_GET['id']) ? $_GET['id'] : '';
$owner = isset($_GET['owner']) ? $_GET['owner'] : '';
$group = isset($_GET['group']) ? $_GET['group'] : '';

if(OCP\App::isEnabled('files_sharding') && empty($owner) &&
		OCA\FilesSharding\Lib::inDataFolder($dir, $user_id, $group)){
			$dataServer = \OCA\FilesSharding\Lib::getServerForFolder($dir, $user_id, true);
}
if(empty($dataServer) && OCP\App::isEnabled('files_sharding') && !empty($owner) /* && !empty($id)*/){
	if(OCA\FilesSharding\Lib::inDataFolder($dir, $owner, $group)){
		$dataServer = \OCA\FilesSharding\Lib::getServerForFolder($dir, $owner, true);
	}
	elseif(!empty($owner) && !\OCA\FilesSharding\Lib::onServerForUser($owner)){
		$dataServer = \OCA\FilesSharding\Lib::getServerForUser($owner, true,
				\OCA\FilesSharding\Lib::$USER_SERVER_PRIORITY_PRIMARY, true);
	}
}
if(!empty($dataServer) && !\OCA\FilesSharding\Lib::isServerMe($dataServer)){
	$url = urlencode($dataServer.
			'/apps/user_pods/nbviewer.php?file='.$file.
			($dir?'&dir='.$dir:'').($owner?'&owner='.$owner:'').($id?'&id='.$id:'').
			($group?'&group='.$group:''));
	$proxy_url = \OC::$WEBROOT.'/apps/files_sharding/download_proxy.php?url='.$url.'&mode=native';
	\OC_Response::redirect($proxy_url);
	exit;
}

$file_name_with_full_path = \OCA\FilesSharding\Lib::getFullPath($file, $dir, $owner, $id, $group);
$nbViewerPrivateURL = OC_Appconfig::getValue('user_pods', 'nbViewerPrivateURL');
$nbViewerPublicURL = OC_Appconfig::getValue('user_pods', 'nbViewerPublicURL');
$jupyterYamlFile = OC_Appconfig::getValue('user_pods', 'jupyterYamlFile');

\OCP\Util::writeLog('user_pods', 'POSTing '.$file_name_with_full_path.' to '.$nbViewerPrivateURL."/create/", \OC_Log::WARN);

// POST the file to nbviewer
$cFile = curl_file_create($file_name_with_full_path, "application/x-ipynb-json", $file);
$post = array('nb'=> $cFile);
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $nbViewerPrivateURL."/create/");
curl_setopt($ch, CURLOPT_POST,1);
curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);
curl_setopt($ch, CURLOPT_UNRESTRICTED_AUTH, TRUE);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_HEADER, 0);

$result = curl_exec($ch);
curl_close ($ch);

$result = str_replace('src="/static/', 'src="'.$nbViewerPublicURL.'/static/', $result);
$result = str_replace('href="/static/', 'href="'.$nbViewerPublicURL.'/static/', $result);
$result = str_replace('<head>', "<head>\n<meta name='Jupyter_YAML_File' content='".$jupyterYamlFile."'", $result);

echo($result);

