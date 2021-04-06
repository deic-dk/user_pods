<?php

OC::$CLASSPATH['OC_Kubernetes_Util'] = 'apps/kubernetes_app/lib/util.php';


OCP\App::addNavigationEntry(
  array(
    'id'    => 'kubernetes_app',
    'order' => 6,
    //    'icon'  => OCP\Util::imagePath( 'kubernetes_app', 'nav-icon.png' ),
    'href'  => OCP\Util::linkTo('kubernetes_app', 'index.php'),
    'name'  => 'Kubernetes'
  )
);
