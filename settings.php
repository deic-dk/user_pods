<?php

OCP\Util::addScript('user_pods', 'settings');
OCP\Util::addStyle('user_pods', 'style');

$tmpl = new OCP\Template('user_pods', 'settings');
return $tmpl->fetchPage();
