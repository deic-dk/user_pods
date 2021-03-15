<?php

//echo rawurlencode('ssh-rsa AAAAB3NzaC1yc2EAAAADAQABAAABgQC+UTjmuw2Ds5/2oLCynEigyQZ3E5ExzO8f0Btg5NWJiiGPSWcR5sJrUOOpMevm+X7c1hY+CaAiH2Cx/Ept1n/e+YVQcR6aRcqwYGX1BtLUu/RwIGo0F8JOSjzMTYAeqJKimu0wI0NM8kXVQPZAcsNGvZTrep/iSlgZR3ivS6ySJ2Y3G2frtRmzXRjHrVghTlkSvY6Euqd3kclfXEuY//bW0P2XWTiAmcT0PjhGGYLbYwfFY7w/7TT7tVX7Q5WNyo7XRiH6nYSw2k9WM2WruI8bgeREJ9IFLVKCoj7p6w3oYNZ4v7gMPZCpCIT5cl4fLt9CvrTfKjVlzimKeqVgqkqEWS/jpF1oheAcTbPP5lLqPryq+4UHtPlOzyZY52YQtftCocl+mvAJ6k9nLW2S6pXYPDkGAiJsJki4QJ8O8PUzLNsc9gb+3tN58wXzAbQbcXOuDMbwEiKqRwEj1BEyTZ09x2Df0Knp2LHlB89mmBwoXfTlVIgTo46+gp2RcRkGe9M= ioannapsylla@dhcp-10-201-255-86.clients.net.dtu.dk')

$test = '/tank/data/owncloud/kerverous/files/pod_manifests/jupyter_sciencedata.yaml';

	$has_ssh = false;
	$has_mount = false;

	if( strpos(file_get_contents($test),"SSH_PUBLIC_KEY") !== false) {
		$has_ssh = true;	
	}
	
	if( strpos(file_get_contents($test), "mountPath") != false) {
		$has_mount = true;
	}
	print_r($has_mount);
?>

