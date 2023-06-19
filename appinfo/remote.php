<?php

OCP\JSON::checkAppEnabled('user_pods');
require_once('chooser/lib/lib_chooser.php');
require_once('apps/chooser/appinfo/apache_note_user.php');

header("Content-Type: application/json");

$notebookGroup = "notebooks";

$groupFileInfos = dbGetNotebooksAndDirsSharedWithGroup();
$groupPublicInfos = dbGetNotebooksAndDirsSharedPublic($groupFileInfos);
$groupPublicFileInfos = array_values($groupPublicInfos);

$sections = array_unique(array_column($groupPublicFileInfos, 'group'));

$sectionsArray = array_map(function($section) use ($groupPublicFileInfos){
	return ["header"=>dbGetGroupDescription($section), "links"=>array_map(function($row) use ($section, $groupPublicFileInfos){
					if($row['item_type']=='folder'){
						// "text" will be overridden - loaded from description.txt
						// Load cover.png via web interface, not webdav as the latter messes up session
						$coverUrl = str_replace('/public/', '/shared/', $row['url']);
						$coverUrl = preg_replace('|/$|', '', $coverUrl);
						$coverUrl = $coverUrl.'?files=cover.png&download&direct';
						return ["text"=>$row['filename'], "target"=>$row['target'], "url"=>$row['url'], "img"=>$coverUrl, "type"=>$row['item_type']];
					}
					else{
						return ["text"=>$row['filename'], "target"=>$row['target'], "url"=>$row['url'], "img"=>"/img/file-jupyter-o.png", "type"=>$row['item_type']];
					}
				},
				array_filter($groupPublicFileInfos, function($dbRow) use ($section){
					return $dbRow['group']==$section;
			})
	)];
}, $sections);

$ret = [
	"title" => "ScienceNotebooks | Share your calculations",
	"image" => "<img src='/static/img/science_notebooks.png' width='192px'/>",
	"subtitle" => "Jupyter Notebooks from <a href='https://sciencedata.dk/'>ScienceData</a>",
	"text" => "",
	"show_input" => true, // the input field is nice to have - we'll hide it with css/js
	"sections" => $sectionsArray
];

OCP\JSON::encodedPrint($ret);

function dbGetGroupDescription($group){
	$groupInfo = \OC_User_Group_Admin_Util::dbGetGroupInfo($group);
	return $groupInfo['description'];
}

function dbGetNotebooksAndDirsSharedPublic($infoArr) {
	$master = \OCA\FilesSharding\Lib::getMasterURL();
	foreach($infoArr as $fileid=>&$info){
		$sql = "SELECT * FROM `*PREFIX*share` WHERE `share_type` = ? AND `item_source` = ?";
		$arr = array(\OCP\Share::SHARE_TYPE_LINK, $fileid);
		$stmt = OC_DB::prepare($sql);
		$result = $stmt->execute($arr);
		while($row = $result->fetchRow()){
			if(!empty($row['token'])){
				$info['target'] = preg_replace('|^https://|', '/urls/', $master).'public/'.$row['token'].'/?base_name='.substr($row['file_target'], 1);
				$info['url'] = $master.'public/'.$row['token'].'/';
				break;
			}
		}
		if(empty($info['url'])){
			unset($infoArr[$fileid]);
		}
	}
	\OCP\Util::writeLog('user_pods', 'Returning notebooks '.serialize($infoArr), \OCP\Util::WARN);
	return $infoArr;
}

// returns list of rows like [group, owner_uid, owner_display_name, filename, url]
function dbGetNotebooksAndDirsSharedWithGroup() {
	global $notebookGroup;
	$sql = "SELECT * FROM `*PREFIX*share` WHERE `share_type` = ? AND ( `share_with` = ? OR `share_with` LIKE ? )";
	$arr = array(\OCP\Share::SHARE_TYPE_GROUP, $notebookGroup, $notebookGroup."/%");
	$stmt = OC_DB::prepare($sql);
	$result = $stmt->execute($arr);
	$infoArr = array();
	
	while($row = $result->fetchRow()){
		if(!array_key_exists($row['item_source'], $infoArr)){
			$info = [];
		}
		else{
			$info = $infoArr[$row['item_source']];
		}
		$info['group'] = $row['share_with'];
		$info['filename'] = trim($row['file_target'], '/');
		$info['fileid'] = $row['item_source'];
		$info['item_type'] = $row['item_type'];
		$info['owner_uid'] = $row['uid_owner'];
		$info['owner_display_name'] = \OCP\User::getDisplayName($row['uid_owner']);
		$infoArr[$row['item_source']] = $info;
		\OCP\Util::writeLog('user_pods', 'Info '.serialize($infoArr), \OCP\Util::WARN);
	}
	\OCP\Util::writeLog('user_pods', 'Returning notebooks '.serialize($infoArr), \OCP\Util::WARN);
	return $infoArr;
}

function getDirMimeId($user_id){
	$storage = \OC\Files\Filesystem::getStorage('/'.$user_id.'/');
	$cache = $storage->getCache();
	$dirMimeId = $cache->getMimetypeId('httpd/unix-directory');
	return $dirMimeId;
}

