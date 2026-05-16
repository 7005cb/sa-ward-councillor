<?php defined('BX_DOL') or die('hack attempt');

require_once('./inc/header.inc.php');
require_once(BX_DIRECTORY_PATH_INC . 'design.inc.php');

bx_import('BxDolModule');
$oModule = BxDolModule::getInstance('sa_rentals');
if ($oModule) {
    $oModule->processing();
}
