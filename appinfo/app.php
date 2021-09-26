<?php

OC::$CLASSPATH['OC_Kubernetes_Util'] ='apps/user_pods/lib/util.php';

OCP\App::addNavigationEntry(
    array( 'id'    => 'user_pods',
           'order' => 6,
           'icon'  => OCP\Util::imagePath( 'user_pods', 'kubernetes.png' ),
           'href'  => OCP\Util::linkTo( 'index.php/apps/user_pods' , 'index.php' ),
           'name'  => 'Compute'
         )
    );
	
\OCP\App::registerAdmin('user_pods', 'settings');
