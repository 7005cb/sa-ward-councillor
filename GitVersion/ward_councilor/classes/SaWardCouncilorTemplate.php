<?php
defined('BX_DOL') or die('hack attempt');

class SaWardCouncilorTemplate extends BxDolModuleTemplate
{
    function __construct(&$oConfig, &$oDb)
    {
        parent::__construct($oConfig, $oDb);
    }

    public function addCss($mixedFiles = 'main.css', $bDynamic = false)
    {
        return parent::addCss($mixedFiles, $bDynamic);
    }
}
