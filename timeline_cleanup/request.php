<?php
/**
 * Timeline Cleanup Tool — Web Entry Point
 * modules/sa/timeline_cleanup/request.php
 */

require_once(dirname(__FILE__) . '/../../../inc/header.inc.php');
require_once(BX_DIRECTORY_PATH_INC . 'design.inc.php');

$oModule = BxDolModule::getInstance('sa_timeline_cleanup');
if (!$oModule) {
    echo 'Module not loaded';
    exit;
}

$sAction = bx_get('action');

// AJAX run cleanup
if ($sAction === 'run' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    // Override settings from form POST
    $iDays = (int)bx_get('days');
    $iBatch = (int)bx_get('batch');
    $sTypes = bx_get('event_types');
    $bDryRun = (bool)bx_get('dry_run');

    if ($iDays > 0)    setParam('sa_timeline_cleanup_days', $iDays);
    if ($iBatch > 0)   setParam('sa_timeline_cleanup_batch', $iBatch);
    if (!empty($sTypes)) setParam('sa_timeline_cleanup_event_types', $sTypes);
    setParam('sa_timeline_cleanup_dry_run', $bDryRun ? '1' : '0');

    header('Content-Type: text/html; charset=utf-8');
    echo $oModule->serviceRunCleanup();
    exit;
}

// Default: show the cleanup page
$iPageId = (int)bx_get('page_id');
if ($iPageId <= 0) {
    // Try to find the page
    $oPage = BxDolPage::getObjectInstance('sa_timeline_cleanup');
    if ($oPage) {
        $oPage->displayPage();
    } else {
        // Fallback: just show the cleanup form directly
        echo $oModule->serviceGetCleanupPage();
    }
} else {
    echo $oModule->serviceGetCleanupPage();
}
exit;
