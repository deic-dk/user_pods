<?php

class OC_Kubernetes_Util {
    private static $URI = 'http://10.0.0.12/';
     /**
     * @brief Get all user's pods
     * @param  string $uid Name of the user
     * @return array  with pod names of a user
     */
    public static function getUserPods( $uid )
    {
  	$table = array();		
	$complete_uri = OC_Kubernetes_Util::$URI."get_containers.php?user_id=".$uid;
	$response = file_get_contents($complete_uri);
	$rows = explode("\n", $response);
	array_pop($rows);
	foreach ($rows as $row) {
		$container = array("pod_name"=>"","container_name"=>"","status"=>"", "ssh_port"=>"", "https_port"=> "", "uri"=>"");
		$cells = explode("|", $row);

		$container["pod_name"] = $cells[0] ?? "";
		$container["container_name"] = $cells[1] ?? "";
		$container["status"] = $cells[7] ?? "";
		$container["ssh_port"] = $cells[8] ?? "";
		$container["https_port"] = $cells[9] ?? "";
		$container["uri"] = $cells[10] ?? "";
		array_push($table, $container);
	}	
        return $table;
    }

    public static function addRow($index, $value)
    {
	echo "<td id=\"$index\"  class=\"$value\">
                 <div class=\"$index\">
                      <span id=\"$index\">$value</span>
                 </div>
              </td>";
    }


    public static function getImages()
    {
	$dir =  'pod_manifests';
	$dir = \OC\Files\Filesystem::normalizePath($dir);

	try {
		$dirInfo = \OC\Files\Filesystem::getFileInfo($dir);
		if (!$dirInfo || !$dirInfo->getType() === 'dir') {
			header("HTTP/1.0 404 Not Found");
			exit();
		}	

		$permissions = $dirInfo->getPermissions();

		$sortAttribute = isset($_GET['sort']) ? $_GET['sort'] : 'name';
		$sortDirection = isset($_GET['sortdirection']) ? ($_GET['sortdirection'] === 'desc') : false;

		// make filelist

		$files = \OCA\Files\Helper::getFiles($dir, $sortAttribute, $sortDirection);
		$data = \OCA\Files\Helper::formatFileInfos($files);

		$filenames = array();
		foreach ($data as $file) {
			array_push($filenames, $file['name']);
		}
		return $filenames;

	} catch (\OCP\Files\StorageNotAvailableException $e) {
		\OCP\Util::logException('files', $e);
		OCP\JSON::error(array(
			'data' => array(
			'exception' => '\OCP\Files\StorageNotAvailableException',
			'message' => $l->t('Storage not available')
			)
		));
	} catch (\OCP\Files\StorageInvalidException $e) {
		\OCP\Util::logException('files', $e);
		OCP\JSON::error(array(
			'data' => array(
			'exception' => '\OCP\Files\StorageInvalidException',
			'message' => $l->t('Storage invalid')
			)
		));
	} catch (\Exception $e) {
		\OCP\Util::logException('files', $e);
		OCP\JSON::error(array(
			'data' => array(
			'exception' => '\Exception',
			'message' => $l->t('Unknown error')
			)
		));
	}

  }

  public static function checkImage($yaml_file)
  {     
	$test = '/tank/data/owncloud/kerverous/files/pod_manifests/'.$yaml_file;

	$has_ssh = false;
	$has_mount = false;

	if( strpos(file_get_contents($test),"SSH_PUBLIC_KEY") !== false) {
		$has_ssh = true;	
	}

	if( strpos(file_get_contents($test), "mountPath") != false) {
		$has_mount = true;
	}
	return array($has_ssh, $has_mount);
  }

  public static function createPod($yaml_file, $ssh_key, $storage_path, $uid)
  {
	$complete_uri = self::$URI."run_pod.php?user_id=".$uid."&yaml_uri=/files/pod_manifests/".$yaml_file;
	if (is_null($ssh_key) != false) {
		$encoded_key = rawurlencode($ssh_key);
		if (is_null($storage_path) != false) {
			$complete_uri = $complete_uri."&storage_path=".$storage_path."&public_key=".$encoded_key;
		} else  {
			$complete_uri = $complete_uri."&public_key=".$encoded_key;
		}
	} else {
		if (is_null($storage_path) != false) {
			$complete_uri = $complete_uri."&storage_path=".$storage_path;
		}
	}

	$response = file_get_contents($complete_uri);
	// TODO Add exceptions and handling
	return $response;
  } 

  private static function getAppDir($user){
	\OC_User::setUserId($user);
	\OC_Util::setupFS($user);
	$fs = \OCP\Files::getStorage('kubernetes_app');
	if(!$fs){
		\OC_Log::write('kubernetes_app', "ERROR, could not access files of user ".$user, \OC_Log::ERROR);
		return null;
	}
	return $fs->getLocalFile('/');
  }

  public static function deletePod($pod_name, $uid) 
  {
	  $complete_uri = OC_Kubernetes_Util::$URI."delete_pod.php?user_id=".$uid."&pod=".$pod_name;
	  $response = file_get_contents($complete_uri);
	  return $response;
  }

  public static function getLogs($pod_name, $uid)
  {
	  $file_path = self::getAppDir($uid)."/pod_logs/";

	  if (!is_dir($file_path))
	  {
    		mkdir($file_path, 0750, true);
	  }	
	  
	  $complete_uri = OC_Kubernetes_Util::$URI."get_pod_logs.php?user_id=".$uid."&pod=".$pod_name;
	  $response = file_get_contents($complete_uri);

	  $file = $file_path.$pod_name.".log";
	  $logfile = fopen($file, "w") or die("Unable to open file!");
	  fwrite($logfile, $response);
	  fclose($logfile);

	  $type = filetype($file);
	  header("Content-type: $type");
  	  header("Content-Disposition: attachment;filename=$pod_name.log");
       	  readfile($file);
  }
}
