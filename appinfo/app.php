<?php

OC::$CLASSPATH['OC_Kubernetes'] = 'apps/kubernetes/lib/backend.php';

OCP\App::addNavigationEntry(
    array( 'id'    => 'kubernetes',
           'order' => 6,
           //    'icon'  => OCP\Util::imagePath( 'kubernetes', 'nav-icon.png' ),
           'href'  => OCP\Util::linkTo( 'kubernetes' , 'index.php' ),
           'name'  => 'Kubernetes Management'
         )
    );
	
