<?php

OC::$CLASSPATH['OC_Kubernetes_Util'] ='apps/user_pods/lib/util.php';

OCP\App::addNavigationEntry(
    array( 'id'    => 'user_pods',
           'order' => 6,
           //    'icon'  => OCP\Util::imagePath( 'user_pods', 'nav-icon.png' ),
           'href'  => OCP\Util::linkTo( 'index.php/apps/user_pods' , 'index.php' ),
           'name'  => 'Kubernetes'
         )
    );
	
