<?php

class OC_Kubernetes_Util {

	private $publicIP;
	private $privateIP;
	private $storageDir;
	private $manifestsURL;
	public $rawManifestsURL;
	
	function __construct(){
		$this->publicIP  = OC_Appconfig::getValue('user_pods', 'publicIP');
		$this->privateIP = OC_Appconfig::getValue('user_pods', 'privateIP');
		$this->storageDir = OC_Appconfig::getValue('user_pods', 'storageDir');
		$this->manifestsURL = OC_Appconfig::getValue('user_pods', 'manifestsURL');
		$this->rawManifestsURL = OC_Appconfig::getValue('user_pods', 'rawManifestsURL');
	}
	
	/**
	 * @brief Get all user's pods
	 * @param  string $uid Name of the user
	 * @return array  with pod names of a user
	 */
	public function createStorageDir($uid) {
		$folder_path = $this->storageDir . $uid;
		if (!is_dir($folder_path)) {
			mkdir($folder_path, 0755, true);
		}
	}

	public function getContainers($uid, $podNames=null){
		$containers = array();
		// pod_name|container_name|image_name|pod_ip|node_ip|owner|age(s)|status|ssh_port|https_port|uri
		$url = 'http://'.$this->privateIP."/get_containers.php?fields=include&user_id=".$uid;
		$response = file_get_contents($url);
		$rows = explode("\n", trim($response));
		$fields = explode("|", array_shift($rows));
		foreach($rows as $row) {
			if(empty($row)){
				continue;
			}
			$values = explode("|", $row);
			$container = [];
			$i = 0;
			foreach($values as $value){
				$container[$fields[$i]] = empty($value)?"":$value;
				++$i;
			}
			if(!empty($container['uri'])||!empty($container['https_port'])){
				$container['url'] = 'https://'.$this->publicIP.(empty($container['https_port'])?'':':'.$container['https_port']).
					'/'.(empty($container['uri'])?'':$container['uri']);
			}
			else{
				$container['url'] = '';
			}
			unset($container['uri']);
			unset($container['https_port']);
			if(!empty($container['ssh_port'])){
				$container['ssh_url'] = 'ssh://'.
					(empty($container['ssh_username'])?'':$container['ssh_username'].'@').
					$this->publicIP.':'.$container['ssh_port'];
			}
			else{
				$container['ssh_url'] = '';
			}
			unset($container['ssh_port']);
			unset($container['ssh_username']);
			if(!empty($container['age'])){
				$container['age'] = floor($container['age'] / 3600) . gmdate(":i:s", $container['age'] % 3600);
			}
			if(empty($podNames) || in_array($container['pod_name'], $podNames)){
				array_push($containers, $container);
			}
		}
		\OC_Log::write('user_pods', "Got containers from " . $url.": ".serialize($containers), \OC_Log::WARN);
		return $containers;
	}

	private static function getContent($uri) {
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $uri);
		curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.13) Gecko/20080311 Firefox/2.0.0.13');
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		$data = curl_exec($ch);
		curl_close($ch);
		return $data;
	}

	public function getManifests() {
		try {
			$res = self::getContent($this->manifestsURL);
			$type = '.yaml';
			$filenames = array();
			$dom = new DomDocument();
			$dom->loadHTML($res, LIBXML_NOERROR);
			$finder = new DomXPath($dom);
			$classname = "js-navigation-open Link--primary";
			$nodes = $finder->query("//*[contains(@class, '$classname')]");
			foreach ($nodes as $elem) {
				$filename = $elem->textContent;
				$len = strlen($type);
				$is_yaml = (substr($filename, -$len) === $type);
				if ($is_yaml == true) {
					array_push($filenames, $filename);
				}
			}
			return $filenames;
		}
		catch(\Exception $e) {
			\OCP\Util::logException('Pods', $e);
			OCP\JSON::error(array(
				'data' => array(
					'exception' => get_class($e),
					'message' =>  $e->getMessage()
				)
			));
		}
	}

	/**
	 * Get available information on a manifest from our yaml repository.
	 * @param $yaml_file
	 * @return associative array with information
	 */
	public function checkManifest($yaml_file) {
		if(empty($yaml_file)){
			return [];
		}
		$github_url = $this->rawManifestsURL . $yaml_file;
		$yaml = self::getContent($github_url);
		$arr = yaml_parse($yaml);
		$md_file = preg_replace('/\.yaml$/', '.md', $yaml_file);
		$github_md_url = $this->rawManifestsURL . $md_file;
		$manifest_info = file_get_contents($github_md_url);
		$pod_accepts_public_key = false;
		$pod_username = "";
		$pod_mount_path = [];
		$pod_mount_src = "";
		$containerInfos = [];
		if(!empty($arr['spec']['containers'])){
			foreach($arr['spec']['containers'] as $container){
				$accepts_public_key = false;
				$mountPaths = [];
				$image_name = "";
				if(!empty($container['image'])){
					$image_name = $container['image'];
				}
				if(!empty($container['env'])){
					foreach($container['env'] as $env){
						if(!empty($env['name']) && $env['name']=="SSH_PUBLIC_KEY"){
							$accepts_public_key = true;
							$pod_accepts_public_key = true;
						}
						if(!empty($env['name']) && $env['name']=="USERNAME" && !empty($env['value'])){
							$username = $env['value'];
							$pod_username = $env['value'];
						}
						if(!empty($env['name']) && $env['name']=="MOUNT_SRC" && !empty($env['value'])){
							$pod_mount_src = $env['value'];
						}
					}
				}
				if(!empty($container['volumeMounts'])){
					$pod_mount_path[$container['volumeMounts'][0]['name']]= $container['volumeMounts'][0]['mountPath'];
					foreach($container['volumeMounts'] as $volumeMount){
						$mountPaths[$volumeMount['name']] = $volumeMount['mountPath'];
					}
				}
				$containerInfos[] = [
					'image_name'=>$image_name,
					//'image_description'=>$image_description,
					'accepts_public_key'=>$accepts_public_key,
						'username'=>$username,
					'mount_paths'=>$mountPaths
				];
			}
		}
		return ['manifest_url'=>$github_url, 'manifest_info'=>$manifest_info,
				'pod_accepts_public_key'=>$pod_accepts_public_key, 'pod_username'=>$pod_username,
				'pod_mount_path'=>$pod_mount_path, 'pod_mount_src'=>$pod_mount_src,
				'container_infos'=>$containerInfos];
	}

	public function createPod($yaml_url, $public_key, $storage_path, $uid) {
		$url = 'http://'.$this->privateIP . "/run_pod.php?user_id=" . $uid .
			"&yaml_url=" . $yaml_url;
		if (!empty($public_key)) {
			$encoded_key = rawurlencode($public_key);
			$url = $url . "&public_key=" . $encoded_key;
		}
		if(!empty($storage_path)){
			$url = $url . "&storage_path=" . $storage_path;
		}
		return file_get_contents($url);
	}

	private static function getAppDir($user) {
		\OC_User::setUserId($user);
		\OC_Util::setupFS($user);
		$fs = \OCP\Files::getStorage('user_pods');
		if (!$fs) {
			\OC_Log::write('user_pods', "ERROR, could not access files of user " . $user, \OC_Log::ERROR);
			return null;
		}
		return $fs->getLocalFile('/');
	}

	public function deletePod($pod_name, $uid) {
		$complete_uri = 'http://'.$this->privateIP . "/delete_pod.php?user_id=" . $uid . "&pod=" . $pod_name;
		$response = file_get_contents($complete_uri);
		return $response;
	}

	public function getLogs($pod_name, $uid) {
		$file_path = self::getAppDir($uid) . "/pod_logs/";
		if (!is_dir($file_path)) {
			mkdir($file_path, 0750, true);
		}

		$complete_uri = 'http://'.$this->privateIP . "/get_pod_logs.php?user_id=" . $uid . "&pod=" . $pod_name;
		$response = file_get_contents($complete_uri);
		$file = $file_path . $pod_name . ".log";
		$logfile = fopen($file, "w") or die("Unable to open file!");
		fwrite($logfile, $response);
		fclose($logfile);
		$type = filetype($file);
		header("Content-type: $type");
		header("Content-Disposition: attachment;filename=$pod_name.log");
		readfile($file);
	}

}

