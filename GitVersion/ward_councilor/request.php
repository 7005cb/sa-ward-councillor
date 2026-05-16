<?php
/**
 * Copyright (c) South Africa Community
 * Ward Councilor Portal Module Entry Point
 */

defined('BX_DOL') or die('hack attempt');

// Check if this is a module request
if (defined('BX_DOL') && !defined('BX_SYSTEM_MODULE')) {
    
    // Include module configuration
    require_once(BX_DIRECTORY_PATH_MODULES . 'sa/ward_councilor/install/config.php');
    
    // Create module instance
    $oModule = BxDolModule::getInstance('sa_ward_councilor');
    
    if ($oModule) {
        // Handle module actions
        $sAction = bx_get('action');
        
        switch ($sAction) {
            case 'get_dashboard':
                echo $oModule->serviceGetDashboardBlock();
                break;
            case 'get_requests':
                echo $oModule->serviceGetRequestsBlock();
                break;
            default:
                // Default to dashboard
                echo $oModule->serviceGetDashboardBlock();
        }
    }
}
