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
					if($row['is_dir']){
						// "text" will be overridden - loaded from description.txt
						return ["text"=>$row['filename'], "target"=>$row['url'], "img"=>"cover.png"];
					}
					else{
						return ["text"=>$row['filename'], "target"=>$row['url'], "img"=>"/img/file-jupyter.png"];
					}
				},
				array_filter($groupPublicFileInfos, function($dbRow) use ($section){
					return $dbRow['group']==$section;
			})
	)];
}, $sections);

$ret = [
	"title" => "ScienceNotebooks",
	"subtitle" => "Copy-paste-compute! Share your calculations with colleagues, students and the world. Jupyter Notebooks shared by ScienceData users",
	"text" => "<a href='https://sciencedata.dk/sites/developer/Notebooks/index#publishing_notebooks'>Contribute</a>",
	"show_input" => false,
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
				$info['url'] = $master.'/shared/'.$row['token'];
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
		$fileRow = \OCA\FilesSharding\Lib::dbGetFile($row['item_source']);
		// We need some/any userid to get the id of the directory mimetype
		$dirMimeId = empty($dirMimeId)?getDirMimeId($row['uid_owner']):$dirMimeId;
		// Skip non .ipynb files
		if($fileRow['mimetype']!=$dirMimeId && substr($info['filename'], -6)!='.ipynb'){
			continue;
		}
		$info['is_dir'] = false;
		if($fileRow['mimetype']==$dirMimeId){
			$info['is_dir'] = true;
		}
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

