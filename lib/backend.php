<?php

class OC_Kubernetes {
    private static $URI = 'http://10.0.0.12/';
     /**
     * @brief Get all user's pods
     * @param  string $uid Name of the user
     * @return array  with pod names of a user
     */
    public static function getUserPods( $uid )
    {
       
        $table = array();	
        $full_URI = OC_Kubernetes::$URI."get_containers.php?user_id=".$uid;	
	$response = file_get_contents($full_URI);
	$rows = explode("\n", $response);
	array_pop($rows);
	foreach ($rows as $row) {
               $container = array("pod_name"=>"", "container_name"=>"", "status"=>"", "ssh_port"=>"", "https_port"=>"", "Uri_Jupy"=>"", "docker_image"=>"");
               $cells = explode("|", $row);

	       $container["pod_name"] = $cells[0] ?? "";
	       $container["container_name"] = $cells[1] ?? "";
	       $container["status"] = $cells[7] ?? "";
	       $container["https_port"] = $cells[9] ?? "";
	       $container["ssh_port"] = $cells[8] ?? "";
	       $container["Uri_Jupy"] = $cells[10] ?? "";
	       $container["docker_image"] = $cells[2] ?? "";
		array_push($table, $container);
	}
        return $table;
    }



    public static function deletePod( $pod, $uid ){
    
	    $full_URI = OC_Kubernetes::$URI."delete_pod.php?user_id=".$uid."&pod=".$pod;
	    $response = file_get_contents($full_URI);
	    return $response;
    }   
    
    public static function createPath($uid){
        $path = "/tank/storage/" .$uid;
	if (!is_dir($path)){
	   mkdir($path, 0750, true);
	}
    
    }

    
    public static function createUserFolder($uid){
	  
	    \OC_User::setUserId($uid);
            \OC_Util:: setupFS($uid);
	    $fs = \OCP\Files::getStorage('kubernetes');
	    if (!$fs){
	       \OCP\Util::writeLog('kubernetes', 'Could not create a folder' .$uid, \OCP\Util::ERROR);
	        return false;
                }
          $dir = \OC\Files\Filesystem::normalizePath('/'.$uid);
          return $fs->getLocalFile($dir);
    }


      public static function getLogs( $pod, $uid ){

          $log = "/tank/data/owncloud/kerverous/kubernetes/logs/";
	     if (!is_dir($log)){
	     
	        mkdir($log,0750, true);
	     }  
	    
	    $uri_log = OC_Kubernetes::$URI . "get_pod_logs.php?user_id=" . $uid . "&pod=" . $pod;
	    $response = file_get_contents($uri_log);
	    $log_file = $log . $pod . ".log"; 
            $final_file = file_put_contents($log_file, $response);
	    return $log_file;

	  /*  header('Content-Type: application/octet-stream');
	    header('Content-Disposition: attachment; filename="'.basename($log_file)'"');
	    header('Cache-Control: must-revalidate');
	    header('Pragma: pyblic');
	    header('Content-Length: ', filesize($log_file));
	    readfile($log_file); */
      }

/* public static function createPod($yaml_uri, $uid, $ssh_key, $storage_path){
       $full_uri = self::$URI . "run_pod.php?user_id=" . $uid . "&storage_path=" . $storage_path ."&public_key=" . $ssh_key
    
    
}*/

}


