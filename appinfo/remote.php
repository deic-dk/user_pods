<?php

OCP\JSON::checkAppEnabled('user_pods');
OCP\JSON::checkAppEnabled('user_group_admin');
require_once('chooser/lib/lib_chooser.php');
require_once('user_pods/lib/notebooks.php');
require_once('apps/chooser/appinfo/apache_note_user.php');

header("Content-Type: application/json");

$user = \OCP\USER::getUser();

if(empty($user) && !empty($_SERVER['PHP_AUTH_USER']) &&
		\OCA\FilesSharding\Lib::checkIP()){
			$user = $_SERVER['PHP_AUTH_USER'];
}

$notebookGroup = "sciencenotebooks";
$notebookGroupOwner = OC_User_Group_Admin_Util::getGroupOwner($notebookGroup);

$notebooks = new OC_Notebooks($user, $notebookGroup, $notebookGroupOwner);

if(!empty($_REQUEST['action']) && $_REQUEST['action']=='delete' && !empty($_REQUEST['filename']) && !empty($_REQUEST['section'])){
	// Delete notebook published by the logged-in user
	if($notebooks->delete($_REQUEST['section'].'/'.$_REQUEST['filename']) &&
			$notebooks->dbDeletePublishedRecord($_REQUEST['section'].'/'.$_REQUEST['filename'])){
		OCP\JSON::success(array("message" => "Notebook ".$_REQUEST['filename']." deleted for user ".$user));
	}
	else{
		header($_SERVER['SERVER_PROTOCOL'] . ' 500 Internal Server Error');
		OCP\JSON::error(array("message" => "Could not delete notebook ".$_REQUEST['filename']." for user ".$user));
	}
}
elseif(!empty($_REQUEST['action']) && $_REQUEST['action']=='publish' && !empty($_REQUEST['filename']) && !empty($_REQUEST['fileid'])){
	// Publish notebook
	if($notebooks->publish($_REQUEST['section'], $_REQUEST['filename'], $_REQUEST['fileid'], $_REQUEST['group']) && $notebooks->dbAddNotebookPublishedRecord($_REQUEST['section'].'/'.$_REQUEST['filename'])){
		OCP\JSON::success(array("i" => $_REQUEST['i'], "message" => "Notebook ".$_REQUEST['filename']." published for user ".$user));
	}
	else{
		header($_SERVER['SERVER_PROTOCOL'] . ' 500 Internal Server Error');
		OCP\JSON::error(array("message" => "Could not publish notebook ".$_REQUEST['filename']." for user ".$user));
	}
}
else{
	if(!empty($_REQUEST['mynotebooks'])){
		\OCP\Util::writeLog('user_pods', 'Notebooks owned by: '.$user, \OC_Log::WARN);
		// Proxy if not on home server
		if(!empty($user) && OCP\App::isEnabled('files_sharding') && !\OCA\FilesSharding\Lib::onServerForUser($user)){
			$dataServer = \OCA\FilesSharding\Lib::getServerForUser($user, true,
					\OCA\FilesSharding\Lib::$USER_SERVER_PRIORITY_PRIMARY, true);
			$url = rtrim($dataServer, '/').'/'.ltrim($_SERVER['REQUEST_URI'], '/');
			$curl = curl_init();
			curl_setopt($curl, CURLOPT_HEADER, false);
			curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC ) ;
			curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
			curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
			curl_setopt($curl, CURLOPT_USERPWD, $user.":");
			curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($curl, CURLOPT_URL, $url);
			$json_response = curl_exec($curl);
			$status = curl_getinfo($curl);
			curl_close($curl);
			if(empty($status['http_code']) || $status['http_code']===0 || $status['http_code']>=300 ||
					$json_response===null || $json_response===false){
						\OCP\Util::writeLog('user_pods', 'ERROR: bad response from '.$url.' : '.
								serialize($status).' : '.$json_response, \OC_Log::ERROR);
						\OCP\JSON::error(array(
								'status' => $status,
						));
			}
			else{
				echo $json_response;
			}
		}
		else{
			// Get notebooks published by the requested user
			OCP\JSON::encodedPrint($notebooks->dbGetNotebooksPublishedByUser());
		}
	}
	else{
		// Get all notebook directories, i.e. folders shared publicly and with with $notebookGroup.
		// The group owner will keep "official" notebooks in folders shared with this group.
		$publicNotebookFolderInfos = $notebooks->getNotebookDirs();
		$ret = [
				"title" => "ScienceNotebooks | Share your calculations",
				"image" => "<img src='/static/img/science_notebooks.png' width='192px'/>",
				"subtitle" => "Jupyter Notebooks on <a href='https://sciencedata.dk/'>ScienceData</a>",
				"text" => "",
				"show_input" => true, // the input field is nice to have - we'll hide it with css/js
				"sections" => $publicNotebookFolderInfos
		];
		OCP\JSON::encodedPrint($ret);
		exit;
	}
}
