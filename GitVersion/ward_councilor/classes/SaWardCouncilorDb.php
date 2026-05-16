<?php defined('BX_DOL') or die('hack attempt');


class SaWardCouncilorDb extends BxDolModuleDb
{
    function __construct(&$oConfig) 
    {
        parent::__construct($oConfig);
    }

    function getSpaces($iCatId = 10)
    {
        return $this->getAll(
            "SELECT `id`, `space_name` AS `title` FROM `bx_spaces_data` WHERE `space_cat` = ? AND `status` = 'active' AND `status_admin` = 'active' ORDER BY `space_name` ASC",
            [$iCatId]
        );
    }

    // =====================================================
    // SERVICE REQUESTS
    // =====================================================
    
    function getServiceRequests($aParams = array())
    {
        $sWhere = " WHERE 1 ";
        $sOrder = " ORDER BY `created` DESC ";
        $sLimit = "";
        
        if(isset($aParams['status'])) {
            $sStatus = addslashes($aParams['status']);
            $sWhere .= " AND `status` = '$sStatus'";
        }
        
        if(isset($aParams['space_id'])) {
            if($aParams['space_id'])
                $sWhere .= " AND `space_id` = " . (int)$aParams['space_id'];
            // if space_id is null/0, show all (no filter)
        }

        if(isset($aParams['author_id'])) {
            $sWhere .= " AND `author_id` = " . (int)$aParams['author_id'];
        }
        
        if(isset($aParams['category'])) {
            $sCategory = addslashes($aParams['category']);
            $sWhere .= " AND `category` = '$sCategory'";
        }
        
        if(isset($aParams['priority'])) {
            $sPriority = addslashes($aParams['priority']);
            $sWhere .= " AND `priority` = '$sPriority'";
        }
        
        if(isset($aParams['search'])) {
            $sSearch = addslashes($aParams['search']);
            $sWhere .= " AND (`title` LIKE '%$sSearch%' OR `description` LIKE '%$sSearch%' OR `reference_number` LIKE '%$sSearch%')";
        }
        
        if(isset($aParams['limit'])) {
            $sLimit = " LIMIT " . (int)$aParams['limit'];
        }
        
        $sSql = "SELECT * FROM `sa_ward_councilor_requests` " . $sWhere . $sOrder . $sLimit;
        return $this->getAll($sSql);
    }

    function getServiceRequest($iId)
    {
        $sSql = "SELECT * FROM `sa_ward_councilor_requests` WHERE `id` = " . (int)$iId . " LIMIT 1";
        return $this->getRow($sSql);
    }

    function addServiceRequest($aData)
    {
        $sTitle = addslashes($aData['title']);
        $sDescription = addslashes($aData['description']);
        $sCategory = addslashes($aData['category']);
        $sLocation = isset($aData['location']) ? addslashes($aData['location']) : '';
        $sContactPhone = isset($aData['contact_phone']) ? addslashes($aData['contact_phone']) : '';
        $sContactEmail = isset($aData['contact_email']) ? addslashes($aData['contact_email']) : '';
        $sPriority = addslashes($aData['priority']);
        $sStatus = addslashes($aData['status']);
        $sCreated = addslashes($aData['created']);
        $iAuthor = (int)$aData['author_id'];
        $iSpaceId = isset($aData['space_id']) && $aData['space_id'] ? (int)$aData['space_id'] : 'NULL';
        $iAllowViewTo = isset($aData['allow_view_to']) ? (int)$aData['allow_view_to'] : 2;
        
        // Generate reference number
        $sRefPrefix = strtoupper(substr($sCategory, 0, 3));
        $sRefNumber = $sRefPrefix . '-' . date('Ymd') . '-' . str_pad(mt_rand(1, 9999), 4, '0', STR_PAD_LEFT);
        
        $sSql = "INSERT INTO `sa_ward_councilor_requests` 
            (`title`, `description`, `category`, `location`, `contact_phone`, `contact_email`,
             `priority`, `status`, `reference_number`, `author_id`, `space_id`, `allow_view_to`, `created`) 
            VALUES ('$sTitle', '$sDescription', '$sCategory', '$sLocation', '$sContactPhone', '$sContactEmail',
                    '$sPriority', '$sStatus', '$sRefNumber', $iAuthor, $iSpaceId, $iAllowViewTo, '$sCreated')";
        
        $this->query($sSql);
        return $this->lastId();
    }

    function updateServiceRequest($iId, $aData)
    {
        $aSets = array();
        
        if(isset($aData['status'])) {
            $aSets[] = "`status` = '" . addslashes($aData['status']) . "'";
        }
        if(isset($aData['priority'])) {
            $aSets[] = "`priority` = '" . addslashes($aData['priority']) . "'";
        }
        if(isset($aData['councilor_notes'])) {
            $aSets[] = "`councilor_notes` = '" . addslashes($aData['councilor_notes']) . "'";
        }
        
        $aSets[] = "`updated` = NOW()";
        
        if(empty($aSets)) return false;
        
        $sSql = "UPDATE `sa_ward_councilor_requests` SET " . implode(', ', $aSets) . " WHERE `id` = " . (int)$iId;
        return $this->query($sSql);
    }

    function updateServiceRequestViews($iId)
    {
        return $this->query("UPDATE `sa_ward_councilor_requests` SET `views` = `views` + 1 WHERE `id` = " . (int)$iId);
    }

    // =====================================================
    // MEETINGS
    // =====================================================
    
    function getMeetings($aParams = array())
    {
        $sWhere = " WHERE 1 ";
        $sOrder = " ORDER BY `meeting_date` ASC ";
        $sLimit = "";
        
        if(isset($aParams['upcoming'])) {
            $sWhere .= " AND `meeting_date` >= NOW()";
        }
        
        if(isset($aParams['space_id']) && $aParams['space_id']) {
            $sWhere .= " AND `space_id` = " . (int)$aParams['space_id'];
        }
        
        if(isset($aParams['status'])) {
            $sStatus = addslashes($aParams['status']);
            $sWhere .= " AND `status` = '$sStatus'";
        }
        
        if(isset($aParams['limit'])) {
            $sLimit = " LIMIT " . (int)$aParams['limit'];
        }
        
        $sSql = "SELECT * FROM `sa_ward_councilor_meetings` " . $sWhere . $sOrder . $sLimit;
        return $this->getAll($sSql);
    }

    function getMeeting($iId)
    {
        $sSql = "SELECT * FROM `sa_ward_councilor_meetings` WHERE `id` = " . (int)$iId . " LIMIT 1";
        return $this->getRow($sSql);
    }

    function addMeeting($aData)
    {
        $sTitle = addslashes($aData['title']);
        $sDescription = isset($aData['description']) ? addslashes($aData['description']) : '';
        $sMeetingDate = addslashes($aData['meeting_date']);
        $sLocation = isset($aData['location']) ? addslashes($aData['location']) : '';
        $sType = addslashes($aData['type']);
        $sStatus = addslashes($aData['status']);
        $iSpaceId = isset($aData['space_id']) && $aData['space_id'] ? (int)$aData['space_id'] : 'NULL';
        $iAllowViewTo = isset($aData['allow_view_to']) ? (int)$aData['allow_view_to'] : 1;
        
        $sSql = "INSERT INTO `sa_ward_councilor_meetings` 
            (`title`, `description`, `meeting_date`, `location`, `type`, `status`, `space_id`, `allow_view_to`) 
            VALUES ('$sTitle', '$sDescription', '$sMeetingDate', '$sLocation', '$sType', '$sStatus', $iSpaceId, $iAllowViewTo)";
        
        $this->query($sSql);
        return $this->lastId();
    }

    // =====================================================
    // ANNOUNCEMENTS
    // =====================================================
    
    function getAnnouncements($aParams = array())
    {
        $sWhere = " WHERE 1 ";
        $sOrder = " ORDER BY `created` DESC ";
        $sLimit = "";
        
        if(isset($aParams['status'])) {
            $sStatus = addslashes($aParams['status']);
            $sWhere .= " AND `status` = '$sStatus'";
        }
        
        if(isset($aParams['space_id']) && $aParams['space_id']) {
            $sWhere .= " AND `space_id` = " . (int)$aParams['space_id'];
        }
        
        if(isset($aParams['pinned'])) {
            $sWhere .= " AND `pinned` = 1";
        }
        
        if(isset($aParams['search'])) {
            $sSearch = addslashes($aParams['search']);
            $sWhere .= " AND (`title` LIKE '%$sSearch%' OR `content` LIKE '%$sSearch%')";
        }
        
        if(isset($aParams['limit'])) {
            $sLimit = " LIMIT " . (int)$aParams['limit'];
        }
        
        $sSql = "SELECT * FROM `sa_ward_councilor_announcements` " . $sWhere . $sOrder . $sLimit;
        return $this->getAll($sSql);
    }

    function getAnnouncement($iId)
    {
        $sSql = "SELECT * FROM `sa_ward_councilor_announcements` WHERE `id` = " . (int)$iId . " LIMIT 1";
        return $this->getRow($sSql);
    }

    function addAnnouncement($aData)
    {
        $sTitle = addslashes($aData['title']);
        $sContent = addslashes($aData['content']);
        $iPinned = isset($aData['pinned']) ? (int)$aData['pinned'] : 0;
        $sStatus = addslashes($aData['status']);
        $iAuthorId = (int)$aData['author_id'];
        $iSpaceId = isset($aData['space_id']) && $aData['space_id'] ? (int)$aData['space_id'] : 'NULL';
        $iAllowViewTo = isset($aData['allow_view_to']) ? (int)$aData['allow_view_to'] : 1;
        
        $sSql = "INSERT INTO `sa_ward_councilor_announcements` 
            (`title`, `content`, `pinned`, `status`, `author_id`, `space_id`, `allow_view_to`, `created`) 
            VALUES ('$sTitle', '$sContent', $iPinned, '$sStatus', $iAuthorId, $iSpaceId, $iAllowViewTo, NOW())";
        
        $this->query($sSql);
        return $this->lastId();
    }

    function updateAnnouncementViews($iId)
    {
        return $this->query("UPDATE `sa_ward_councilor_announcements` SET `views` = `views` + 1 WHERE `id` = " . (int)$iId);
    }

    // =====================================================
    // WARD INFO
    // =====================================================
    
    function getWardInfo($iSpaceId)
    {
        $sSql = "SELECT * FROM `sa_ward_councilor_info` WHERE `space_id` = " . (int)$iSpaceId . " LIMIT 1";
        return $this->getRow($sSql);
    }

    function saveWardInfo($aData)
    {
        $iSpaceId = (int)$aData['space_id'];
        $sWardNumber = addslashes($aData['ward_number']);
        $sMunicipality = addslashes($aData['municipality']);
        $sProvince = addslashes($aData['province']);
        $sPopulation = (int)$aData['population'];
        $sDescription = isset($aData['description']) ? addslashes($aData['description']) : '';
        $sOfficeAddress = isset($aData['office_address']) ? addslashes($aData['office_address']) : '';
        $sOfficeHours = isset($aData['office_hours']) ? addslashes($aData['office_hours']) : '';
        $sContactPhone = isset($aData['contact_phone']) ? addslashes($aData['contact_phone']) : '';
        $sContactEmail = isset($aData['contact_email']) ? addslashes($aData['contact_email']) : '';
        
        // Check if exists
        $aExisting = $this->getWardInfo($iSpaceId);
        
        if($aExisting) {
            $sSql = "UPDATE `sa_ward_councilor_info` SET 
                `ward_number` = '$sWardNumber',
                `municipality` = '$sMunicipality',
                `province` = '$sProvince',
                `population` = $sPopulation,
                `description` = '$sDescription',
                `office_address` = '$sOfficeAddress',
                `office_hours` = '$sOfficeHours',
                `contact_phone` = '$sContactPhone',
                `contact_email` = '$sContactEmail',
                `updated` = NOW()
                WHERE `space_id` = $iSpaceId";
            return $this->query($sSql);
        } else {
            $sSql = "INSERT INTO `sa_ward_councilor_info` 
                (`space_id`, `ward_number`, `municipality`, `province`, `population`, `description`,
                 `office_address`, `office_hours`, `contact_phone`, `contact_email`, `created`) 
                VALUES ($iSpaceId, '$sWardNumber', '$sMunicipality', '$sProvince', $sPopulation, '$sDescription',
                        '$sOfficeAddress', '$sOfficeHours', '$sContactPhone', '$sContactEmail', NOW())";
            return $this->query($sSql);
        }
    }

    // =====================================================
    // STATS
    // =====================================================
    
    function getStats($iSpaceId = null)
    {
        $aStats = array(
            'total_requests' => 0,
            'pending_requests' => 0,
            'in_progress_requests' => 0,
            'resolved_requests' => 0,
            'upcoming_meetings' => 0,
            'announcements' => 0
        );
        
        $sSpaceWhere = $iSpaceId ? " WHERE `space_id` = " . (int)$iSpaceId : "";
        
        $sSql = "SELECT COUNT(*) FROM `sa_ward_councilor_requests`" . $sSpaceWhere;
        $aStats['total_requests'] = (int)$this->getOne($sSql);
        
        $sSql = "SELECT COUNT(*) FROM `sa_ward_councilor_requests` WHERE `status` = 'pending'" . ($iSpaceId ? " AND `space_id` = " . (int)$iSpaceId : "");
        $aStats['pending_requests'] = (int)$this->getOne($sSql);
        
        $sSql = "SELECT COUNT(*) FROM `sa_ward_councilor_requests` WHERE `status` = 'in_progress'" . ($iSpaceId ? " AND `space_id` = " . (int)$iSpaceId : "");
        $aStats['in_progress_requests'] = (int)$this->getOne($sSql);
        
        $sSql = "SELECT COUNT(*) FROM `sa_ward_councilor_requests` WHERE `status` = 'resolved'" . ($iSpaceId ? " AND `space_id` = " . (int)$iSpaceId : "");
        $aStats['resolved_requests'] = (int)$this->getOne($sSql);
        
        $sSql = "SELECT COUNT(*) FROM `sa_ward_councilor_meetings` WHERE `meeting_date` >= NOW()" . ($iSpaceId ? " AND `space_id` = " . (int)$iSpaceId : "");
        $aStats['upcoming_meetings'] = (int)$this->getOne($sSql);
        
        $sSql = "SELECT COUNT(*) FROM `sa_ward_councilor_announcements` WHERE `status` = 'published'" . ($iSpaceId ? " AND `space_id` = " . (int)$iSpaceId : "");
        $aStats['announcements'] = (int)$this->getOne($sSql);
        
        return $aStats;
    }

    // =====================================================
    // ACTIVITY NOTES (trail)
    // =====================================================

    function addNote($iRequestId, $iAuthorId, $sNote, $sStatusChange = null)
    {
        // Resolve author name at write time
        $sAuthorName = '';
        $aProfile = $this->getRow("SELECT p.content_id, p.type FROM sys_profiles p WHERE p.id=" . (int)$iAuthorId . " AND p.type='bx_persons' LIMIT 1");
        if($aProfile)
            $sAuthorName = (string)$this->getOne("SELECT fullname FROM bx_persons_data WHERE id=" . (int)$aProfile['content_id'] . " LIMIT 1");

        // Resolve actor role from ACL level at write time
        $iLevel = (int)$this->getOne("SELECT IDLevel FROM sys_acl_levels_members WHERE IDMember=" . (int)$iAuthorId . " LIMIT 1");
        $aRoleMap = array(12 => 'councillor', 10 => 'leadership', 7 => 'moderator', 8 => 'admin', 3 => 'member');
        $sActorRole = isset($aRoleMap[$iLevel]) ? $aRoleMap[$iLevel] : 'member';

        $sNote       = addslashes($sNote);
        $sAuthorName = addslashes($sAuthorName);
        $sStatus     = $sStatusChange ? "'" . addslashes($sStatusChange) . "'" : 'NULL';

        return $this->query("INSERT INTO `sa_ward_councilor_notes`
            (`request_id`, `author_id`, `author_name`, `actor_role`, `note`, `status_change`, `created`)
            VALUES (" . (int)$iRequestId . ", " . (int)$iAuthorId . ", '$sAuthorName', '$sActorRole', '$sNote', $sStatus, NOW())");
    }

    function getNotes($iRequestId)
    {
        return $this->getAll(
            "SELECT n.*, p.`id` as profile_id FROM `sa_ward_councilor_notes` n
             LEFT JOIN `sys_profiles` p ON p.`id` = n.`author_id`
             WHERE n.`request_id` = " . (int)$iRequestId . "
             ORDER BY n.`created` ASC"
        );
    }

    function getRequestCategories()
    {
        $sSql = "SELECT `category`, COUNT(*) as count 
                 FROM `sa_ward_councilor_requests` 
                 WHERE `category` IS NOT NULL 
                 GROUP BY `category` 
                 ORDER BY count DESC";
        return $this->getAll($sSql);
    }
}
