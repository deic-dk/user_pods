<?php

class OC_Kubernetes_Util
{
	private static $CADDY_URI = 'http://10.0.0.12/';
	private static $GITHUB_CONTENT_URI = 'https://raw.githubusercontent.com';

	public static function createStorageDir($uid)
	/**
	 * @brief Create a dedicated folder for the user to be used for mounting in a pod
	 * @param  string $uid Name of the user
	 */
	{
		$folder_path = "/tank/storage/" . $uid;

		if (!is_dir($folder_path)) {
			mkdir($folder_path, 0755, true);
		}
	}

	public static function getUserPods($uid)
	/**
	 * @brief Get all user's pods
	 * @param  string $uid Name of the user
	 * @return array  with data for each pod
	 */
	{
		$table = array();
		$complete_uri = self::$CADDY_URI . "get_containers.php?fields=include&user_id=" . $uid;
		$response = file_get_contents($complete_uri);
		$rows = explode("\n", $response);
		$fields = explode("|", $rows[0]);
		array_shift($rows);
		foreach ($rows as $row) {
			if (empty($row)) {
				continue;
			}
			$values = explode("|", $row);
			$container = [];
			$i = 0;
			foreach ($values as $value) {
				$container[$fields[$i++]] = $value ?? "";
			}
			array_push($table, $container);
		}
		return $table;
	}

	public static function addCell($index, $value)
	/**
	 * @brief Populate a cell of the pods table with corresponding information
	 * @param  string $index the name of a field (e.g. https_port)
	 * @param string $value the value of a field (e.g. 200 for https_port)
	 */
	{
		echo "<td class=\"$value\">
                 <div>
                      <span id=\"$index\">$value</span>
                 </div>
              </td>";
	}

	private static function getContent($uri)
	/**
	 * @brief Get HTML content of a link
	 * @param  string $uri a web link
	 * @return string HTML content
	 */
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

	public static function getImages($default_pods_uri)
	/**
	 * @brief Get the filenames of the image YAML files from GitHub
	 * @return array with the YAML filenames
	 */
	{
		try {
			$res = self::getContent($default_pods_uri);
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
					'exception' => '\Exception',
					'message' => $l->t('Unknown error')
				)
			));
		}
	}

	public static function getDockerhubDescription($image_name, $dockerhub)
	/**
	 * @brief Get a description of an image from DockerHub
	 * @param string $image_name Name of the docker image
	 * @return string  a description of the image
	 */
	{
		$dockerhub_uri = $dockerhub . 'v2/repositories/' . $image_name . '/';
		$dict = json_decode(self::getContent($dockerhub_uri));

		$description = $dict->{'full_description'};
		return $description;
	}

	public static function checkImage($yaml_file, $dockerhub, $github_repo)
	/**
	 * @brief Parse a YAML file of an image and extract information such as ssh key, mount path and image name/description
	 * @param  string $yaml_file Name of the the YAML file
	 * @return array  with data of the YAML file and the docker image
	 */
	{
		$filename = explode('.', $yaml_file)[0];

		$yaml_github_uri = self::$GITHUB_CONTENT_URI . $github_repo . 'main/' . $yaml_file;
		$markdown_github_uri = self::$GITHUB_CONTENT_URI . $github_repo . 'main/' . $filename . '.md';

		$yaml_content = self::getContent($yaml_github_uri);
		$markdown_content = self::getContent($markdown_github_uri);

		$has_ssh = false;
		$has_mount = false;
		$mountPath = "";

		$temp = explode("image:", $yaml_content);
		$image_name = trim(explode(PHP_EOL, $temp[1])[0]);

		$image_description = self::getDockerhubDescription($image_name, $dockerhub);

		$parsed_yaml = yaml_parse($yaml_content);

		$containers = $parsed_yaml['spec']['containers'];

		foreach ($containers as $container) {
			if (isset($container['env']) == true) {
				$env = $container['env'];
				foreach ($env as $var) {
					if ($var['name'] == 'SSH_PUBLIC_KEY') {
						$has_ssh = true;
					}
				}
			}
			if (isset($container['volumeMounts']) == true) {
				$volumes = $container['volumeMounts'];
				foreach ($volumes as $volume) {
					if (isset($volume['mountPath'])) {
						$has_mount = true;
						$mountPath = $volume['mountPath'];
					}
				}
			}
		}

		return array($has_ssh, $has_mount, $image_name, $image_description, $mountPath, $markdown_content);
	}

	public static function createPod($yaml_file, $ssh_key, $storage_path, $github_repo, $uid)
	/**
	 * @brief Send a GET request to the kube server for creating a new pod
	 * @param  string $yaml_file Name of the YAML file
	 * @param string $ssh_key Public SSH key
	 * @param string $storage_path Path name to mount in the container
	 * @param string $uid Name of the user
	 * @return string  Response from the kube server 
	 */
	{
		try {
			$yaml_github_uri = self::$GITHUB_CONTENT_URI . $github_repo . 'main/' . $yaml_file;
			$yaml_content = self::getContent($yaml_github_uri);

			$file_path =  self::getAppDir($uid, 'files');

			$file = $file_path . '/' . $yaml_file;
			$temp_file = fopen($file, "w") or die("Unable to open file!");
			fwrite($temp_file, $yaml_content);
			fclose($temp_file);

			$complete_uri = self::$CADDY_URI . "run_pod.php?user_id=" . $uid . "&yaml_url=/files/" . $yaml_file;
			if (is_null($ssh_key) == false) {
				$encoded_key = rawurlencode($ssh_key);
				if (is_null($storage_path) == false) {
					$complete_uri = $complete_uri . "&storage_path=" . $storage_path . "&public_key=" . $encoded_key;
				} else {
					$complete_uri = $complete_uri . "&public_key=" . $encoded_key;
				}
			} else {
				if (is_null($storage_path) == false) {
					$complete_uri = $complete_uri . "&storage_path=" . $storage_path;
				}
			}

			$response = file_get_contents($complete_uri);

			unlink($file);

			return $response;
		} catch (\Exception $e) {
			\OCP\Util::logException('Pod creation', $e);
			OCP\JSON::error(array(
				'data' => array(
					'exception' => '\Exception',
					'message' => $l->t('Unknown error')
				)
			));
		}
	}

	private static function getAppDir($uid, $app)
	/**
	 * @brief Create a folder for the user in the application's directory on the server
	 * @param  string $uid Name of the user
	 * @return string Name of the user's directory on the server
	 */
	{
		\OC_User::setUserId($uid);
		\OC_Util::setupFS($uid);
		$fs = \OCP\Files::getStorage($app);
		if (!$fs) {
			\OC_Log::write('kubernetes_app', "ERROR, could not access files of user " . $uid, \OC_Log::ERROR);
			return null;
		}
		return $fs->getLocalFile('/');
	}

	public static function deletePod($pod_name, $uid)
	/**
	 * @brief Sends a GET request to the kube server for deleting a user's pod
	 * @param string $pod_name Name of the pod
	 * @param  string $uid Name of the user
	 * @return string  Response from the kube server
	 */
	{
		$complete_uri = OC_Kubernetes_Util::$CADDY_URI . "delete_pod.php?user_id=" . $uid . "&pod=" . $pod_name;
		$response = file_get_contents($complete_uri);
		return $response;
	}

	public static function getLogs($pod_name, $uid)
	/**
	 * @brief Sends a GET request to the kube server for retrieving the logs of a pod
	 * @param string $pod_name Name of the pod
	 * @param  string $uid Name of the user
	 */
	{
		$file_path = self::getAppDir($uid, 'kubernetes_app') . "/pod_logs/";

		if (!is_dir($file_path)) {
			mkdir($file_path, 0750, true);
		}

		$complete_uri = OC_Kubernetes_Util::$CADDY_URI . "get_pod_logs.php?user_id=" . $uid . "&pod=" . $pod_name;
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
