<?php defined('BX_DOL') or die('hack attempt');

bx_import('BxDolStudioInstaller');

class SaRentalsInstaller extends BxDolStudioInstaller
{
    function __construct($aConfig)
    {
        parent::__construct($aConfig);
    }

    protected function _onEnableAfter()
    {
        $mixedResult = parent::_onEnableAfter();

        $this->_updateRelations('enable');

        return $mixedResult;
    }

    protected function _onDisableBefore()
    {
        $this->_updateRelations('disable');

        return parent::_onDisableBefore();
    }

    protected function _updateRelations($sOperation)
    {
        $aConfig = $this->_aConfig;
        if(empty($aConfig['relations']) || !is_array($aConfig['relations']))
            return;

        foreach($aConfig['relations'] as $sModule) {
            if(!$this->oDb->isModuleByName($sModule))
                continue;

            $aRelation = $this->oDb->getRelationsBy(array('type' => 'module', 'value' => $sModule));
            if(empty($aRelation) || empty($aRelation['on_' . $sOperation]))
                continue;

            if(!BxDolRequest::serviceExists($aRelation['module'], $aRelation['on_' . $sOperation]))
                continue;

            bx_srv_ii($aRelation['module'], $aRelation['on_' . $sOperation], array($aConfig['home_uri']));
        }
    }
}
