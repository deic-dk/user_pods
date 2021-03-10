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
		$container = array("pod_name"=>"","container_name"=>"","status"=>"", "ssh_port"=>"", "https_port"=> "", "Uri_Jupy"=> "");
		$cells = explode("|", $row);

		$container["pod_name"] = $cells[0] ?? "";
		$container["container_name"] = $cells[1] ?? "";
		$container["status"] = $cells[7] ?? "";
		$container["ssh_port"] = $cells[8] ?? "";
		$container["https_port"] = $cells[9] ?? "";
		$container["Uri_Jupy"]= $cells[10] ?? "";
               #### $container["owner"]= $cells[6] ?? "";
		array_push($table, $container);
	}	
        return $table;
    }



    public static function deletePod( $pod_name, $uid ){
    
	    $full_URI = OC_Kubernetes::$URI."delete_pod.php?user_id=".$uid."&pod=".$pod_name;


	    $response = file_get_contents($full_URI);
	    return $response;
         }    
    

}
