<?php

class OC_Kubernetes_Util {
    private static $URI = 'http://10.0.0.12/get_containers.php';
     /**
     * @brief Get all user's pods
     * @param  string $uid Name of the user
     * @return array  with pod names of a user
     */
    public static function getUserPods( $uid )
    {
  	$table = array();		
	$complete_uri = OC_Kubernetes_Util::$URI."?user_id=".$uid;
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


}
