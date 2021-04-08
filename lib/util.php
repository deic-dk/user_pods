<?php

class OC_Kubernetes_Util
{
	private static $CADDY_URI = 'http://10.0.0.12/';
	private static $GITHUB_URI = 'https://raw.githubusercontent.com/deic-dk/pod_manifests/main/';
	private static $DOCKERHUB_URI = 'https://hub.docker.com/v2/repositories/';
	/**
	 * @brief Get all user's pods
	 * @param  string $uid Name of the user
	 * @return array  with pod names of a user
	 */

	public static function createStorageDir($uid)
	{
		$folder_path = "/tank/storage/" . $uid;

		if (!is_dir($folder_path)) {
			mkdir($folder_path, 0755, true);
		}
	}

	public static function getUserPods($uid)
	{
		$table = array();
		$complete_uri = self::$CADDY_URI . "get_containers.php?fields=true&user_id=" . $uid;
		$response = file_get_contents($complete_uri);
		$fields = explode("|", $response);
		$url = self::$CADDY_URI."get_containers.php?user_id=".$uid;
		$response = file_get_contents($url);
		$rows = explode("\n", $response);
		//array_pop($rows);
		foreach ($rows as $row) {
			if (empty($row)) { continue; }
			$values = explode("|", $row);
			$container = [];
			$i = 0;
			foreach($values as $value) {
				$container[$fields[$i++]] = $value??"";
			}
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

	public static function getImages()
	{
		$default_pods_uri =  'https://github.com/deic-dk/pod_manifests';
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

	public static function getDockerhubDescription($image_name)
	{
		$dockerhub_uri = self::$DOCKERHUB_URI . $image_name . '/';
		$dict = json_decode(self::getContent($dockerhub_uri));

		$description = $dict->{'full_description'};
		return $description;
	}

	public static function checkImage($yaml_file)
	{
		$github_uri = self::$GITHUB_URI . $yaml_file;

		$yaml_content = self::getContent($github_uri);

		$has_ssh = false;
		$has_mount = false;
		$mountPath = "";

		$temp = explode("image:", $yaml_content);
		$image_name = trim(explode(PHP_EOL, $temp[1])[0]);

		$image_description = self::getDockerhubDescription($image_name);

		if (strpos($yaml_content, "SSH_PUBLIC_KEY") !== false) {
			$has_ssh = true;
		}

		if (strpos($yaml_content, "mountPath") != false) {
			$has_mount = true;
			$mountPath = explode(PHP_EOL, explode("mountPath",$yaml_content)[1])[0];
		}
		return array($has_ssh, $has_mount, $image_name, $image_description, $mountPath);
	}

	public static function createPod($yaml_file, $ssh_key, $storage_path, $uid)
	{
		$complete_uri = self::$CADDY_URI . "run_pod.php?user_id=" . $uid . "&yaml_uri=/files/pod_manifests/" . $yaml_file;
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
		// TODO Add exceptions and handling
		return $response;
	}

	private static function getAppDir($user)
	{
		\OC_User::setUserId($user);
		\OC_Util::setupFS($user);
		$fs = \OCP\Files::getStorage('kubernetes_app');
		if (!$fs) {
			\OC_Log::write('kubernetes_app', "ERROR, could not access files of user " . $user, \OC_Log::ERROR);
			return null;
		}
		return $fs->getLocalFile('/');
	}

	public static function deletePod($pod_name, $uid)
	{
		$complete_uri = OC_Kubernetes_Util::$CADDY_URI . "delete_pod.php?user_id=" . $uid . "&pod=" . $pod_name;
		$response = file_get_contents($complete_uri);
		return $response;
	}

	public static function getLogs($pod_name, $uid)
	{
		$file_path = self::getAppDir($uid) . "/pod_logs/";

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
