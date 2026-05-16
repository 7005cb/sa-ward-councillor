<?php defined('BX_DOL') or die('hack attempt');

bx_import('BxDolModuleTemplate');

class SaSupportSchemeTemplate extends BxDolModuleTemplate 
{    
    function __construct(&$oConfig, &$oDb) 
    {
        parent::__construct($oConfig, $oDb);
    }
}

