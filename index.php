<?php

OCP\User::checkLoggedIn();
OCP\App::checkAppEnabled('kubernetes');

OCP\App::setActiveNavigationEntry( 'kubernetes' );

OCP\Util::addStyle('kubernetes', 'kubernetes');
OCP\Util::addStyle('files', 'files');

OCP\Util::addScript('kubernetes','script');
OC_Util::addScript( 'core', 'multiselect' );
OC_Util::addScript( 'core', 'singleselect' );
OC_Util::addScript('core', 'jquery.inview');

$tmpl = new OCP\Template('kubernetes', 'main', 'user');
//$tmpl = assign( 'containers' , OC_Backend::getUserPods(OC_User::getUser ()), true );
$tmpl->printPage();
