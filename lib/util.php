<?php

class OC_Kubernetes_Util {
	private static $KUBE_URL = 'http://10.0.0.12/';

	public static function getUserPods($uid){
	$table = array();
	// First get fields
	// pod_name|container_name|image_name|pod_ip|node_ip|owner|age(s)|status|ssh_port|https_port|uri
	$url = OC_Kubernetes_Util::$KUBE_URL."get_containers.php?fields=true&user_id=".$uid;
	$response = file_get_contents($url);
	$fields = explode("|", $response);
	// Now the values
	$url = OC_Kubernetes_Util::$KUBE_URL."get_containers.php?user_id=".$uid;
	$response = file_get_contents($url);
	$rows = explode("\n", $response);	
	foreach ($rows as $row) {
		if(empty($row)){
			continue;
		}
		$values = explode("|", $row);
		$container = [];
		$i = 0;
		foreach($values as $value){
			$container[$fields[$i++]] = $value??"";
		}
		array_push($table, $container);
	}
		return $table;
	}

	public static function addRow($index, $value){
	echo "<td id=\"".$index."\" class=\"$value\">
				 <div class='col-xs-8 filelink-wrap'>
						<span>".$value."</span>
				 </div>
				</td>";
	}


	public static function getImages(){
	$dir =	'pod_manifests';
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


	public static function createPod($yaml_file, $ssh_key, $storage_path, $uid){
	$encoded_key = rawurlencode ($ssh_key);
	$complete_uri = OC_Kubernetes_Util::$KUBE_URL."run_pod.php?user_id=".$uid."&storage_path=".$storage_path."&public_key=".$encoded_key."&yaml_uri=/files/pod_manifests/".$yaml_file;
	$response = file_get_contents($complete_uri);
	// TODO Add exceptions and handling
	return $response;
	} 

	public static function deletePod($pod_name, $uid) {
		$complete_uri = OC_Kubernetes_Util::$KUBE_URL."delete_pod.php?user_id=".$uid."&pod=".$pod_name;
		$response = file_get_contents($complete_uri);
		return $response;
	}

	public static function getLogs($pod_name, $uid){
		$file_path = "/tmp/";
		$complete_uri = OC_Kubernetes_Util::$KUBE_URL."get_pod_logs.php?user_id=".$uid."&pod=".$pod_name;
		$response = file_get_contents($complete_uri);

		$file = $file_path.$pod_name."log";
		$logfile = fopen($file, "w") or die("Unable to open file!");
		fwrite($logfile, $response);
		fclose($logfile);

		$type = filetype($file);
		header("Content-type: $type");
			header("Content-Disposition: attachment;filename=$pod_name.log");
		 		readfile($file);
	}
}
