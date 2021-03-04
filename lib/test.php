<?php

$response = file_get_contents("https://10.0.0.12/get_containers.php");
echo gettype($response);
$rows = explode("\n", $response);
print_r(sizeof($rows));
$table = array();
foreach ($rows as $row) {
	$container = array("pod_name"=>"","container_name"=>"","status"=>"", "ssh_port"=>"", "https_port"=> "");
	$cells = explode("|", $row);
	$container["pod_name"] = $cells[0] ?? "";
	$container["container_name"] = $cells[1] ?? "";
	$container["status"] = $cells[7] ?? "";
	$container["ssh_port"] = $cells[8] ?? "";
	$container["https_port"] = $cells[9] ?? "";
	
	array_push($table, $container);

}
?>

