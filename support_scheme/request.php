<?php defined('BX_DOL') or die('hack attempt');
/**
 * Copyright (c) South Africa Community
 * 
 * @defgroup    SupportScheme Community Support Scheme module
 * @ingroup     SAModules
 *
 * @{
 */

require_once(BX_DIRECTORY_PATH_INC . "design.inc.php");

check_logged();

bx_import('BxDolRequest');
BxDolRequest::processAsAction($GLOBALS['aModule'], $GLOBALS['aRequest']);

/** @} */
