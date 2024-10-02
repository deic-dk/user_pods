<?php

class OC_Notebooks {
	
	private $user;
	private $notebookGroup;
	private $notebookGroupOwner;
	
	function __construct($user, $notebookGroup, $notebookGroupOwner){
		$this->user = $user;
		$this->notebookGroup = $notebookGroup;
		$this->notebookGroupOwner = $notebookGroupOwner;
	}

	///////////////////////////////////////////////////////////////////////////////
	// Central DB functions
	///////////////////////////////////////////////////////////////////////////////

	public static function dbGetNotebookDirsSharedPublic($infoArr) {
		$master = \OCA\FilesSharding\Lib::getMasterURL();
		foreach($infoArr as $index=>&$info){
			$sql = "SELECT * FROM `*PREFIX*share` WHERE `share_type` = ? AND `item_source` = ?";
			$arr = array(\OCP\Share::SHARE_TYPE_LINK, $info['fileid']);
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
				unset($infoArr[$index]);
			}
			else{
				$coverUrl = str_replace('/public/', '/shared/', $info['url']);
				$coverUrl = preg_replace('|/$|', '', $coverUrl);
				$coverUrl = $coverUrl.'?files=cover.png&download&direct';
				$info['img'] = $coverUrl;
				$textUrl = str_replace('/public/', '/shared/', $info['url']);
				$textUrl = preg_replace('|/$|', '', $textUrl);
				$textUrl = $textUrl.'?files=description.txt&download&direct';
				$curl = curl_init();
				curl_setopt($curl, CURLOPT_HEADER, false);
				curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
				curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
				curl_setopt($curl, CURLOPT_URL, $textUrl);
				$text = curl_exec($curl);
				curl_close($curl);
				$info['text'] = $text;
			}
		}
		\OCP\Util::writeLog('user_pods', 'Returning public notebook dirs '.serialize($infoArr), \OCP\Util::WARN);
		return array_values($infoArr);
	}
	
	// returns list of rows like [group, owner_uid, owner_display_name, filename, url]
	public static function dbGetDirsSharedWithGroup($nbGroup) {
		$sql = "SELECT * FROM `*PREFIX*share` WHERE `share_type` = ? AND `share_with` = ? AND `item_type` = ?";
		$arr = array(\OCP\Share::SHARE_TYPE_GROUP, $nbGroup, 'folder');
		$stmt = OC_DB::prepare($sql);
		$result = $stmt->execute($arr);
		$infoArr = array();
		
		while($row = $result->fetchRow()){
			$filename = trim($row['file_target'], '/');
			$info = [];
			$info['fileid'] = $row['item_source'];
			$info['owner_uid'] = $row['uid_owner'];
			$info['owner_display_name'] = \OCP\User::getDisplayName($row['uid_owner']);
			$info['filename'] = $filename;
			$infoArr[] = $info;
			\OCP\Util::writeLog('user_pods', 'Info '.serialize($infoArr), \OCP\Util::INFO);
		}
		\OCP\Util::writeLog('user_pods', 'Returning notebook dirs '.serialize($infoArr), \OCP\Util::WARN);
		return array_values($infoArr);
	}
	
	public function getNotebookDirs(){
		if(!\OCP\App::isEnabled('files_sharding') || \OCA\FilesSharding\Lib::isMaster()){
			$notebookFolderInfos = self::dbGetDirsSharedWithGroup($this->notebookGroup);
			$publicNotebookFolderInfos = self::dbGetNotebookDirsSharedPublic($notebookFolderInfos);
			return $publicNotebookFolderInfos;
		}
		else{
			$publicNotebookFolderInfos = \OCA\FilesSharding\Lib::ws('get_notebook_dirs', array(), false, true, null, 'user_pods');
			\OCP\Util::writeLog('user_pods', 'Got notebook dirs json '.serialize($publicNotebookFolderInfos), \OCP\Util::INFO);
			return $publicNotebookFolderInfos;
		}
	}
	
	///////////////////////////////////////////////////////////////////////////////
	// User DB/prefs functions
	///////////////////////////////////////////////////////////////////////////////
	
	public function dbGetNotebooksPublishedByUser(){
		$publishedNotebooksJson = OC_Preferences::getValue($this->user, 'user_pods', 'published_notebooks', '[]');
		$publishedNotebooks = json_decode($publishedNotebooksJson, true);
		return $publishedNotebooks;
	}
	
	public function dbDeletePublishedRecord($notebook){
		$publishedNotebooksJson = OC_Preferences::getValue($this->user, 'user_pods', 'published_notebooks', '[]');
		$publishedNotebooks = json_decode($publishedNotebooksJson, true);
		if(($key = array_search($notebook, $publishedNotebooks))!== false){
			unset($publishedNotebooks[$key]);
		}
		return OC_Preferences::setValue($this->user, 'user_pods', 'published_notebooks', json_encode($publishedNotebooks));
	}
	
	public function getPublishedNotebooks(){
		$notebooks = [];
		$query = \OC_DB::prepare('SELECT `configvalue` FROM `*PREFIX*preferences` WHERE `appid` = ? AND `configkey` = ? and userid = ?');
		$result = $query->execute(Array('user_pods', 'published_notebooks', $this->user));
		$results = $result->fetchAll();
		foreach($results as $row){
			$notebooks = array_merge($notebooks, json_decode($row['configvalue'], true));
		}
		return $notebooks;
	}
	
	public function dbAddNotebookPublishedRecord($notebook){
		$publishedNotebooks = $this->getPublishedNotebooks();
		if(in_array($notebook, $publishedNotebooks)){
			\OCP\Util::writeLog('user_pods', 'ERROR: There is already a notebook published by '.
					$this->user.' by the name '.$notebook, \OC_Log::WARN);
			return false;
		}
		$publishedNotebooks[] = $notebook;
		return OC_Preferences::setValue($this->user, 'user_pods', 'published_notebooks', json_encode($publishedNotebooks));
	}
	
	public function publish($section, $fileName, $fileID, $group){
		// PUT file in the folder backing $section on the home server of the owner of the folder as that owner (allowed w/o password via webdav if we use the 10.0 subnet).
		$publicNotebookFolderInfos = $this->getNotebookDirs();
		$ok = false;
		foreach($publicNotebookFolderInfos as $i=>$row){
			if($row['filename']==$section){
				$ok = true;
				break;
			}
		}
		if(!$ok){
			\OCP\Util::writeLog('user_pods', 'ERROR: no such section, '.$section.' in '.serialize($publicNotebookFolderInfos), \OC_Log::WARN);
			return false;
		}
		$sectionFolderID = $row['fileid'];
		$folderOwner = $row['owner_uid'];
		if($folderOwner!=$this->notebookGroupOwner){
			\OCP\Util::writeLog('user_pods', 'ERROR: '.$folderOwner.'!='.$this->notebookGroupOwner, \OC_Log::WARN);
			return false;
		}
		/*No group: We assume that the folders hosting the system sections are in the 'Home' (/files/) of the group owner. */
		$info = \OCA\FilesSharding\Lib::getFileInfo('', $folderOwner, $sectionFolderID, '', $folderOwner, '');
		$sectionFolderPath = str_replace('/'.$folderOwner.'/files/', '', $info['path']);
		$folderOwnerServer = \OCA\FilesSharding\Lib::getServerForUser($folderOwner, true);
		\OC_User::setUserId($this->user);
		\OC_Util::setupFS($this->user);
		$fileFullPath = \OCA\FilesSharding\Lib::getFullPath($fileName, '', $this->user, $fileID, $group);
		$url = $folderOwnerServer.'/files/'.$sectionFolderPath.'/'.$fileName;
		// First check if there's already a file of that name in that folder
		\OCP\Util::writeLog('user_pods', 'Checking '.$fileFullPath.' to ScienceNotebooks as '.$this->user.":".$folderOwnerServer.' to '.$url, \OC_Log::WARN);
		$curl = curl_init();
		curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC ) ;
		curl_setopt($curl, CURLOPT_USERPWD, $folderOwner.":");
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curl, CURLOPT_BINARYTRANSFER, true);
		curl_setopt($curl, CURLOPT_URL, $url);
		curl_setopt($curl, CURLOPT_NOBODY, true);
		curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
		$json_response = curl_exec($curl);
		$status = curl_getinfo($curl);
		if(!empty($status['http_code']) && $status['http_code']<300){
			\OCP\Util::writeLog('user_pods', 'File already exists '.$url, \OC_Log::WARN);
			return false;
		}
		
		\OCP\Util::writeLog('user_pods', 'Uploading '.$fileFullPath.' to ScienceNotebooks as '.$this->notebookGroupOwner.":".$folderOwnerServer.' to '.$url, \OC_Log::WARN);
	
		$cFile = fopen($fileFullPath, "rb");
		curl_setopt($curl, CURLOPT_PUT, true);
		curl_setopt($curl, CURLOPT_INFILE, $cFile);
		curl_setopt($curl, CURLOPT_INFILESIZE, filesize($fileFullPath));
	
		$json_response = curl_exec($curl);
		$status = curl_getinfo($curl);
		curl_close($curl);
		
		if(empty($status['http_code']) || $status['http_code']===0 || $status['http_code']>=300 ||
				$json_response===null || $json_response===false){
			\OCP\Util::writeLog('user_pods', 'ERROR: bad response from '.$url.' : '.
					serialize($status).' : '.$json_response, \OC_Log::ERROR);
			return false;
		}
		return true;
	}
	
	// The only notebooks we allow to be deleted by this script are the ones owned by the owner of the group 'notebooks'
	public function delete($notebook){
		
		if(!empty($this->user) && OCP\App::isEnabled('files_sharding') && !\OCA\FilesSharding\Lib::onServerForUser($this->user)){
			$dataServer = \OCA\FilesSharding\Lib::getServerForUser($this->user, false,
					\OCA\FilesSharding\Lib::$USER_SERVER_PRIORITY_PRIMARY, true);
			$url = rtrim($dataServer, '/').'/'.ltrim($_SERVER['REQUEST_URI'], '/');
			\OC_Response::redirect($url);
			exit;
		}
		
		$notebooks = $this->dbGetNotebooksPublishedByUser();
		if(!in_array($notebook, $notebooks)){
			\OCP\Util::writeLog('user_pods', 'ERROR: Notebook '.$notebook." not registered. ".serialize($notebooks), \OC_Log::ERROR);
			return false;
		}
		$section = preg_replace("|^/*([^/]+)/(.*)|", "$1", $notebook);
		$filename = preg_replace("|^/*([^/]+)/(.*)|", "$2", $notebook);
		if(empty($section) || $section==$notebook){
			\OCP\Util::writeLog('user_pods', 'ERROR: cannot delete '.$notebook.". Cannot parse base folder / section, ".$section, \OC_Log::ERROR);
			return false;
		}
		$publicNotebookFolderInfos = $this->getNotebookDirs();
		// section is just the folder name
		$ok = false;
		foreach($publicNotebookFolderInfos as $i=>$row){
			if($row['filename']==$section){
				$ok = true;
				break;
			}
		}
		if(!$ok){
			\OCP\Util::writeLog('user_pods', 'ERROR: cannot delete '.$notebook.". Share not found:".$section, \OC_Log::ERROR);
			return false;
		}
		$sectionFolderID = $row['fileid'];
		/*No group: We assume that the folders hosting the system sections are in the 'Home' (/files/) of the group owner. */
		$info = \OCA\FilesSharding\Lib::getFileInfo('', $this->notebookGroupOwner, $sectionFolderID, '', $this->notebookGroupOwner, '');
		$sectionFolderPath = preg_replace('|^/*'.$this->notebookGroupOwner.'/files|', '/', $info['path']);
		$folderOwnerServer = \OCA\FilesSharding\Lib::getServerForUser($this->notebookGroupOwner, true);
		$url = $folderOwnerServer.'/files'.$sectionFolderPath.'/'.$filename;
		
		\OCP\Util::writeLog('user_pods', 'Deleting '.$info['path'].' --> '.$url.' as '.$this->user.":".$folderOwnerServer, \OC_Log::WARN);
		$curl = curl_init();
		curl_setopt($curl, CURLOPT_HEADER, false);
		curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC ) ;
		curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($curl, CURLOPT_USERPWD, $this->notebookGroupOwner.":");
		curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "DELETE");
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curl, CURLOPT_URL, $url);
		
		$json_response = curl_exec($curl);
		$status = curl_getinfo($curl);
		curl_close($curl);
		
		if(empty($status['http_code']) || $status['http_code']===0 || $status['http_code']>=300 ||
				$json_response===null || $json_response===false){
					\OCP\Util::writeLog('user_pods', 'ERROR: bad response from '.$url.' : '.
							serialize($status).' : '.$json_response, \OC_Log::ERROR);
					return false;
		}
		return true;
	}
}
