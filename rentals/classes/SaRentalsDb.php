<?php
defined('BX_DOL') or die('hack attempt');

class SaRentalsDb extends BxDolModuleDb
{
    function __construct(&$aModule)
    {
        parent::__construct($aModule);
    }

    function addListing($aData)
    {
        $aCols = array();
        $aVals = array();
        foreach ($aData as $sCol => $mVal) {
            $aCols[] = '`' . $sCol . '`';
            // NULL stays NULL; numerics cast directly; strings via escape()
            // NOTE: this UNA's escape() already wraps strings in single quotes — do NOT add extra quotes
            if (is_null($mVal))
                $aVals[] = 'NULL';
            elseif (is_int($mVal) || is_float($mVal))
                $aVals[] = $mVal;
            else
                $aVals[] = $this->escape((string)$mVal);
        }
        $sCols = implode(',', $aCols);
        $sVals = implode(',', $aVals);
        return $this->query("INSERT INTO `sa_rentals_listings` ($sCols) VALUES ($sVals)");
    }

    function getLastInsertId()
    {
        return $this->lastId();
    }

    function updateListing($iId, $aData)
    {
        $aParts = array();
        foreach ($aData as $sCol => $mVal) {
            if (is_null($mVal))
                $aParts[] = '`' . $sCol . '` = NULL';
            elseif (is_int($mVal) || is_float($mVal))
                $aParts[] = '`' . $sCol . '` = ' . $mVal;
            else
                $aParts[] = '`' . $sCol . '` = ' . $this->escape((string)$mVal);
        }
        $sSet = implode(', ', $aParts);
        return $this->query("UPDATE `sa_rentals_listings` SET $sSet WHERE `id` = " . (int)$iId);
    }

    function deleteListing($iId)
    {
        return $this->query("DELETE FROM `sa_rentals_listings` WHERE `id` = " . (int)$iId);
    }

    function getListing($iId)
    {
        $aRows = $this->getAll("SELECT * FROM `sa_rentals_listings` WHERE `id` = " . (int)$iId . " LIMIT 1");
        return !empty($aRows[0]) ? $aRows[0] : false;
    }

    function canViewListing($aListing, $iViewerId = 0)
    {
        $iViewerId = (int)$iViewerId;

        // Authors always see their own listings regardless of visibility
        if ($iViewerId && (int)$aListing['author_id'] === $iViewerId)
            return true;

        // Moderators and admins can always view any listing
        if ($iViewerId && BxDolAcl::getInstance()->isAllowed('sa_rentals', 'edit any entry', false))
            return true;

        // Pending listings are only visible to their author and admins (handled above)
        if (!empty($aListing['status']) && $aListing['status'] === 'pending')
            return false;

        $sVisibility = (isset($aListing['visibility']) && $aListing['visibility'] !== '')
            ? $aListing['visibility']
            : 'public';

        if ($sVisibility === 'public')
            return true;

        // Non-public listings require a logged-in viewer
        if (!$iViewerId)
            return false;

        if ($sVisibility === 'space') {
            // Guard: a listing with space visibility but no space assigned is inaccessible
            $iSpaceId = (int)$aListing['space_id'];
            if ($iSpaceId <= 0) return false;
            return in_array($iSpaceId, array_map('intval', $this->_getUserSpaceIds($iViewerId)), true);
        }

        if ($sVisibility === 'group') {
            // Guard: a listing with group visibility but no group assigned is inaccessible
            $iGroupId = (int)$aListing['group_id'];
            if ($iGroupId <= 0) return false;
            return in_array($iGroupId, array_map('intval', $this->_getUserGroupIds($iViewerId)), true);
        }

        return false;
    }

    function getListings($aFilter = array())
    {
        $sWhere = "WHERE 1";
        // Never show pending listings in public browse
        $sWhere .= ' AND `status` != "pending"';
        if (!empty($aFilter['province']))
            $sWhere .= ' AND `province` = "' . $this->escape($aFilter['province']) . '"';
        if (!empty($aFilter['property_type']))
            $sWhere .= ' AND `property_type` = "' . $this->escape($aFilter['property_type']) . '"';
        if (!empty($aFilter['min_rent']))
            $sWhere .= ' AND `rent_zar` >= ' . (float)$aFilter['min_rent'];
        if (!empty($aFilter['max_rent']))
            $sWhere .= ' AND `rent_zar` <= ' . (float)$aFilter['max_rent'];
        if (!empty($aFilter['bedrooms']))
            $sWhere .= ' AND `bedrooms` >= ' . (int)$aFilter['bedrooms'];
        if (!empty($aFilter['status']))
            $sWhere .= ' AND `status` = "' . $this->escape($aFilter['status']) . '"';

        $iViewerId = !empty($aFilter['viewer_id']) ? (int)$aFilter['viewer_id'] : 0;
        if ($iViewerId) {
            $aUserSpaceIds = $this->_getUserSpaceIds($iViewerId);
            $aUserGroupIds = $this->_getUserGroupIds($iViewerId);
            $sSpaceIn = !empty($aUserSpaceIds) ? implode(',', $aUserSpaceIds) : '0';
            $sGroupIn = !empty($aUserGroupIds) ? implode(',', $aUserGroupIds) : '0';
            $sWhere .= ' AND (
                `visibility` = "public"
                OR (`visibility` = "space" AND `space_id` IN (' . $sSpaceIn . '))
                OR (`visibility` = "group" AND `group_id` IN (' . $sGroupIn . '))
                OR `author_id` = ' . $iViewerId . '
            )';
        } else {
            $sWhere .= ' AND `visibility` = "public"';
        }

        return $this->getAll("SELECT * FROM `sa_rentals_listings` $sWhere ORDER BY `featured` DESC, `created` DESC");
    }

    function getListingsByAuthor($iAuthorId)
    {
        return $this->getAll("SELECT * FROM `sa_rentals_listings` WHERE `author_id` = " . (int)$iAuthorId . " ORDER BY `created` DESC");
    }

    function getListingsAdmin($aFilter = array())
    {
        $sWhere = "WHERE 1";
        if (!empty($aFilter['status']))
            $sWhere .= ' AND `status` = "' . $this->escape($aFilter['status']) . '"';
        return $this->getAll("SELECT * FROM `sa_rentals_listings` $sWhere ORDER BY `featured` DESC, `created` DESC");
    }

    function approveListing($iId)
    {
        return $this->query("UPDATE `sa_rentals_listings` SET `status` = 'available' WHERE `id` = " . (int)$iId . " AND `status` = 'pending'");
    }

    function featureListing($iId, $bFeatured)
    {
        $iVal = $bFeatured ? 1 : 0;
        return $this->query("UPDATE `sa_rentals_listings` SET `featured` = $iVal WHERE `id` = " . (int)$iId);
    }

    function incrementViews($iId)
    {
        return $this->query("UPDATE `sa_rentals_listings` SET `views_count` = `views_count` + 1 WHERE `id` = " . (int)$iId);
    }

    function getUserSpaces($iUserId)
    {
        // This UNA install uses space_name (older schema — verified live 2026-05-05)
        return $this->getAll("SELECT s.`id`, s.`space_name` AS `title` FROM `bx_spaces_data` s
            INNER JOIN `bx_spaces_fans` f ON f.`content` = s.`id`
            WHERE f.`initiator` = " . (int)$iUserId . " AND f.`mutual` = 1
            ORDER BY s.`space_name`");
    }

    function getUserGroups($iUserId)
    {
        // This UNA install uses group_name (older schema — verified live 2026-05-05)
        return $this->getAll("SELECT g.`id`, g.`group_name` AS `title` FROM `bx_groups_data` g
            INNER JOIN `bx_groups_fans` f ON f.`content` = g.`id`
            WHERE f.`initiator` = " . (int)$iUserId . " AND f.`mutual` = 1
            ORDER BY g.`group_name`");
    }

    protected function _getUserSpaceIds($iUserId)
    {
        $aRows = $this->getAll("SELECT f.`content` FROM `bx_spaces_fans` f WHERE f.`initiator` = " . (int)$iUserId . " AND f.`mutual` = 1");
        return array_column($aRows, 'content');
    }

    protected function _getUserGroupIds($iUserId)
    {
        $aRows = $this->getAll("SELECT f.`content` FROM `bx_groups_fans` f WHERE f.`initiator` = " . (int)$iUserId . " AND f.`mutual` = 1");
        return array_column($aRows, 'content');
    }
}
