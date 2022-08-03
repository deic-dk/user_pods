<?php

class OC_Kubernetes_Util
{

	private $publicIP;
	private $privateIP;
	private $storageDir;
	private $manifestsURL;
	public $rawManifestsURL;
	public static $testing = false;

	function __construct()
	{
		$this->publicIP  = OC_Appconfig::getValue('user_pods', 'publicIP');
		//$this->privateIP = OC_Appconfig::getValue('user_pods', 'privateIP');
		// Set manually for now, configure correctly after ingress is set up
		$this->privateIP = "10.0.0.12:22080";
		$this->storageDir = OC_Appconfig::getValue('user_pods', 'storageDir');
		$this->manifestsURL = OC_Appconfig::getValue('user_pods', 'manifestsURL');
		$this->rawManifestsURL = OC_Appconfig::getValue('user_pods', 'rawManifestsURL');
	}

	/**
	 * @brief Get all user's pods
	 * @param  string $uid Name of the user
	 * @return array  with pod names of a user
	 */
	public function createStorageDir($uid)
	{
		$folder_path = $this->storageDir . '/' . $uid;
		if (!is_dir($folder_path)) {
			\OC_Log::write('user_pods', 'Try to make dir: ' . $folder_path, \OC_Log::WARN);
			mkdir($folder_path, 0755, true);
		}
	}

	public function curlBackend($function, $post_arr)
	{
		$url = 'http://' . $this->privateIP . "/" . $function;
		$crl = curl_init($url);
		curl_setopt_array($crl, array(
			CURLOPT_POST => true,
			CURLOPT_POSTFIELDS => json_encode($post_arr),
			CURLOPT_HTTPHEADER => array('Content-Type: application/json'),
			CURLOPT_RETURNTRANSFER => true,
		));
		\OC_Log::write('user_pods', 'Calling backend: ' . $url . ", " . json_encode($post_arr), \OC_Log::DEBUG);
		$response_str = curl_exec($crl);
		$response = json_decode($response_str, true);
		$code = curl_getinfo($crl, CURLINFO_HTTP_CODE);
		\OC_Log::write('user_pods', "Backend $function: " . $code . ", response: " . json_encode($response), \OC_Log::DEBUG);
		if ($code == 200) {
			return $response;
		}
		return false;
	}

	public function getPods($uid)
	{
		$post_arr = ['user_id' => $uid];
		return $this->curlBackend("get_pods", $post_arr);
	}

	private static function getContent($uri)
	{
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $uri);
		curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.13) Gecko/20080311 Firefox/2.0.0.13');
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		$data = curl_exec($ch);
		curl_close($ch);
		return $data;
	}

	public function getManifests()
	{
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
		} catch (\Exception $e) {
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
	public function checkManifest($yaml_file)
	{
		if (empty($yaml_file)) {
			return [];
		}
		$github_url = $this->rawManifestsURL . $yaml_file;
		$yaml = self::getContent($github_url);
		$arr = yaml_parse($yaml);
		$md_file = preg_replace('/\.yaml$/', '.md', $yaml_file);
		$github_md_url = $this->rawManifestsURL . $md_file;
		$manifest_info = file_get_contents($github_md_url);
		$containerInfos = [];
		if (!empty($arr['spec']['containers'])) {
			foreach ($arr['spec']['containers'] as $container) {
				$container_name = "";
				if (!empty($container['name'])) {
					$container_name = $container['name'];
				}
				$ask_environment_vars = [];
				if (!empty($container['env'])) {
					foreach ($container['env'] as $env) {
						if (!empty($env['name'])) {
							if (preg_match('/.*\s- name: ' . $env['name'] . ' # ask/', $yaml)) {
								$required = preg_match('/.*\s- name: ' . $env['name'] . ' # ask require/', $yaml);
								$ask_environment_vars[$env['name']] = [$env['value'], $required];
							}
						}
					}
				}
				$containerInfos[] = [
					'name' => $container_name,
					'env' => $ask_environment_vars,
				];
			}
		}
		return [
			'manifest_url' => $github_url,
			'manifest_info' => $manifest_info,
			'container_infos' => $containerInfos
		];
	}

	public static function okayCreatePodInput($input)
	{
		$matchers = [
			'SSH_PUBLIC_KEY' => '/^ssh-(rsa|dsa|ecdsa|ed25519) [a-zA-Z0-9\/+=]+( [a-zA-Z0-9]+(@[a-zA-Z0-9.]+)?)?$/',
			'path' => '/^([a-zA-Z0-9-_.][a-zA-Z0-9-_.\/ ]+[a-zA-Z0-9-_. ]+)?$/'
		];
		foreach ($input as $container => $settings) {
			foreach ($settings as $env => $value) {
				if (array_key_exists($env, $matchers)) {
					if (!preg_match($matchers[$env], $value)) {
						return false;
					}
				} else {
					if (!preg_match($matchers['path'], $value)) {
						return false;
					}
				}
			}
		}
		return true;
	}

	public function createPod($uid, $yaml_url, $settings_input)
	{
		$post_arr = [
			"settings" => $settings_input,
			"yaml_url" => $yaml_url,
			"user_id" => $uid,
		];
		return $this->curlBackend("create_pod", $post_arr);
	}

	private static function getAppDir($user)
	{
		\OC_User::setUserId($user);
		\OC_Util::setupFS($user);
		$fs = \OCP\Files::getStorage('user_pods');
		if (!$fs) {
			\OC_Log::write('user_pods', "ERROR, could not access files of user " . $user, \OC_Log::ERROR);
			return null;
		}
		return $fs->getLocalFile('/');
	}

	public function deletePod($pod_name, $uid)
	{
		$post_arr = [
			'user_id' => $uid,
			'pod_name' => $pod_name,
		];
		return $this->curlBackend("delete_pod", $post_arr);
	}

	public function getLogs($pod_name, $uid)
	{
		$file_path = self::getAppDir($uid) . "/pod_logs/";
		if (!is_dir($file_path)) {
			mkdir($file_path, 0750, true);
		}

		$complete_uri = 'http://' . $this->privateIP . "/get_pod_logs.php?user_id=" . $uid . "&pod=" . $pod_name;
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
