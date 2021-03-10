<?php

OCP\User::checkLoggedIn();
OCP\App::checkAppEnabled('kubernetes');

$tmpl = new OCP\Template('kubernetes', 'settings.tpl');
$tmpl ->assign('bk_list', OC_k8s::getAllItemsByUser());
return $tmpl->fetchPage();

