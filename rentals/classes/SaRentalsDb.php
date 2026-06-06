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
        if ($iViewerId && BxDolAcl::getInstance()->isMemberLevelInSet(array(7, 8)))
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

    // ─── Phase 1 Enhancement helpers ──────────────────────────────────────────

    /**
     * Count active (non-expired, non-pending) listings by author.
     * Used for quota enforcement before allowing a new listing to be created.
     */
    function getActiveListingCountByAuthor($iAuthorId)
    {
        $aRow = $this->getRow("SELECT COUNT(*) AS `cnt` FROM `sa_rentals_listings`
            WHERE `author_id` = " . (int)$iAuthorId . "
            AND `status` NOT IN ('pending', 'taken')");
        return isset($aRow['cnt']) ? (int)$aRow['cnt'] : 0;
    }

    /**
     * Count uploaded photos for a listing (comma-separated ids in media_storage_ids).
     */
    function getPhotoCountForListing($iListingId)
    {
        $aRow = $this->getRow("SELECT `media_storage_ids` FROM `sa_rentals_listings` WHERE `id` = " . (int)$iListingId);
        if (empty($aRow['media_storage_ids'])) return 0;
        $aIds = array_filter(explode(',', $aRow['media_storage_ids']));
        return count($aIds);
    }

    /**
     * Mark a listing as expired (status = inactive equivalent — we use 'taken' is wrong,
     * so we add a soft 'hold' and rely on the expires_at column for display messaging).
     * Does NOT delete — owner can still renew.
     */
    function expireListing($iId)
    {
        return $this->query("UPDATE `sa_rentals_listings` SET `status` = 'hold'
            WHERE `id` = " . (int)$iId . " AND `status` = 'available'");
    }

    /**
     * Set or clear the verified badge on a listing.
     */
    function verifyListing($iId, $iAdminId)
    {
        return $this->query("UPDATE `sa_rentals_listings`
            SET `verified` = 1, `verified_by` = " . (int)$iAdminId . ", `verified_at` = NOW()
            WHERE `id` = " . (int)$iId);
    }

    function unverifyListing($iId)
    {
        return $this->query("UPDATE `sa_rentals_listings`
            SET `verified` = 0, `verified_by` = 0, `verified_at` = NULL
            WHERE `id` = " . (int)$iId);
    }

    /**
     * Count how many enquiries a tenant has sent today (throttle check).
     */
    function getEnquiryCountToday($iTenantId)
    {
        $aRow = $this->getRow("SELECT COUNT(*) AS `cnt` FROM `sa_rentals_enquiries`
            WHERE `tenant_id` = " . (int)$iTenantId . "
            AND DATE(`created`) = CURDATE()");
        return isset($aRow['cnt']) ? (int)$aRow['cnt'] : 0;
    }
}
