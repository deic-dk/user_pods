<?php

OCP\JSON::checkAppEnabled('user_pods');
OCP\JSON::checkAppEnabled('files_sharding');

require_once('user_pods/lib/notebooks.php');

$user = \OCP\USER::getUser();
$notebookGroup = "sciencenotebooks";
$notebookGroupOwner = OC_User_Group_Admin_Util::getGroupOwner($notebookGroup);

$notebooks = new OC_Notebooks($user, $notebookGroup, $notebookGroupOwner);

$notebookFolderInfos = $notebooks->dbGetDirsSharedWithGroup($notebookGroup);
$publicNotebookFolderInfos = $notebooks::dbGetNotebookDirsSharedPublic($notebookFolderInfos);

OCP\JSON::encodedPrint($publicNotebookFolderInfos);

