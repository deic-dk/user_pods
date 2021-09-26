<?php

OCP\User::checkLoggedIn();
OCP\App::checkAppEnabled('user_pods');
// We use marked.js from files_markdown for parsing pod info
OCP\App::checkAppEnabled('files_markdown');

OCP\App::setActiveNavigationEntry( 'user_pods' );

OCP\Util::addStyle('user_pods', 'style');
OCP\Util::addStyle('files', 'files');

OCP\Util::addScript('user_pods','script');
OC_Util::addScript( 'core', 'multiselect' );
OC_Util::addScript( 'core', 'singleselect' );
OC_Util::addScript('core', 'jquery.inview');
OC_Util::addScript('files_markdown','marked');

$tmpl = new OCP\Template('user_pods', 'main', 'user');
$util = new OC_Kubernetes_Util();
$tmpl->assign('manifests', $util->getManifests());
$tmpl->printPage();

$util->createStorageDir(OCP\User::getUser());
