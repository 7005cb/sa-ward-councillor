<?php defined('BX_DOL') or die('hack attempt');

bx_import('BxDolModuleDb');

class SaRentalsDb extends BxDolModuleDb
{
    function __construct(&$oConfig)
    {
        parent::__construct($oConfig);
    }

    function getListings($aParams = array())
    {
        $sWhere = " WHERE `status` = 'active' ";
        if (!empty($aParams['province']))
            $sWhere .= " AND `province` = '" . addslashes($aParams['province']) . "'";
        if (!empty($aParams['type']))
            $sWhere .= " AND `property_type` = '" . addslashes($aParams['type']) . "'";
        if (!empty($aParams['all_statuses']) && !empty($aParams['author_id']))
            $sWhere = " WHERE `author_id` = " . (int)$aParams['author_id'];
        elseif (!empty($aParams['author_id']))
            $sWhere .= " AND `author_id` = " . (int)$aParams['author_id'];
        $sLimit = isset($aParams['limit']) ? " LIMIT " . (int)$aParams['limit'] : " LIMIT 20";
        return $this->getAll("SELECT * FROM `sa_rentals_listings`" . $sWhere . " ORDER BY `created` DESC" . $sLimit);
    }

    function getListing($iId)
    {
        return $this->getRow("SELECT * FROM `sa_rentals_listings` WHERE `id` = " . (int)$iId . " LIMIT 1");
    }

    function addListing($aData)
    {
        $sTitle   = addslashes($aData['title']);
        $sDesc    = addslashes($aData['description']);
        $sType    = addslashes($aData['property_type']);
        $sProv    = addslashes($aData['province']);
        $sCity    = addslashes($aData['city']);
        $sAddr    = addslashes($aData['address']);
        $fRent    = (float)$aData['rent_zar'];
        $iAuthor  = (int)$aData['author_id'];
        $sContact = addslashes($aData['contact']);
        return $this->query("INSERT INTO `sa_rentals_listings`
            (`title`,`description`,`property_type`,`province`,`city`,`address`,`rent_zar`,`author_id`,`contact`,`status`,`created`)
            VALUES ('$sTitle','$sDesc','$sType','$sProv','$sCity','$sAddr',$fRent,$iAuthor,'$sContact','active',NOW())");
    }

    function updateListing($iId, $aData)
    {
        $aParts = array();
        foreach (array('title','description','property_type','province','city','address','contact','status') as $sCol)
            if (isset($aData[$sCol]))
                $aParts[] = "`$sCol` = '" . addslashes($aData[$sCol]) . "'";
        if (isset($aData['rent_zar']))
            $aParts[] = "`rent_zar` = " . (float)$aData['rent_zar'];
        if (empty($aParts)) return false;
        return $this->query("UPDATE `sa_rentals_listings` SET " . implode(',', $aParts) . " WHERE `id` = " . (int)$iId);
    }

    function deleteListing($iId)
    {
        return $this->query("DELETE FROM `sa_rentals_listings` WHERE `id` = " . (int)$iId);
    }
}
