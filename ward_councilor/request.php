<?php
/**
 * Ward Councilor Portal — AJAX request handler
 * Called by the nav strip JS: modules/sa/ward_councilor/request.php?action=X&space_id=N
 */

// Capture space_id before UNA bootstrap potentially clears $_GET
$_wcSpaceId = isset($_GET['space_id']) ? (int)$_GET['space_id'] : 0;

require_once(dirname(__FILE__) . '/../../../inc/header.inc.php');
require_once(BX_DIRECTORY_PATH_INC . 'design.inc.php');

// Restore so _getCurrentSpaceId() can find it via bx_get() or $_GET
if($_wcSpaceId > 0) $_GET['space_id'] = $_wcSpaceId;

$oModule = BxDolModule::getInstance('sa_ward_councilor');
if(!$oModule) { echo ''; exit; }

$sAction  = bx_get('action');

// Moderation AJAX handler (JSON response)
if($sAction === 'moderate_request') {
    header('Content-Type: application/json; charset=utf-8');
    $iRequestId = (int)bx_get('id');
    $sModAction = bx_get('mod_action');
    if(!$iRequestId || !$sModAction) {
        echo json_encode(array('success' => false, 'error' => 'Missing params'));
        exit;
    }
    if(!$oModule->_isCouncilor()) {
        echo json_encode(array('success' => false, 'error' => 'Access denied'));
        exit;
    }
    $aTransitions = array(
        'approve'    => 'active',
        'reject'     => 'rejected',
        'in_progress'=> 'in_progress',
        'resolved'   => 'resolved',
    );
    if(!isset($aTransitions[$sModAction])) {
        echo json_encode(array('success' => false, 'error' => 'Invalid action'));
        exit;
    }
    $sNewStatus = $aTransitions[$sModAction];
    $oDb = $oModule->_oDb;
    $aRequest = $oDb->getServiceRequest($iRequestId);
    if(!$aRequest) {
        echo json_encode(array('success' => false, 'error' => 'Request not found'));
        exit;
    }
    // Validate transition
    $aValidFrom = array(
        'approve'     => array('pending'),
        'reject'      => array('pending'),
        'in_progress' => array('active'),
        'resolved'    => array('active', 'in_progress'),
    );
    if(!in_array($aRequest['status'], $aValidFrom[$sModAction])) {
        echo json_encode(array('success' => false, 'error' => 'Invalid status transition'));
        exit;
    }
    $oDb->updateServiceRequest($iRequestId, array('status' => $sNewStatus));
    echo json_encode(array('success' => true, 'new_status' => $oModule->_getStatusLabel($sNewStatus)));
    exit;
}

// Cascading community selector — returns JSON array of child spaces
if($sAction === 'get_child_spaces') {
    header('Content-Type: application/json; charset=utf-8');
    $iParentId = (int)bx_get('parent_id');
    if(!$iParentId) {
        echo json_encode(array());
        exit;
    }
    $oDb = BxDolDb::getInstance();
    $aSpaces = $oDb->getAll(
        $oDb->prepare(
            "SELECT p.`id`, d.`space_name` AS `name` FROM `bx_spaces_data` d
             JOIN `sys_profiles` p ON p.`content_id` = d.`id` AND p.`type` = 'bx_spaces' AND p.`status` = 'active'
             WHERE d.`parent_space` = ? AND d.`status` = 'active'
             ORDER BY d.`space_name` ASC",
            $iParentId
        )
    );
    echo json_encode(is_array($aSpaces) ? $aSpaces : array());
    exit;
}

$sOut = '';
switch($sAction) {
    case 'get_space_summary_block':  $sOut = $oModule->serviceGetSpaceSummaryBlock();  break;
    case 'get_requests_block':       $sOut = $oModule->serviceGetRequestsBlock();       break;
    case 'get_meetings_block':       $sOut = $oModule->serviceGetMeetingsBlock();       break;
    case 'get_announcements_block':  $sOut = $oModule->serviceGetAnnouncementsBlock(); break;
    case 'get_my_requests_block':    $sOut = $oModule->serviceGetMyRequestsBlock();    break;
    case 'get_manage_block':         $sOut = $oModule->serviceGetManageBlock();         break;
    default:                         $sOut = $oModule->serviceGetSpaceSummaryBlock();  break;
}

header('Content-Type: text/html; charset=utf-8');
echo $sOut;
exit;
