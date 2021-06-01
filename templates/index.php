<?php

OCP\User::checkLoggedIn();
OCP\App::checkAppEnabled('kubernetes_app');

OCP\App::setActiveNavigationEntry( 'kubernetes_app' );

OCP\Util::addStyle('kubernetes_app', 'kubernetes_app');
OCP\Util::addStyle('files', 'files');

OCP\Util::addScript('kubernetes_app','script');
OC_Util::addScript( 'core', 'multiselect' );
OC_Util::addScript( 'core', 'singleselect' );
OC_Util::addScript('core', 'jquery.inview');

$tmpl = new OCP\Template('kubernetes_app', 'main', 'user');
$tmpl->printPage();

OC_Kubernetes_Util::createStorageDir(OCP\User::getUser());
