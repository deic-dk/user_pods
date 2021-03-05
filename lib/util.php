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


  public static function createPod($yaml_file, $ssh_key, $uid)
  {
	$encoded_key = rawurlencode ($ssh_key);
	$complete_uri = OC_Kubernetes_Util::$URI."run_pod.php?user_id=".$uid."&storage_path=www&public_key=".$encoded_key."&yaml_uri=/files/pod_manifests/".$yaml_file;
	$response = file_get_contents($complete_uri);
	// TODO Add exceptions and handling
	return $response;
  } 
}
