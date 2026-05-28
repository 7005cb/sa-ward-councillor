<?php defined('BX_DOL') or die('hack attempt');


class SaWardCouncilorModule extends BxDolModule 
{
    protected $_aRequestCategories = array(
        'water' => 'Water & Sanitation',
        'electricity' => 'Electricity',
        'roads' => 'Roads & Transport',
        'refuse' => 'Refuse & Waste',
        'housing' => 'Housing',
        'health' => 'Health Services',
        'safety' => 'Safety & Security',
        'parks' => 'Parks & Recreation',
        'other' => 'Other'
    );
    
    protected $_aPriorityLevels = array(
        'low' => 'Low',
        'medium' => 'Medium',
        'high' => 'High',
        'urgent' => 'Urgent'
    );
    
    protected $_aRequestStatuses = array(
        'pending' => 'Pending',
        'active' => 'Active',
        'rejected' => 'Rejected',
        'in_progress' => 'In Progress',
        'resolved' => 'Resolved',
        'closed' => 'Closed'
    );

    function __construct(&$aModule) 
    {
        parent::__construct($aModule);
    }

    protected function _getCategoryLabel($sCategory) {
        return isset($this->_aRequestCategories[$sCategory]) ? $this->_aRequestCategories[$sCategory] : $sCategory;
    }

    protected function _getPriorityLabel($sPriority) {
        return isset($this->_aPriorityLevels[$sPriority]) ? $this->_aPriorityLevels[$sPriority] : $sPriority;
    }

    protected function _getStatusLabel($sStatus) {
        return isset($this->_aRequestStatuses[$sStatus]) ? $this->_aRequestStatuses[$sStatus] : $sStatus;
    }

    protected function _timeAgo($sDate) {
        $iTime = strtotime($sDate);
        $iDiff = time() - $iTime;
        
        if($iDiff < 60) return 'Just now';
        if($iDiff < 3600) return floor($iDiff / 60) . ' min ago';
        if($iDiff < 86400) return floor($iDiff / 3600) . ' hours ago';
        if($iDiff < 604800) return floor($iDiff / 86400) . ' days ago';
        return date('d M Y', $iTime);
    }

    /**
     * Group an array of items by calendar month of a given date field.
     * Returns an ordered array: [ 'F Y' => [ ...items ] ]
     * Most recent month first.
     */
    protected function _groupByMonth($aItems, $sDateField = 'created')
    {
        $aGroups = array();
        foreach($aItems as $aItem) {
            $sKey = date('F Y', strtotime($aItem[$sDateField]));
            $aGroups[$sKey][] = $aItem;
        }
        return $aGroups;
    }

    /**
     * Render a month-grouped list.
     * $fnRender is a callable that receives one item and returns HTML.
     * $sGridClass is the CSS class wrapping each month's items.
     */
    protected function _renderGrouped($aGroups, $fnRender, $sGridClass = 'wc-requests-grid')
    {
        $sOut = '';
        foreach($aGroups as $sMonth => $aItems) {
            $sCards = '';
            foreach($aItems as $aItem) {
                $sCards .= $fnRender($aItem);
            }
            $sOut .= '<div class="wc-month-group">
                <div class="wc-month-label">' . htmlspecialchars($sMonth) . ' <span class="wc-month-count">(' . count($aItems) . ')</span></div>
                <div class="' . $sGridClass . '">' . $sCards . '</div>
            </div>';
        }
        return $sOut;
    }

    // =====================================================
    // DASHBOARD / MAIN PORTAL
    // =====================================================
    
    function serviceGetDashboardBlock()
    {
        $this->_oTemplate->addCss(array('main.css'));
        
        
        $iSpaceId = $this->_getCurrentSpaceId();
        $aStats = $this->_oDb->getStats($iSpaceId);
        
        // Get recent requests
        $aRequests = $this->_oDb->getServiceRequests(array(
            'limit' => 5,
            'space_id' => $iSpaceId
        ));
        
        $sRequests = '';
        foreach($aRequests as $aRequest) {
            $sRequests .= '<div class="wc-recent-item">
                <span class="wc-status wc-status-' . $aRequest['status'] . '">' . $this->_getStatusLabel($aRequest['status']) . '</span>
                <div class="wc-recent-content">
                    <a href="' . BX_DOL_URL_ROOT . 'page.php?i=view-ward-request&id=' . $aRequest['id'] . '">' . htmlspecialchars($aRequest['title']) . '</a>
                    <span class="wc-recent-meta">' . $this->_getCategoryLabel($aRequest['category']) . ' • ' . $this->_timeAgo($aRequest['created']) . '</span>
                </div>
            </div>';
        }
        
        // Get upcoming meetings
        $aMeetings = $this->_oDb->getMeetings(array(
            'upcoming' => 1,
            'limit' => 3,
            'space_id' => $iSpaceId
        ));
        
        $sMeetings = '';
        foreach($aMeetings as $aMeeting) {
            $sMeetings .= '<div class="wc-meeting-item">
                <div class="wc-meeting-date">
                    <span class="wc-meeting-day">' . date('d', strtotime($aMeeting['meeting_date'])) . '</span>
                    <span class="wc-meeting-month">' . date('M', strtotime($aMeeting['meeting_date'])) . '</span>
                </div>
                <div class="wc-meeting-info">
                    <strong>' . htmlspecialchars($aMeeting['title']) . '</strong>
                    <span>📍 ' . htmlspecialchars($aMeeting['location']) . '</span>
                    <span>🕐 ' . date('H:i', strtotime($aMeeting['meeting_date'])) . '</span>
                </div>
            </div>';
        }
        
        // Get pinned announcements
        $aAnnouncements = $this->_oDb->getAnnouncements(array(
            'limit' => 3,
            'space_id' => $iSpaceId,
            'status' => 'published'
        ));
        
        $sAnnouncements = '';
        foreach($aAnnouncements as $aAnn) {
            $sAnnouncements .= '<div class="wc-announcement-item">
                ' . ($aAnn['pinned'] ? '<span class="wc-pinned">📌</span>' : '') . '
                <a href="' . BX_DOL_URL_ROOT . 'page.php?i=view-ward-announcement&id=' . $aAnn['id'] . '">' . htmlspecialchars($aAnn['title']) . '</a>
                <span class="wc-announcement-date">' . $this->_timeAgo($aAnn['created']) . '</span>
            </div>';
        }
        
        // Get councilor info
        $sCouncilorInfo = $this->_getCouncilorInfoBlock($iSpaceId);
        
        return '<div class="wc-portal">
            <div class="wc-dashboard-header">
                <div class="wc-h1">🏛️ Ward Councillor Portal</div>
                <p>Welcome to your community engagement center</p>
            </div>
            
            ' . $sCouncilorInfo . '
            
            ' . (empty($iSpaceId) ? $this->_getCommunityPrompt() : '') . '
            
            <div class="wc-stats-grid">
                <div class="wc-stat-card">
                    <span class="wc-stat-icon">📋</span>
                    <div class="wc-stat-content">
                        <span class="wc-stat-value">' . $aStats['total_requests'] . '</span>
                        <span class="wc-stat-label">Total Requests</span>
                    </div>
                </div>
                ' . (isLogged() ? '
                <div class="wc-stat-card wc-stat-warning">
                    <span class="wc-stat-icon">⏳</span>
                    <div class="wc-stat-content">
                        <span class="wc-stat-value">' . $aStats['pending_requests'] . '</span>
                        <span class="wc-stat-label">Pending</span>
                    </div>
                </div>
                <div class="wc-stat-card wc-stat-info">
                    <span class="wc-stat-icon">🔄</span>
                    <div class="wc-stat-content">
                        <span class="wc-stat-value">' . $aStats['in_progress_requests'] . '</span>
                        <span class="wc-stat-label">In Progress</span>
                    </div>
                </div>' : '
                <div class="wc-stat-card wc-stat-info">
                    <span class="wc-stat-icon">📢</span>
                    <div class="wc-stat-content">
                        <span class="wc-stat-value">' . $aStats['active_requests'] . '</span>
                        <span class="wc-stat-label">Active</span>
                    </div>
                </div>') . '
                <div class="wc-stat-card wc-stat-success">
                    <span class="wc-stat-icon">✅</span>
                    <div class="wc-stat-content">
                        <span class="wc-stat-value">' . $aStats['resolved_requests'] . '</span>
                        <span class="wc-stat-label">Resolved</span>
                    </div>
                </div>
            </div>
            
            <div class="wc-dashboard-grid">
                <div class="wc-dashboard-main">
                    <div class="wc-section">
                        <div class="wc-section-header">
                            <div class="wc-h2">📋 Recent Service Requests</div>
                            <a href="' . BX_DOL_URL_ROOT . 'page.php?i=ward-requests" class="wc-link">View All →</a>
                        </div>
                        ' . ($sRequests ? $sRequests : '<p class="wc-empty">No service requests yet</p>') . '
                    </div>
                    
                    <div class="wc-section">
                        <div class="wc-section-header">
                            <div class="wc-h2">📅 Upcoming Meetings</div>
                            <a href="' . BX_DOL_URL_ROOT . 'page.php?i=ward-meetings" class="wc-link">View All →</a>
                        </div>
                        ' . ($sMeetings ? $sMeetings : '<p class="wc-empty">No upcoming meetings</p>') . '
                    </div>
                </div>
                
                <div class="wc-dashboard-sidebar">
                    <div class="wc-section">
                        <div class="wc-section-header">
                            <div class="wc-h2">📢 Announcements</div>
                            <a href="' . BX_DOL_URL_ROOT . 'page.php?i=ward-announcements" class="wc-link">View All →</a>
                        </div>
                        ' . ($sAnnouncements ? $sAnnouncements : '<p class="wc-empty">No announcements yet</p>') . '
                    </div>
                    
                    <div class="wc-quick-actions">
                        <div class="wc-h3">Quick Actions</div>
                        <a href="' . BX_DOL_URL_ROOT . 'page.php?i=create-ward-request" class="wc-btn wc-btn-primary">📝 Submit Request</a>
                        ' . ($this->_isCouncilor() ? '
                        <a href="' . BX_DOL_URL_ROOT . 'page.php?i=create-ward-meeting" class="wc-btn wc-btn-secondary">📅 Schedule Meeting</a>
                        <a href="' . BX_DOL_URL_ROOT . 'page.php?i=create-ward-announcement" class="wc-btn wc-btn-secondary">📢 Post Announcement</a>
                        ' : '') . '
                    </div>
                </div>
            </div>
        </div>';
    }

    protected function _getCurrentSpaceId()
    {
        // 1. Explicit space_id param (our AJAX calls pass this directly)
        $iSpaceId = (int)bx_get('space_id');
        if(!$iSpaceId) $iSpaceId = (int)(isset($_GET['space_id']) ? $_GET['space_id'] : 0);
        if($iSpaceId > 0) return $iSpaceId;

        // 2. UNA Space profile page — URL is /view-space-profile/eersterust
        //    BxDolPage::processSeoLink() sets $_GET['id'] = bx_spaces_data.id
        //    We join to sys_profiles to get the profile ID used as space_id
        $iContentId = (int)bx_get('id');
        if(!$iContentId) $iContentId = (int)(isset($_GET['id']) ? $_GET['id'] : 0);
        if($iContentId > 0) {
            // Confirm we're on a space page (not some other module's 'id' param)
            $sPageUri = bx_get('i');
            if(!$sPageUri) $sPageUri = isset($_GET['i']) ? $_GET['i'] : '';
            $aSpacePageUris = array('view-space-profile', 'view-space-profile-closed', 'space-profile-info');
            if(in_array($sPageUri, $aSpacePageUris) || strpos((string)$_SERVER['REQUEST_URI'], 'view-space-profile') !== false) {
                $oDb = BxDolDb::getInstance();
                $iSpaceId = (int)$oDb->getOne(
                    $oDb->prepare(
                        "SELECT p.id FROM sys_profiles p
                         WHERE p.content_id = ? AND p.type = 'bx_spaces' AND p.status = 'active'
                         LIMIT 1",
                        $iContentId
                    )
                );
                if($iSpaceId > 0) return $iSpaceId;
            }
        }

        // 3. profile_id passed directly
        $iProfileId = (int)bx_get('profile_id');
        if(!$iProfileId) $iProfileId = (int)(isset($_GET['profile_id']) ? $_GET['profile_id'] : 0);
        if($iProfileId > 0) {
            $oDb = BxDolDb::getInstance();
            $sType = $oDb->getOne(
                $oDb->prepare("SELECT type FROM sys_profiles WHERE id=? LIMIT 1", $iProfileId)
            );
            if($sType === 'bx_spaces') return $iProfileId;
        }

        // 4. Auto-detect: logged-in member belongs to exactly one space
        if(isLogged()) {
            $iProfileId = (int)bx_get_logged_profile_id();
            $oDb = BxDolDb::getInstance();
            $aSpaces = $oDb->getAll(
                $oDb->prepare(
                    "SELECT `content` FROM `bx_spaces_fans`
                     WHERE `initiator` = ? AND `mutual` = 1
                     LIMIT 2",
                    $iProfileId
                )
            );
            if(count($aSpaces) === 1) {
                return (int)$aSpaces[0]['content'];
            }
        }

        return null;
    }

    /**
     * Resolve ACL level IDs for moderation-capable roles.
     * Standard levels 7 (Moderator) and 8 (Administrator) are always included.
     * Custom levels (Leadership, Councillor) are found by matching display name
     * in sys_acl_levels.Name or sys_localization_strings.String (for lang-key names).
     * Result cached in static variable for request lifetime.
     * @return array Integer level IDs
     */
    private static $_aModLevelIds = null;
    protected function _getModeratorLevelIds()
    {
        if(self::$_aModLevelIds !== null)
            return self::$_aModLevelIds;
        $oDb = BxDolDb::getInstance();
        $aIds = array(7, 8); // Moderator, Administrator — always standard
        // Find custom levels by display name (may be stored as lang keys like _adm_prm_txt_level_name_1716021731)
        $aLevels = $oDb->getAll("SELECT ID, Name FROM sys_acl_levels WHERE ID > 8");
        $aStrings = array();
        $aLocRows = $oDb->getAll("SELECT k.`Key`, s.`String` FROM sys_localization_strings s JOIN sys_localization_keys k ON k.ID = s.IDKey WHERE s.IDLanguage = (SELECT ID FROM sys_localization_languages WHERE Name = 'en' LIMIT 1) AND k.`Key` LIKE '_adm_prm_txt_level_name_%'");
        foreach($aLocRows as $aLoc) { $aStrings[$aLoc['Key']] = $aLoc['String']; }
        foreach($aLevels as $aLevel) {
            $sName = isset($aStrings[$aLevel['Name']]) ? $aStrings[$aLevel['Name']] : $aLevel['Name'];
            $sLower = strtolower($sName);
            if(strpos($sLower, 'councillor') !== false || strpos($sLower, 'leadership') !== false)
                $aIds[] = (int)$aLevel['ID'];
        }
        self::$_aModLevelIds = array_unique($aIds);
        return self::$_aModLevelIds;
    }

    protected function _isCouncilor()
    {
        if(!isLogged()) return false;
        $iAccountId = getLoggedId();
        $oDb = BxDolDb::getInstance();
        $iPersonProfileId = (int)$oDb->getOne(
            $oDb->prepare("SELECT `id` FROM `sys_profiles` WHERE `account_id`=? AND `type`='bx_persons' AND `status`='active' LIMIT 1", $iAccountId)
        );
        if($iPersonProfileId) {
            $iLevel = (int)$oDb->getOne(
                $oDb->prepare("SELECT `IDLevel` FROM `sys_acl_levels_members` WHERE `IDMember`=? LIMIT 1", $iPersonProfileId)
            );
            $aModLevels = $this->_getModeratorLevelIds();
            if($iLevel > 0 && in_array($iLevel, $aModLevels)) return true;
        }
        // Fallback: space admin
        // bx_spaces required — skip check, always installed on this platform
        $iProfileId = (int)bx_get_logged_profile_id();
        $iSpaceId   = (int)$this->_getCurrentSpaceId();
        if(!$iSpaceId)
            return (bool)$oDb->getOne($oDb->prepare(
                "SELECT `id` FROM `bx_spaces_admins` WHERE `fan_id`=? AND (`expired`=0 OR `expired`>UNIX_TIMESTAMP()) LIMIT 1",
                $iProfileId
            ));
        return (bool)$oDb->getOne($oDb->prepare(
            "SELECT `id` FROM `bx_spaces_admins` WHERE `group_profile_id`=? AND `fan_id`=? AND (`expired`=0 OR `expired`>UNIX_TIMESTAMP()) LIMIT 1",
            $iSpaceId, $iProfileId
        ));
    }


    protected function _getCouncilorInfoBlock($iSpaceId)
    {
        if(!$iSpaceId) return '';
        
        $aWardInfo = $this->_oDb->getWardInfo($iSpaceId);
        if(!$aWardInfo) return '';
        
        return '<div class="wc-ward-info">
            <div class="wc-ward-header">
                <div class="wc-h2">🏘️ Ward ' . htmlspecialchars($aWardInfo['ward_number']) . '</div>
                <span class="wc-ward-meta">' . htmlspecialchars($aWardInfo['municipality']) . ', ' . htmlspecialchars($aWardInfo['province']) . '</span>
            </div>
            ' . ($aWardInfo['description'] ? '<p>' . nl2br(htmlspecialchars($aWardInfo['description'])) . '</p>' : '') . '
            <div class="wc-ward-contact">
                ' . ($aWardInfo['office_address'] ? '<span>📍 ' . htmlspecialchars($aWardInfo['office_address']) . '</span>' : '') . '
                ' . ($aWardInfo['office_hours'] ? '<span>🕐 ' . htmlspecialchars($aWardInfo['office_hours']) . '</span>' : '') . '
                ' . ($aWardInfo['contact_phone'] ? '<span>📞 ' . htmlspecialchars($aWardInfo['contact_phone']) . '</span>' : '') . '
            </div>
        </div>';
    }

    /**
     * Build <option> list of active spaces for community selector dropdown.
     * Used by guest-facing pages when no space context is set.
     */
    protected function _getSpaceOptions()
    {
        $sOptions = '';
        $aSpaces = $this->_oDb->getSpaces();
        if(is_array($aSpaces)) {
            foreach($aSpaces as $aSpace) {
                $sOptions .= '<option value="' . $aSpace['id'] . '">🏠 ' . htmlspecialchars($aSpace['title']) . '</option>';
            }
        }
        return $sOptions;
    }

    /**
     * Community selector dropdown for guests without space context.
     * @param string $sPage 'dashboard' or 'requests'
     * @return string HTML
     */
    protected function _getCommunitySelector($sPage)
    {
        $sUrl = ($sPage == 'dashboard') ? 'page.php?i=ward-councilor-dashboard' : 'page.php?i=ward-requests';
        $sHtml  = "<div class=\"wc-community-selector\" style=\"text-align:center;padding:16px 20px;margin-bottom:16px;background:var(--color-bg-secondary,#f9f9f9);border:1px solid var(--color-box-border,#e0e0e0);border-radius:8px;\">";
        $sHtml .= "<p style=\"margin:0 0 10px;color:var(--color-text-secondary,#666);\">Select your community to view ward information</p>";
        $sHtml .= "<select onchange=\"window.location.href='" . $sUrl . "&space_id='+this.value\" style=\"padding:8px 12px;border:1px solid #ddd;border-radius:4px;min-width:200px;\">";
        $sHtml .= "<option value=\"\">🌍 Choose your ward...</option>";
        $sHtml .= $this->_getSpaceOptions();
        $sHtml .= "</select></div>";
        return $sHtml;
    }

    private function _getCommunityPrompt()
    {
        $oDb = BxDolDb::getInstance();

        $aCountries = $oDb->getAll(
            "SELECT d.`id`, d.`space_name`, p.`id` AS `profile_id` FROM `bx_spaces_data` d
             JOIN `sys_profiles` p ON p.`content_id` = d.`id` AND p.`type` = 'bx_spaces' AND p.`status` = 'active'
             WHERE d.`parent_space` = 0 AND d.`status` = 'active'
             ORDER BY d.`space_name` ASC"
        );

        $sOptions = '<option value="">Select Country...</option>';
        if(is_array($aCountries)) {
            foreach($aCountries as $aC) {
                $sOptions .= '<option value="' . (int)$aC['id'] . '">' . htmlspecialchars($aC['space_name']) . '</option>';
            }
        }

        $sUrl = BX_DOL_URL_ROOT . 'modules/sa/ward_councilor/request.php?action=get_child_spaces&parent_id=';

        $sHtml  = '<div class="wc-community-prompt" style="text-align:center;padding:40px 20px;">';
        $sHtml .= '<div style="font-size:48px;margin-bottom:16px;">&#127968;</div>';
        $sHtml .= '<h3 style="margin:0 0 8px;">Find Your Community</h3>';
        $sHtml .= '<p style="margin:0 0 20px;">Select your location to find your neighbourhood ward portal.</p>';
        $sHtml .= '<div style="max-width:320px;margin:0 auto;">';
        $sHtml .= '<select id="wc-sel-country" style="width:100%;padding:8px;margin-bottom:10px;" onchange="wcLoadChildren(this.value,\'wc-sel-province\')">';
        $sHtml .= $sOptions;
        $sHtml .= '</select>';
        $sHtml .= '<select id="wc-sel-province" style="width:100%;padding:8px;margin-bottom:10px;" disabled onchange="wcLoadChildren(this.value,\'wc-sel-city\')">';
        $sHtml .= '<option value="">Select Province...</option>';
        $sHtml .= '</select>';
        $sHtml .= '<select id="wc-sel-city" style="width:100%;padding:8px;margin-bottom:10px;" disabled onchange="wcLoadChildren(this.value,\'wc-sel-community\')">';
        $sHtml .= '<option value="">Select City...</option>';
        $sHtml .= '</select>';
        $sHtml .= '<select id="wc-sel-community" style="width:100%;padding:8px;margin-bottom:10px;" disabled onchange="wcSelectCommunity(this.value)">';
        $sHtml .= '<option value="">Select Community...</option>';
        $sHtml .= '</select>';
        $sHtml .= '<button onclick="wcJoinCommunity()" class="bx-btn bx-btn-primary" style="width:100%;margin-top:10px;">Join My Community</button>';
        $sHtml .= '</div>';
        $sHtml .= '<script>';
        $sHtml .= 'var wcAjaxUrl = "' . $sUrl . '";';
        $sHtml .= 'var iSelectedCommunity = 0;';
        $sHtml .= 'function wcSelectCommunity(iId) {';
        $sHtml .= '  iSelectedCommunity = iId;';
        $sHtml .= '}';
        $sHtml .= 'function wcLoadChildren(iParentId, sTargetId) {';
        $sHtml .= '  if(!iParentId) return;';
        $sHtml .= '  var oSel = document.getElementById(sTargetId);';
        $sHtml .= '  oSel.disabled = true;';
        $sHtml .= '  oSel.innerHTML = "<option>Loading...</option>";';
        $sHtml .= '  fetch(wcAjaxUrl + iParentId)';
        $sHtml .= '    .then(function(r){ return r.json(); })';
        $sHtml .= '    .then(function(a) {';
        $sHtml .= '      var sHtml = "<option value=\\"\\">Select...</option>";';
        $sHtml .= '      if(a && a.length) {';
        $sHtml .= '        a.forEach(function(o){ sHtml += "<option value=\\""+o.id+"\\">"+o.name+"</option>"; });';
        $sHtml .= '      }';
        $sHtml .= '      oSel.innerHTML = sHtml;';
        $sHtml .= '      oSel.disabled = false;';
        $sHtml .= '    });';
        $sHtml .= '}';
        $sHtml .= 'function wcJoinCommunity() {';
        $sHtml .= '  var iId = iSelectedCommunity';
        $sHtml .= '          || document.getElementById("wc-sel-city").value';
        $sHtml .= '          || document.getElementById("wc-sel-province").value';
        $sHtml .= '          || document.getElementById("wc-sel-country").value;';
        $sHtml .= '  if(!iId) { alert("Please select your community from the list first."); return; }';
        $sHtml .= '  window.location.href = "page.php?i=view-space-profile&profile_id=" + iId;';
        $sHtml .= '}';
        $sHtml .= '</script>';
        $sHtml .= '</div>';

        return $sHtml;
    }



    // =====================================================
    // SERVICE REQUESTS
    // =====================================================
    
    function serviceGetRequestsBlock()
    {
        $this->_oTemplate->addCss(array('main.css'));
        
        
        $iSpaceId = $this->_getCurrentSpaceId();
        
        $aParams = array('space_id' => $iSpaceId);
        
        $sStatusFilter = bx_get('status');
        if($sStatusFilter) {
            $aParams['status'] = $sStatusFilter;
        }
        
        $sCategoryFilter = bx_get('category');
        if($sCategoryFilter) {
            $aParams['category'] = $sCategoryFilter;
        }
        
        $sPriorityFilter = bx_get('priority');
        if($sPriorityFilter) {
            $aParams['priority'] = $sPriorityFilter;
        }
        
        $sSearch = bx_get('search');
        if($sSearch) {
            $aParams['search'] = $sSearch;
        }
        
        $aRequests = $this->_oDb->getServiceRequests($aParams);
        $aStats = $this->_oDb->getStats($iSpaceId);
        
        // Status tabs — guests see All/Active/Resolved; logged-in see all including Pending/In Progress
        $sStatusTabs = '<a href="page.php?i=ward-requests" class="wc-tab' . (!$sStatusFilter ? ' active' : '') . '">All (' . $aStats['total_requests'] . ')</a>';
        if(isLogged()) {
            $sStatusTabs .= '<a href="page.php?i=ward-requests&status=pending" class="wc-tab' . ($sStatusFilter == 'pending' ? ' active' : '') . '">Pending (' . $aStats['pending_requests'] . ')</a>';
            $sStatusTabs .= '<a href="page.php?i=ward-requests&status=in_progress" class="wc-tab' . ($sStatusFilter == 'in_progress' ? ' active' : '') . '">In Progress (' . $aStats['in_progress_requests'] . ')</a>';
        }
        $sStatusTabs .= '<a href="page.php?i=ward-requests&status=resolved" class="wc-tab' . ($sStatusFilter == 'resolved' ? ' active' : '') . '">Resolved (' . $aStats['resolved_requests'] . ')</a>';
        
        $sContent = '';
        if(empty($aRequests)) {
            $sContent = '<div class="wc-empty-state">
                <div class="wc-empty-icon">📋</div>
                <div class="wc-h3">No Service Requests Found</div>
                <p>There are no service requests matching your criteria.</p>
                <a href="' . BX_DOL_URL_ROOT . 'page.php?i=create-ward-request" class="wc-btn wc-btn-primary">Submit a Request</a>
            </div>';
        } else {
            $aGroups = $this->_groupByMonth($aRequests, 'created');
            $sContent = $this->_renderGrouped($aGroups, array($this, '_renderRequestCard'));
        }
        
        // Community selector for guests without space context
        $sCommunitySelector = empty($iSpaceId) ? $this->_getCommunityPrompt() : '';

        return '<div class="wc-requests">
            <div class="wc-page-header">
                <div class="wc-h1">📋 Service Requests</div>
                <a href="' . BX_DOL_URL_ROOT . 'page.php?i=create-ward-request" class="wc-btn wc-btn-primary">+ Submit Request</a>
            </div>
            ' . $sCommunitySelector . '
            
            <div class="wc-status-filter">' . $sStatusTabs . '</div>
            
            <div class="wc-search-box">
                <form method="get">
                    <input type="hidden" name="i" value="ward-requests">
                    ' . ($sStatusFilter ? '<input type="hidden" name="status" value="' . htmlspecialchars($sStatusFilter) . '">' : '') . '
                    <input type="text" name="search" placeholder="Search requests..." value="' . htmlspecialchars($sSearch ? $sSearch : '') . '">
                    <button type="submit">🔍</button>
                </form>
            </div>
            
            <div class="wc-grouped-list">' . $sContent . '</div>
        </div>';
    }

    protected function _renderRequestCard($aRequest)
    {
        $sUrl = BX_DOL_URL_ROOT . 'page.php?i=view-ward-request&id=' . $aRequest['id'];
        
        $sPriorityClass = 'wc-priority-' . $aRequest['priority'];
        $sStatusClass = 'wc-status-' . $aRequest['status'];
        
        return '<div class="wc-request-card ' . $sPriorityClass . '">
            <div class="wc-request-header">
                <span class="wc-ref-number">' . htmlspecialchars($aRequest['reference_number']) . '</span>
                <span class="wc-status ' . $sStatusClass . '">' . $this->_getStatusLabel($aRequest['status']) . '</span>
            </div>
            <div class="wc-request-body">
                <div class="wc-h3"><a href="' . $sUrl . '">' . htmlspecialchars($aRequest['title']) . '</a></div>
                <p class="wc-request-desc">' . htmlspecialchars(substr($aRequest['description'] ? $aRequest['description'] : '', 0, 150)) . '</p>
                <div class="wc-request-meta">
                    <span class="wc-category">' . $this->_getCategoryLabel($aRequest['category']) . '</span>
                    <span class="wc-priority">Priority: ' . $this->_getPriorityLabel($aRequest['priority']) . '</span>
                    <span class="wc-date">' . $this->_timeAgo($aRequest['created']) . '</span>
                </div>
            </div>
            <div class="wc-request-footer">
                <span>👁 ' . (int)$aRequest['views'] . ' views</span>
                ' . ($aRequest['location'] ? '<span>📍 ' . htmlspecialchars($aRequest['location']) . '</span>' : '') . '
            </div>
        </div>';
    }

    function serviceGetRequestDetailsBlock()
    {
        $this->_oTemplate->addCss(array('main.css'));

        $iRequestId = (int)bx_get('id');
        if(!$iRequestId) return 'Request not found';

        $aRequest = $this->_oDb->getServiceRequest($iRequestId);
        if(!$aRequest) return 'Request not found';

        $this->_oDb->updateServiceRequestViews($iRequestId);
        
        // Activity trail
        $aNotes = $this->_oDb->getNotes($iRequestId);
        $sTrail = '';
        foreach($aNotes as $aNote) {
            $sStatusBadge = $aNote['status_change']
                ? '<span class="wc-status wc-status-' . $aNote['status_change'] . '" style="margin-left:6px;">' . $this->_getStatusLabel($aNote['status_change']) . '</span>'
                : '';
            $sRoleBadge = '<span class="wc-role-badge wc-role-' . $aNote['actor_role'] . '">' . ucfirst($aNote['actor_role']) . '</span>';
            $sAuthor = $aNote['author_name'] ? htmlspecialchars($aNote['author_name']) : 'Unknown';
            $sTrail .= '<div class="wc-note-entry">
                <div class="wc-note-meta">
                    <span>' . date('d M Y H:i', strtotime($aNote['created'])) . '</span>
                    ' . $sRoleBadge . '
                    <span class="wc-note-author">— ' . $sAuthor . '</span>
                    ' . $sStatusBadge . '
                </div>
                <div class="wc-note-text">' . nl2br(htmlspecialchars($aNote['note'])) . '</div>
            </div>';
        }

        // Councilor response form
        $sResponseForm = '';
        if($this->_isCouncilor() && $aRequest['status'] != 'closed') {
            if($_SERVER['REQUEST_METHOD'] === 'POST' && bx_get('response_status')) {
                if (!$this->checkAllowEdit($iRequestId))
                    return MsgBox('Access Denied. This area is restricted to Ward Councillors and Leadership only.');
                $sNewStatus = bx_get('response_status');
                $sNoteText  = trim(bx_get('councilor_notes'));

                $this->_oDb->updateServiceRequest($iRequestId, array('status' => $sNewStatus));

                // Fire edit alert for timeline
                $iAuthorProfileId = bx_get_logged_profile_id();
                $aFresh = $this->_oDb->getServiceRequest($iRequestId);
                $iOwnerId = !empty($aFresh['space_id']) ? (int)$aFresh['space_id'] : $iAuthorProfileId;
                try {
                    $oAlert = new BxDolAlerts('sa_ward_councilor', 'edited', $iRequestId, $iAuthorProfileId, array('owner_id' => $iOwnerId));
                    $oAlert->alert();
                } catch(Exception $e) {}

                // Migrate legacy councilor_notes on first update
                $aFresh = $this->_oDb->getServiceRequest($iRequestId);
                if(!empty($aFresh['councilor_notes'])) {
                    $bMigrated = false;
                    foreach($aNotes as $n) { if($n['note'] === $aFresh['councilor_notes']) { $bMigrated = true; break; } }
                    if(!$bMigrated)
                        $this->_oDb->addNote($iRequestId, (int)$aFresh['author_id'], $aFresh['councilor_notes'], $aFresh['status']);
                }

                if($sNoteText)
                    $this->_oDb->addNote($iRequestId, (int)bx_get_logged_profile_id(), $sNoteText, $sNewStatus);

                $aRequest = $this->_oDb->getServiceRequest($iRequestId);
                $aNotes   = $this->_oDb->getNotes($iRequestId);
                $sTrail   = '';
                foreach($aNotes as $aNote) {
                    $sStatusBadge = $aNote['status_change']
                        ? '<span class="wc-status wc-status-' . $aNote['status_change'] . '" style="margin-left:6px;">' . $this->_getStatusLabel($aNote['status_change']) . '</span>'
                        : '';
                    $sRoleBadge2 = '<span class="wc-role-badge wc-role-' . $aNote['actor_role'] . '">' . ucfirst($aNote['actor_role']) . '</span>';
                    $sAuthor2 = $aNote['author_name'] ? htmlspecialchars($aNote['author_name']) : 'Unknown';
                    $sTrail .= '<div class="wc-note-entry">
                        <div class="wc-note-meta">
                            <span>' . date('d M Y H:i', strtotime($aNote['created'])) . '</span>
                            ' . $sRoleBadge2 . '
                            <span class="wc-note-author">— ' . $sAuthor2 . '</span>
                            ' . $sStatusBadge . '
                        </div>
                        <div class="wc-note-text">' . nl2br(htmlspecialchars($aNote['note'])) . '</div>
                    </div>';
                }
                $sResponseForm = '<div class="wc-success">✅ Updated successfully!</div>';
            }

            $sStatusOptions = '';
            foreach($this->_aRequestStatuses as $sKey => $sLabel) {
                $sSelected = ($aRequest['status'] == $sKey) ? ' selected' : '';
                $sStatusOptions .= '<option value="' . $sKey . '"' . $sSelected . '>' . $sLabel . '</option>';
            }

            $sResponseForm .= '<div class="wc-councilor-response">
                <form method="post" class="wc-form">
                    <div class="wc-form-group">
                        <label>Update Status</label>
                        <select name="response_status">' . $sStatusOptions . '</select>
                    </div>
                    <div class="wc-form-group">
                        <label>Add Note to Trail</label>
                        <textarea name="councilor_notes" rows="3" placeholder="Add a note..."></textarea>
                    </div>
                    <button type="submit" class="wc-btn wc-btn-primary">Update Request</button>
                </form>
            </div>';
        }

        return '<div class="wc-detail-wrap">

            <div class="wc-detail-hero">
                <div class="wc-detail-hero-top">
                    <span class="wc-ref-number">' . htmlspecialchars($aRequest['reference_number']) . '</span>
                    <span class="wc-status wc-status-' . $aRequest['status'] . '">' . $this->_getStatusLabel($aRequest['status']) . '</span>
                    <span class="wc-priority-tag wc-priority-' . $aRequest['priority'] . '">' . $this->_getPriorityLabel($aRequest['priority']) . '</span>
                </div>
                <div class="wc-detail-title">' . htmlspecialchars($aRequest['title']) . '</div>
                <div class="wc-detail-meta">
                    <span>📁 ' . $this->_getCategoryLabel($aRequest['category']) . '</span>
                    <span>📅 ' . date('d M Y', strtotime($aRequest['created'])) . '</span>
                    <span>👁 ' . (int)$aRequest['views'] . ' views</span>
                </div>
            </div>

            <div class="wc-detail-body">

                <div class="wc-detail-section">
                    <div class="wc-section-label">📝 Description</div>
                    <div class="wc-section-content">' . nl2br(htmlspecialchars($aRequest['description'] ? $aRequest['description'] : 'No description provided.')) . '</div>
                </div>

                <div class="wc-detail-row">
                    <div class="wc-detail-section">
                        <div class="wc-section-label">📍 Location</div>
                        <div class="wc-section-content">' . htmlspecialchars($aRequest['location'] ? $aRequest['location'] : 'Not specified') . '</div>
                    </div>
                    <div class="wc-detail-section">
                        <div class="wc-section-label">📞 Contact</div>
                        <div class="wc-section-content">
                            ' . ($aRequest['contact_phone'] ? '<div>📱 ' . htmlspecialchars($aRequest['contact_phone']) . '</div>' : '') . '
                            ' . ($aRequest['contact_email'] ? '<div>✉️ ' . htmlspecialchars($aRequest['contact_email']) . '</div>' : '') . '
                            ' . (!$aRequest['contact_phone'] && !$aRequest['contact_email'] ? '<span class="wc-muted">Not provided</span>' : '') . '
                        </div>
                    </div>
                </div>


                ' . ($sTrail ? '
                <div class="wc-detail-section wc-notes-section">
                    <div class="wc-section-label">📋 Activity Trail</div>
                    <div class="wc-trail">' . $sTrail . '</div>
                </div>
                ' : '') . '

                ' . $sResponseForm . '

            </div>
        </div>';
    }

    function serviceGetCreateRequestBlock()
    {
        $this->_oTemplate->addCss(array('main.css'));

        if (!isLogged())
            return '<div class="wc-login-required" style="text-align:center;padding:20px;">
<p>Please log in to submit a service request.</p>
<a href="' . BX_DOL_URL_ROOT . 'page.php?i=login" class="bx-btn bx-btn-primary">Log In</a>&nbsp;
<a href="' . BX_DOL_URL_ROOT . 'page.php?i=create-account" class="bx-btn">Register</a>
</div>';

        // Block logged-in users who have not joined the current space/community.
        $iSpaceId = $this->_getCurrentSpaceId();
        if($iSpaceId) {
            $iProfileId = (int)bx_get_logged_profile_id();
            $oDb = BxDolDb::getInstance();
            $bIsMember = (bool)$oDb->getOne(
                $oDb->prepare(
                    "SELECT COUNT(*) FROM bx_spaces_fans
                     WHERE initiator = ?
                     AND content = ?
                     AND mutual = 1",
                    $iProfileId, $iSpaceId
                )
            );
            if(!$bIsMember) {
                $oSpace = BxDolProfile::getInstance($iSpaceId);
                $sSpaceName = $oSpace ? $oSpace->getDisplayName() : 'this community';
                return '<div class="wc-login-required" style="text-align:center;padding:20px;">
            <p>You need to be a member of ' . htmlspecialchars($sSpaceName) . '
            to submit a ward service request.</p>
            <a href="' . BX_DOL_URL_ROOT . 'page.php?i=view-space-profile&profile_id='
            . $iSpaceId . '" class="bx-btn bx-btn-primary">
            Join ' . htmlspecialchars($sSpaceName) . ' →</a>
        </div>';
            }
        }

        $sMessage = '';
        
        if($_SERVER['REQUEST_METHOD'] === 'POST') {
            $sTitle = bx_get('title');
            $sDescription = bx_get('description');
            $sCategory = bx_get('category');
            $sLocation = bx_get('location');
            $sContactPhone = bx_get('contact_phone');
            $sContactEmail = bx_get('contact_email');
            $sPriority = bx_get('priority');
            $iSpaceId = bx_get('space_id');
            $iAllowViewTo = (int)bx_get('allow_view_to') ?: BX_DOL_PG_ALL;
            
            $aErrors = array();
            if(empty($sTitle)) $aErrors[] = 'Title is required';
            if(empty($sDescription)) $aErrors[] = 'Description is required';
            if(empty($sCategory)) $aErrors[] = 'Please select a category';
            
            if(empty($aErrors)) {
                $aData = array(
                    'title' => $sTitle,
                    'description' => $sDescription,
                    'category' => $sCategory,
                    'location' => $sLocation ? $sLocation : '',
                    'contact_phone' => $sContactPhone ? $sContactPhone : '',
                    'contact_email' => $sContactEmail ? $sContactEmail : '',
                    'priority' => $sPriority ? $sPriority : 'medium',
                    'author_id' => isLogged() ? getLoggedId() : 0,
                    'space_id' => $iSpaceId ? $iSpaceId : null,
                    'allow_view_to' => $iAllowViewTo,
                    'status' => 'pending',
                    'created' => date('Y-m-d H:i:s')
                );
                
                $iRequestId = $this->_oDb->addServiceRequest($aData);
                
                if($iRequestId) {
                    $iAuthorProfileId = bx_get_logged_profile_id();
                    $iOwnerId = !empty($aData['space_id']) ? (int)$aData['space_id'] : $iAuthorProfileId;
                    try {
                        if(class_exists('BxDolAlerts')) {
                            $oAlert = new BxDolAlerts('sa_ward_councilor', 'added', $iRequestId, $iAuthorProfileId, array(
                                'owner_id' => $iOwnerId,
                                'object_author_id' => $iAuthorProfileId,
                                'privacy_view' => (int)($aData['allow_view_to'] ? $aData['allow_view_to'] : BX_DOL_PG_ALL),
                            ));
                            $oAlert->alert();
                        }
                    } catch(Exception $e) {}
                    header('Location: ' . BX_DOL_URL_ROOT . 'page.php?i=view-ward-request&id=' . $iRequestId);
                    exit;
                } else {
                    $sMessage = '<div class="wc-error">Error submitting request. Please try again.</div>';
                }
            } else {
                $sMessage = '<div class="wc-error">⚠️ <ul><li>' . implode('</li><li>', $aErrors) . '</li></ul></div>';
            }
        }
        
        $sCategoryOptions = '<option value="">Select a category...</option>';
        foreach($this->_aRequestCategories as $sKey => $sLabel) {
            $sCategoryOptions .= '<option value="' . $sKey . '">' . $sLabel . '</option>';
        }
        
        $sPriorityOptions = '';
        foreach($this->_aPriorityLevels as $sKey => $sLabel) {
            $sPriorityOptions .= '<option value="' . $sKey . '"' . ($sKey == 'medium' ? ' selected' : '') . '>' . $sLabel . '</option>';
        }
        
        // Space options
        $aSpaces = $this->_oDb->getSpaces();
        $sSpaceOptions = '<option value="">🌍 Select your ward/community...</option>';
        if(is_array($aSpaces)) {
            foreach($aSpaces as $aSpace) {
                $sSpaceOptions .= '<option value="' . $aSpace['id'] . '">🏠 ' . htmlspecialchars($aSpace['title']) . '</option>';
            }
        }
        
        return '<style>
.wc-form-group input,.wc-form-group select,.wc-form-group textarea{color:#2d3748!important;background-color:#fff!important}
.wc-form-group select option{color:#2d3748!important;background-color:#fff!important}
.wc-form-group select option:checked{background-color:#007749!important;color:#fff!important}
</style><div class="wc-create-request">
            <div class="wc-create-header">
                <div class="wc-h2">📝 Submit Service Request</div>
                <p>Report an issue or request a service from your ward councillor</p>
            </div>
            
            ' . $sMessage . '
            
            <div class="wc-create-tips">
                <h4>💡 Tips for a Better Response</h4>
                <ul>
                    <li>Be specific about the location</li>
                    <li>Provide clear contact details</li>
                    <li>Include relevant details about the issue</li>
                    <li>Upload photos if possible (coming soon)</li>
                </ul>
            </div>
            
            <form method="post" class="wc-form">
                
                <div class="wc-form-group">
                    <label>Title *</label>
                    <input type="text" name="title" placeholder="e.g., Pothole on Main Street" required>
                </div>
                
                <div class="wc-form-row">
                    <div class="wc-form-group">
                        <label>Category *</label>
                        <select name="category" required>' . $sCategoryOptions . '</select>
                    </div>
                    <div class="wc-form-group">
                        <label>Priority</label>
                        <select name="priority">' . $sPriorityOptions . '</select>
                    </div>
                </div>
                
                <div class="wc-form-group">
                    <label>Description *</label>
                    <textarea name="description" rows="5" placeholder="Describe the issue in detail..." required></textarea>
                </div>
                
                <div class="wc-form-group">
                    <label>Location / Address</label>
                    <input type="text" name="location" placeholder="e.g., 123 Main Street, Near the park">
                </div>
                
                <div class="wc-form-row">
                    <div class="wc-form-group">
                        <label>Contact Phone</label>
                        <input type="tel" name="contact_phone" placeholder="e.g., 082 123 4567">
                    </div>
                    <div class="wc-form-group">
                        <label>Contact Email</label>
                        <input type="email" name="contact_email" placeholder="e.g., your@email.com">
                    </div>
                </div>
                
                <div class="wc-form-group">
                    <label>Visible to</label>
                    ' . $this->_getVisibilityChooser('sa_ward_councilor_request_allow_view_to', 4) . '
                </div>
                
                <button type="submit" class="wc-btn wc-btn-primary wc-btn-large">📤 Submit Request</button>
            </form>
        </div>';
    }

    // =====================================================
    // MEETINGS
    // =====================================================
    
    function serviceGetMeetingsBlock()
    {
        $this->_oTemplate->addCss(array('main.css'));
        
        
        $iSpaceId = $this->_getCurrentSpaceId();
        
        $aMeetings = $this->_oDb->getMeetings(array(
            'space_id' => $iSpaceId,
            'status' => 'scheduled'
        ));
        
        $sContent = '';
        if(empty($aMeetings)) {
            $sContent = '<div class="wc-empty-state">
                <div class="wc-empty-icon">📅</div>
                <div class="wc-h3">No Upcoming Meetings</div>
                <p>There are no scheduled meetings at this time.</p>
                ' . ($this->_isCouncilor() ? '<a href="' . BX_DOL_URL_ROOT . 'page.php?i=create-ward-meeting" class="wc-btn wc-btn-primary">Schedule a Meeting</a>' : '') . '
            </div>';
        } else {
            $aGroups = $this->_groupByMonth($aMeetings, 'meeting_date');
            $sContent = $this->_renderGrouped($aGroups, array($this, '_renderMeetingCard'), 'wc-meetings-grid');
        }
        
        return '<div class="wc-meetings">
            <div class="wc-page-header">
                <div class="wc-h1">📅 Ward Meetings</div>
                ' . ($this->_isCouncilor() ? '<a href="' . BX_DOL_URL_ROOT . 'page.php?i=create-ward-meeting" class="wc-btn wc-btn-primary">+ Schedule Meeting</a>' : '') . '
            </div>
            
            <div class="wc-grouped-list">' . $sContent . '</div>
        </div>';
    }

    protected function _renderMeetingCard($aMeeting)
    {
        $sUrl = BX_DOL_URL_ROOT . 'page.php?i=view-ward-meeting&id=' . $aMeeting['id'];
        $bIsPast = strtotime($aMeeting['meeting_date']) < time();
        $sTypeLabel = ucfirst(str_replace('_', ' ', $aMeeting['type'])) . ' Meeting';

        return '<div class="wc-request-card' . ($bIsPast ? ' wc-meeting-past' : '') . '">
            <div class="wc-request-header">
                <span class="wc-ref-number">' . date('d M Y · H:i', strtotime($aMeeting['meeting_date'])) . '</span>
                <span class="wc-status wc-status-' . $aMeeting['status'] . '">' . ($bIsPast ? 'Past' : ucfirst($aMeeting['status'])) . '</span>
            </div>
            <div class="wc-request-body">
                <div class="wc-h2"><a href="' . $sUrl . '">' . htmlspecialchars($aMeeting['title']) . '</a></div>
                <div class="wc-request-meta">
                    <span>📍 ' . htmlspecialchars($aMeeting['location']) . '</span>
                    <span>' . $sTypeLabel . '</span>
                </div>
            </div>
        </div>';
    }

    function serviceGetMeetingDetailsBlock()
    {
        $this->_oTemplate->addCss(array('main.css'));

        $iMeetingId = (int)bx_get('id');
        if(!$iMeetingId) return 'Meeting not found';

        $aMeeting = $this->_oDb->getMeeting($iMeetingId);
        if(!$aMeeting) return 'Meeting not found';

        $bIsPast = strtotime($aMeeting['meeting_date']) < time();
        $sStatus = $bIsPast ? 'Completed' : ucfirst($aMeeting['status']);
        $sBackUrl = BX_DOL_URL_ROOT . 'page.php?i=ward-meetings';

        return '<div class="wc-detail-wrap">
            <div class="wc-detail-hero">
                <div class="wc-detail-hero-top">
                    <span class="wc-status wc-status-' . htmlspecialchars($aMeeting['status']) . '">' . htmlspecialchars($sStatus) . '</span>
                    <span class="wc-priority-tag" style="background:rgba(0,119,73,0.2);color:#6ee7b7;border:1px solid rgba(0,119,73,0.4);">' . ucfirst(str_replace('_', ' ', $aMeeting['type'])) . ' Meeting</span>
                    ' . ($bIsPast ? '<span class="wc-priority-tag" style="background:rgba(108,117,125,0.2);color:#adb5bd;border:1px solid rgba(108,117,125,0.4);">Past</span>' : '') . '
                </div>
                <div class="wc-detail-title">' . htmlspecialchars($aMeeting['title']) . '</div>
                <div class="wc-detail-meta">
                    <span>📅 ' . date('d M Y', strtotime($aMeeting['meeting_date'])) . '</span>
                    <span>🕐 ' . date('H:i', strtotime($aMeeting['meeting_date'])) . '</span>
                    <span>📍 ' . htmlspecialchars($aMeeting['location']) . '</span>
                </div>
            </div>
            <div class="wc-detail-body">
                <div class="wc-detail-section">
                    <div class="wc-section-label">📋 Agenda / Details</div>
                    <div class="wc-section-content">' . nl2br(htmlspecialchars($aMeeting['description'] ? $aMeeting['description'] : 'Meeting agenda will be shared soon.')) . '</div>
                </div>
                <div>
                    <a href="' . $sBackUrl . '" class="wc-btn wc-btn-secondary">← Back to Meetings</a>
                </div>
            </div>
        </div>';
    }

    function serviceGetCreateMeetingBlock()
    {
        $this->_oTemplate->addCss(array('main.css'));

        if(!$this->_isCouncilor()) {
            return '<div class="wc-error">Only councillors can schedule meetings.</div>';
        }
        
        $sMessage = '';
        
        if($_SERVER['REQUEST_METHOD'] === 'POST') {
            $sTitle = bx_get('title');
            $sDescription = bx_get('description');
            $sMeetingDate = bx_get('meeting_date');
            $sLocation = bx_get('location');
            $sType = bx_get('type');
            $iSpaceId = bx_get('space_id');
            $iAllowViewTo = (int)bx_get('allow_view_to') ?: 1;
            
            $aErrors = array();
            if(empty($sTitle)) $aErrors[] = 'Title is required';
            if(empty($sMeetingDate)) $aErrors[] = 'Date and time is required';
            if(empty($sLocation)) $aErrors[] = 'Location is required';
            
            if(empty($aErrors)) {
                $aData = array(
                    'title' => $sTitle,
                    'description' => $sDescription ? $sDescription : '',
                    'meeting_date' => $sMeetingDate,
                    'location' => $sLocation,
                    'type' => $sType ? $sType : 'community',
                    'space_id' => $iSpaceId ? $iSpaceId : null,
                    'allow_view_to' => $iAllowViewTo,
                    'status' => 'scheduled'
                );
                
                $iMeetingId = $this->_oDb->addMeeting($aData);
                
                if($iMeetingId) {
                    $iAuthorProfileId = bx_get_logged_profile_id();
                    $iOwnerId = !empty($aData['space_id']) ? (int)$aData['space_id'] : $iAuthorProfileId;
                    try {
                        $oAlert = new BxDolAlerts('sa_ward_councilor', 'added', $iMeetingId, $iAuthorProfileId, array(
                            'owner_id' => $iOwnerId,
                            'object_author_id' => $iAuthorProfileId,
                            'privacy_view' => BX_DOL_PG_ALL,
                        ));
                        $oAlert->alert();
                    } catch(Exception $e) {}
                    $sMessage = '<div class="wc-success">✅ Meeting scheduled successfully!</div>';
                } else {
                    $sMessage = '<div class="wc-error">Error scheduling meeting. Please try again.</div>';
                }
            } else {
                $sMessage = '<div class="wc-error">⚠️ <ul><li>' . implode('</li><li>', $aErrors) . '</li></ul></div>';
            }
        }
        
        $aSpaces = $this->_oDb->getSpaces();
        $sSpaceOptions = '<option value="">Select ward...</option>';
        if(is_array($aSpaces)) {
            foreach($aSpaces as $aSpace) {
                $sSpaceOptions .= '<option value="' . $aSpace['id'] . '">' . htmlspecialchars($aSpace['title']) . '</option>';
            }
        }

        return '<div class="wc-create-meeting">
            <div class="wc-create-header">
                <div class="wc-h2">📅 Schedule Ward Meeting</div>
                <p>Schedule a community meeting or public forum</p>
            </div>
            
            ' . $sMessage . '
            
            <form method="post" class="wc-form">
                
                <div class="wc-form-group">
                    <label>Meeting Title *</label>
                    <input type="text" name="title" placeholder="e.g., Quarterly Community Meeting" required>
                </div>
                
                <div class="wc-form-row">
                    <div class="wc-form-group">
                        <label>Date & Time *</label>
                        <input type="datetime-local" name="meeting_date" required>
                    </div>
                    <div class="wc-form-group">
                        <label>Meeting Type</label>
                        <select name="type">
                            <option value="community">Community Meeting</option>
                            <option value="public_forum">Public Forum</option>
                            <option value="committee">Committee Meeting</option>
                            <option value="special">Special Meeting</option>
                        </select>
                    </div>
                </div>
                
                <div class="wc-form-group">
                    <label>Location *</label>
                    <input type="text" name="location" placeholder="e.g., Community Hall, 123 Main Street" required>
                </div>
                
                <div class="wc-form-group">
                    <label>Description</label>
                    <textarea name="description" rows="4" placeholder="Agenda items, topics to be discussed..."></textarea>
                </div>
                
                <div class="wc-form-group">
                    <label>Visible to</label>
                    ' . $this->_getVisibilityChooser('sa_ward_councilor_meeting_allow_view_to', 3) . '
                </div>
                
                <button type="submit" class="wc-btn wc-btn-primary wc-btn-large">📅 Schedule Meeting</button>
            </form>
        </div>';
    }

    // =====================================================
    // ANNOUNCEMENTS
    // =====================================================
    
    function serviceGetAnnouncementsBlock()
    {
        $this->_oTemplate->addCss(array('main.css'));
        
        
        $iSpaceId = $this->_getCurrentSpaceId();
        
        $sSearch = bx_get('search');
        $aParams = array(
            'space_id' => $iSpaceId,
            'status' => 'published'
        );
        if($sSearch) {
            $aParams['search'] = $sSearch;
        }
        
        $aAnnouncements = $this->_oDb->getAnnouncements($aParams);
        
        $sContent = '';
        if(empty($aAnnouncements)) {
            $sContent = '<div class="wc-empty-state">
                <div class="wc-empty-icon">📢</div>
                <div class="wc-h3">No Announcements</div>
                <p>There are no announcements at this time.</p>
                ' . ($this->_isCouncilor() ? '<a href="' . BX_DOL_URL_ROOT . 'page.php?i=create-ward-announcement" class="wc-btn wc-btn-primary">Post Announcement</a>' : '') . '
            </div>';
        } else {
            $aGroups = $this->_groupByMonth($aAnnouncements, 'created');
            $sContent = $this->_renderGrouped($aGroups, array($this, '_renderAnnouncementCard'), 'wc-announcements-list');
        }
        
        return '<div class="wc-announcements">
            <div class="wc-page-header">
                <div class="wc-h1">📢 Ward Announcements</div>
                ' . ($this->_isCouncilor() ? '<a href="' . BX_DOL_URL_ROOT . 'page.php?i=create-ward-announcement" class="wc-btn wc-btn-primary">+ Post Announcement</a>' : '') . '
            </div>
            
            <div class="wc-search-box">
                <form method="get">
                    <input type="hidden" name="i" value="ward-announcements">
                    <input type="text" name="search" placeholder="Search announcements..." value="' . htmlspecialchars($sSearch ? $sSearch : '') . '">
                    <button type="submit">🔍</button>
                </form>
            </div>
            
            <div class="wc-grouped-list">' . $sContent . '</div>
        </div>';
    }

    protected function _renderAnnouncementCard($aAnn)
    {
        $sUrl = BX_DOL_URL_ROOT . 'page.php?i=view-ward-announcement&id=' . $aAnn['id'];

        return '<div class="wc-request-card">
            <div class="wc-request-header">
                ' . ($aAnn['pinned'] ? '<span class="wc-status" style="background:rgba(0,119,73,0.2);color:#6ee7b7;">📌 Pinned</span>' : '<span></span>') . '
                <span class="wc-status wc-status-' . $aAnn['status'] . '">' . ucfirst($aAnn['status']) . '</span>
            </div>
            <div class="wc-request-body">
                <div class="wc-h2"><a href="' . $sUrl . '">' . htmlspecialchars($aAnn['title']) . '</a></div>
                <p class="wc-request-desc">' . htmlspecialchars(substr($aAnn['content'] ? $aAnn['content'] : '', 0, 160)) . '...</p>
                <div class="wc-request-meta">
                    <span>📅 ' . date('d M Y', strtotime($aAnn['created'])) . '</span>
                    <span>👁 ' . (int)$aAnn['views'] . ' views</span>
                </div>
            </div>
        </div>';
    }

    function serviceGetAnnouncementDetailsBlock()
    {
        $this->_oTemplate->addCss(array('main.css'));

        $iAnnId = (int)bx_get('id');
        if(!$iAnnId) return 'Announcement not found';

        $aAnn = $this->_oDb->getAnnouncement($iAnnId);
        if(!$aAnn) return 'Announcement not found';

        $this->_oDb->updateAnnouncementViews($iAnnId);
        
        return '<div class="wc-detail-wrap">
            <div class="wc-detail-hero">
                <div class="wc-detail-hero-top">
                    ' . ($aAnn['pinned'] ? '<span class="wc-priority-tag" style="background:rgba(0,119,73,0.2);color:#6ee7b7;border:1px solid rgba(0,119,73,0.4);">📌 Pinned</span>' : '') . '
                    <span class="wc-status wc-status-' . $aAnn['status'] . '">' . ucfirst($aAnn['status']) . '</span>
                </div>
                <div class="wc-detail-title">' . htmlspecialchars($aAnn['title']) . '</div>
                <div class="wc-detail-meta">
                    <span>📅 ' . date('d M Y', strtotime($aAnn['created'])) . '</span>
                    <span>👁 ' . (int)$aAnn['views'] . ' views</span>
                </div>
            </div>
            <div class="wc-detail-body">
                <div class="wc-detail-section">
                    <div class="wc-section-label">📢 Announcement</div>
                    <div class="wc-section-content">' . nl2br(htmlspecialchars($aAnn['content'])) . '</div>
                </div>
            </div>
        </div>';
    }

    function serviceGetCreateAnnouncementBlock()
    {
        $this->_oTemplate->addCss(array('main.css'));

        if(!$this->_isCouncilor()) {
            return '<div class="wc-error">Only councillors can post announcements.</div>';
        }
        
        $sMessage = '';
        
        if($_SERVER['REQUEST_METHOD'] === 'POST') {
            $sTitle = bx_get('title');
            $sContent = bx_get('content');
            $iPinned = (int)bx_get('pinned');
            $iSpaceId = bx_get('space_id');
            $iAllowViewTo = (int)bx_get('allow_view_to') ?: 1;
            
            $aErrors = array();
            if(empty($sTitle)) $aErrors[] = 'Title is required';
            if(empty($sContent)) $aErrors[] = 'Content is required';
            
            if(empty($aErrors)) {
                $aData = array(
                    'title' => $sTitle,
                    'content' => $sContent,
                    'pinned' => $iPinned,
                    'author_id' => getLoggedId(),
                    'space_id' => $iSpaceId ? $iSpaceId : null,
                    'allow_view_to' => $iAllowViewTo,
                    'status' => 'published'
                );
                
                $iAnnId = $this->_oDb->addAnnouncement($aData);
                
                if($iAnnId) {
                    $iAuthorProfileId = bx_get_logged_profile_id();
                    $iOwnerId = !empty($aData['space_id']) ? (int)$aData['space_id'] : $iAuthorProfileId;
                    try {
                        $oAlert = new BxDolAlerts('sa_ward_councilor', 'added', $iAnnId, $iAuthorProfileId, array(
                            'owner_id' => $iOwnerId,
                            'object_author_id' => $iAuthorProfileId,
                            'privacy_view' => BX_DOL_PG_ALL,
                        ));
                        $oAlert->alert();
                    } catch(Exception $e) {}
                    header('Location: ' . BX_DOL_URL_ROOT . 'page.php?i=view-ward-announcement&id=' . $iAnnId);
                    exit;
                } else {
                    $sMessage = '<div class="wc-error">Error posting announcement. Please try again.</div>';
                }
            } else {
                $sMessage = '<div class="wc-error">⚠️ <ul><li>' . implode('</li><li>', $aErrors) . '</li></ul></div>';
            }
        }
        
        $aSpaces = $this->_oDb->getSpaces();
        $sSpaceOptions = '<option value="">Select ward...</option>';
        foreach($aSpaces as $aSpace) {
            $sSpaceOptions .= '<option value="' . $aSpace['id'] . '">' . htmlspecialchars($aSpace['title']) . '</option>';
        }
        
        return '<div class="wc-create-announcement">
            <div class="wc-create-header">
                <div class="wc-h2">📢 Post Announcement</div>
                <p>Share news and updates with your community</p>
            </div>
            
            ' . $sMessage . '
            
            <form method="post" class="wc-form">
                
                <div class="wc-form-group">
                    <label>Title *</label>
                    <input type="text" name="title" placeholder="e.g., Water Maintenance Notice" required>
                </div>
                
                <div class="wc-form-group">
                    <label>Content *</label>
                    <textarea name="content" rows="8" placeholder="Write your announcement here..." required></textarea>
                </div>
                
                <div class="wc-form-group">
                    <label><input type="checkbox" name="pinned" value="1"> 📌 Pin this announcement (shows at top)</label>
                </div>
                
                <div class="wc-form-group">
                    <label>Visible to</label>
                    ' . $this->_getVisibilityChooser('sa_ward_councilor_announcement_allow_view_to', 3) . '
                </div>
                
                <button type="submit" class="wc-btn wc-btn-primary wc-btn-large">📢 Post Announcement</button>
            </form>
        </div>';
    }

    // =====================================================
    // MY REQUESTS (for logged in users)
    // =====================================================
    
    function serviceGetMyRequestsBlock()
    {
        $this->_oTemplate->addCss(array('main.css'));
        
        
        if(!isLogged()) return 'Please log in to view your requests.';
        
        $aRequests = $this->_oDb->getServiceRequests(array('author_id' => getLoggedId()));
        
        if(empty($aRequests)) {
            return '<div class="wc-empty-state">
                <div class="wc-empty-icon">📝</div>
                <div class="wc-h3">You haven\'t submitted any requests yet.</div>
                <p>Submit your first service request to get help from your ward councillor.</p>
                <a href="' . BX_DOL_URL_ROOT . 'page.php?i=create-ward-request" class="wc-btn wc-btn-primary">Submit a Request</a>
            </div>';
        }
        
        $sContent = '';
        $aGroups = $this->_groupByMonth($aRequests, 'created');
        $sContent = $this->_renderGrouped($aGroups, array($this, '_renderRequestCard'));
        
        return '<div class="wc-my-requests">
            <div class="wc-my-requests-header">
                <div class="wc-h2">📋 My Requests</div>
                <a href="' . BX_DOL_URL_ROOT . 'page.php?i=create-ward-request" class="wc-btn wc-btn-primary">+ New Request</a>
            </div>
            <div class="wc-grouped-list">' . $sContent . '</div>
        </div>';
    }

    // =====================================================
    // MANAGE BLOCK - COUNCILOR BACK-OFFICE
    // =====================================================

    function serviceGetManageBlock()
    {
        $this->_oTemplate->addCss(array('main.css'));

        if(!isLogged())
            return MsgBox('Access Denied. This area is restricted to Ward Councillors and Leadership only.');

        if(!$this->_isCouncilor())
            return MsgBox('Access Denied. This area is restricted to Ward Councillors and Leadership only.');

        $iSpaceId = $this->_getCurrentSpaceId();
        $sTab = bx_get('manage_tab') ? bx_get('manage_tab') : 'requests';

        $sTabNav = '
        <div class="wc-status-filter" style="margin-bottom:24px;">
            <a href="page.php?i=ward-manage&manage_tab=requests" class="wc-tab' . ($sTab=='requests'?' active':'') . '">📋 All Requests</a>
            <a href="page.php?i=ward-manage&manage_tab=meetings" class="wc-tab' . ($sTab=='meetings'?' active':'') . '">📅 Meetings</a>
            <a href="page.php?i=ward-manage&manage_tab=announcements" class="wc-tab' . ($sTab=='announcements'?' active':'') . '">📢 Announcements</a>
            <a href="page.php?i=ward-manage&manage_tab=ward_info" class="wc-tab' . ($sTab=='ward_info'?' active':'') . '">🏘️ Ward Info</a>
        </div>';

        $sContent = '';
        switch($sTab) {
            case 'meetings':      $sContent = $this->_renderManageMeetings($iSpaceId); break;
            case 'announcements': $sContent = $this->_renderManageAnnouncements($iSpaceId); break;
            case 'ward_info':     $sContent = $this->_renderManageWardInfo($iSpaceId); break;
            default:              $sContent = $this->_renderManageRequests($iSpaceId);
        }

        return '<div class="wc-portal">
            <div class="wc-dashboard-header">
                <div class="wc-h1">⚙️ Councillor Management</div>
                <p>Back-office — manage requests, meetings and announcements</p>
            </div>
            ' . $sTabNav . $sContent . '
        </div>';
    }

    protected function _renderManageRequests($iSpaceId)
    {
        if(!$this->_isCouncilor())
            return MsgBox('Access Denied. This area is restricted to Ward Councillors and Leadership only.');

        $sStatusFilter = bx_get('status');
        $aParams = array();
        if($iSpaceId) $aParams['space_id'] = $iSpaceId;
        if($sStatusFilter) $aParams['status'] = $sStatusFilter;

        $aRequests = $this->_oDb->getServiceRequests($aParams);
        $aStats    = $this->_oDb->getStats($iSpaceId);

        $sTabs = '<a href="page.php?i=ward-manage&manage_tab=requests" class="wc-tab' . (!$sStatusFilter?' active':'') . '">All (' . $aStats['total_requests'] . ')</a>';
        foreach($this->_aRequestStatuses as $sKey => $sLabel) {
            $sCount = isset($aStats[$sKey.'_requests']) ? $aStats[$sKey.'_requests'] : 0;
            $sTabs .= '<a href="page.php?i=ward-manage&manage_tab=requests&status='.$sKey.'" class="wc-tab'.($sStatusFilter==$sKey?' active':'').'">' . $sLabel . ' (' . $sCount . ')</a>';
        }

        $sRows = '';
        $aGroups = $this->_groupByMonth($aRequests, 'created');
        foreach($aGroups as $sMonth => $aGroup) {
            $sRows .= '<tr><td colspan="8" class="wc-table-month-row">' . htmlspecialchars($sMonth) . ' <span class="wc-month-count">(' . count($aGroup) . ')</span></td></tr>';
            foreach($aGroup as $aR) {
                $sActions = '';
                switch($aR['status']) {
                    case 'pending':
                        $sActions = '<button type="button" class="wc-btn wc-btn-primary wc-btn-xs" onclick="wcModerateRequest('.$aR['id'].',\'approve\',this)">✓ Approve</button> ';
                        $sActions .= '<button type="button" class="wc-btn wc-btn-secondary wc-btn-xs" onclick="wcModerateRequest('.$aR['id'].',\'reject\',this)">✕ Reject</button>';
                        break;
                    case 'active':
                        $sActions = '<button type="button" class="wc-btn wc-btn-primary wc-btn-xs" onclick="wcModerateRequest('.$aR['id'].',\'in_progress\',this)">▶ In Progress</button> ';
                        $sActions .= '<button type="button" class="wc-btn wc-btn-secondary wc-btn-xs" onclick="wcModerateRequest('.$aR['id'].',\'resolved\',this)">✓ Resolved</button>';
                        break;
                    case 'in_progress':
                        $sActions = '<button type="button" class="wc-btn wc-btn-primary wc-btn-xs" onclick="wcModerateRequest('.$aR['id'].',\'resolved\',this)">✓ Resolved</button>';
                        break;
                    case 'resolved':
                    case 'closed':
                    case 'rejected':
                        $sActions = '<span class="wc-muted">—</span>';
                        break;
                }
                $sRows .= '<tr>';
                $sRows .= '<td><a href="' . BX_DOL_URL_ROOT . 'page.php?i=view-ward-request&id='.$aR['id'].'">' . htmlspecialchars($aR['reference_number']) . '</a></td>';
                $sRows .= '<td>' . htmlspecialchars(substr($aR['title'],0,50)) . '</td>';
                $sRows .= '<td>' . $this->_getCategoryLabel($aR['category']) . '</td>';
                $sRows .= '<td><span class="wc-status wc-status-'.$aR['status'].'">' . $this->_getStatusLabel($aR['status']) . '</span></td>';
                $sRows .= '<td>' . $this->_getPriorityLabel($aR['priority']) . '</td>';
                $sRows .= '<td>' . $this->_timeAgo($aR['created']) . '</td>';
                $sRows .= '<td class="wc-actions-cell">' . $sActions . '</td>';
                $sRows .= '</tr>';
            }
        }
        if(!$sRows) $sRows = '<tr><td colspan="8" style="text-align:center;padding:20px;">No requests found</td></tr>';

        $sJs  = '<script>';
        $sJs .= 'window.wcModerateRequest=function(id,action,btn){';
        $sJs .=   'btn.disabled=true;btn.textContent="...";';
        $sJs .=   "var url='" . BX_DOL_URL_ROOT . "modules/sa/ward_councilor/request.php?action=moderate_request&id='+id+'&mod_action='+action;";
        $sJs .=   'fetch(url,{credentials:"same-origin"})';
        $sJs .=     '.then(function(r){return r.json();})';
        $sJs .=     '.then(function(j){';
        $sJs .=       'if(j&&j.success){btn.closest("tr").style.opacity="0.5";btn.closest(".wc-actions-cell").innerHTML="<span class=\"wc-muted\">"+j.new_status+"</span>";}';
        $sJs .=       'else{alert("Error: "+(j&&j.error||"unknown"));btn.disabled=false;}';
        $sJs .=     '})';
        $sJs .=     '.catch(function(){alert("Network error");btn.disabled=false;});';
        $sJs .= '};';
        $sJs .= '</script>';

        return '<div class="wc-section">' .
            '<div class="wc-section-header">' .
                '<div class="wc-h2">📋 All Service Requests</div>' .
                '<a href="' . BX_DOL_URL_ROOT . 'page.php?i=create-ward-request" class="wc-btn wc-btn-primary" style="padding:6px 14px;font-size:13px;">+ New</a>' .
            '</div>' .
            '<div class="wc-status-filter">' . $sTabs . '</div>' .
            '<div style="overflow-x:auto;">' .
            '<table style="width:100%;border-collapse:collapse;font-size:14px;">' .
                '<thead><tr style="border-bottom:2px solid var(--wc-border);">' .
                    '<th style="padding:8px;text-align:left;">Ref</th>' .
                    '<th style="padding:8px;text-align:left;">Title</th>' .
                    '<th style="padding:8px;text-align:left;">Category</th>' .
                    '<th style="padding:8px;text-align:left;">Status</th>' .
                    '<th style="padding:8px;text-align:left;">Priority</th>' .
                    '<th style="padding:8px;text-align:left;">Submitted</th>' .
                    '<th style="padding:8px;text-align:left;">Action</th>' .
                '</tr></thead>' .
                '<tbody>' . $sRows . '</tbody>' .
            '</table>' .
            '</div>' .
        '</div>' . $sJs;
    }


    protected function _renderManageMeetings($iSpaceId)
    {
        $aMeetings = $this->_oDb->getMeetings($iSpaceId ? array('space_id' => $iSpaceId) : array());
        $sRows = '';
        $aGroups = $this->_groupByMonth($aMeetings, 'meeting_date');
        foreach($aGroups as $sMonth => $aGroup) {
            $sRows .= '<tr><td colspan="5" class="wc-table-month-row">' . htmlspecialchars($sMonth) . ' <span class="wc-month-count">(' . count($aGroup) . ')</span></td></tr>';
            foreach($aGroup as $aM) {
                $sRows .= '<tr>
                    <td>' . htmlspecialchars($aM['title']) . '</td>
                    <td>' . date('d M Y H:i', strtotime($aM['meeting_date'])) . '</td>
                    <td>' . htmlspecialchars($aM['location']) . '</td>
                    <td>' . ucfirst($aM['type']) . '</td>
                    <td>' . $aM['status'] . '</td>
                </tr>';
            }
        }
        if(!$sRows) $sRows = '<tr><td colspan="5" style="text-align:center;padding:20px;">No meetings scheduled</td></tr>';

        return '<div class="wc-section">
            <div class="wc-section-header">
                <div class="wc-h2">📅 Meetings</div>
                <a href="' . BX_DOL_URL_ROOT . 'page.php?i=create-ward-meeting" class="wc-btn wc-btn-primary" style="padding:6px 14px;font-size:13px;">+ Schedule</a>
            </div>
            <table style="width:100%;border-collapse:collapse;font-size:14px;">
                <thead><tr style="border-bottom:2px solid var(--wc-border);">
                    <th style="padding:8px;text-align:left;">Title</th><th style="padding:8px;text-align:left;">Date</th>
                    <th style="padding:8px;text-align:left;">Location</th><th style="padding:8px;text-align:left;">Type</th>
                    <th style="padding:8px;text-align:left;">Status</th>
                </tr></thead>
                <tbody>' . $sRows . '</tbody>
            </table>
        </div>';
    }

    protected function _renderManageAnnouncements($iSpaceId)
    {
        $aAnns = $this->_oDb->getAnnouncements($iSpaceId ? array('space_id' => $iSpaceId) : array());
        $sRows = '';
        $aGroups = $this->_groupByMonth($aAnns, 'created');
        foreach($aGroups as $sMonth => $aGroup) {
            $sRows .= '<tr><td colspan="5" class="wc-table-month-row">' . htmlspecialchars($sMonth) . ' <span class="wc-month-count">(' . count($aGroup) . ')</span></td></tr>';
            foreach($aGroup as $aA) {
                $sRows .= '<tr>
                    <td>' . ($aA['pinned']?'📌 ':'') . htmlspecialchars($aA['title']) . '</td>
                    <td>' . $aA['status'] . '</td>
                    <td>' . $this->_timeAgo($aA['created']) . '</td>
                    <td>' . (int)$aA['views'] . '</td>
                    <td><a href="' . BX_DOL_URL_ROOT . 'page.php?i=view-ward-announcement&id='.$aA['id'].'" class="wc-btn wc-btn-secondary" style="padding:4px 10px;font-size:12px;">View</a></td>
                </tr>';
            }
        }
        if(!$sRows) $sRows = '<tr><td colspan="5" style="text-align:center;padding:20px;">No announcements</td></tr>';

        return '<div class="wc-section">
            <div class="wc-section-header">
                <div class="wc-h2">📢 Announcements</div>
                <a href="' . BX_DOL_URL_ROOT . 'page.php?i=create-ward-announcement" class="wc-btn wc-btn-primary" style="padding:6px 14px;font-size:13px;">+ Post</a>
            </div>
            <table style="width:100%;border-collapse:collapse;font-size:14px;">
                <thead><tr style="border-bottom:2px solid var(--wc-border);">
                    <th style="padding:8px;text-align:left;">Title</th><th style="padding:8px;text-align:left;">Status</th>
                    <th style="padding:8px;text-align:left;">Posted</th><th style="padding:8px;text-align:left;">Views</th>
                    <th style="padding:8px;text-align:left;">Action</th>
                </tr></thead>
                <tbody>' . $sRows . '</tbody>
            </table>
        </div>';
    }

    protected function _renderManageWardInfo($iSpaceId)
    {
        $sMessage = '';
        $bEditMode = false;

        // Handle space selection from dropdown
        $iSelectedSpaceId = (int)bx_get('ward_space_id');
        if($iSelectedSpaceId > 0) {
            $iSpaceId = $iSelectedSpaceId;
        }

        // Handle edit mode toggle
        if(bx_get('edit_ward_info') === '1') {
            $bEditMode = true;
        }

        // Save handler
        if($_SERVER['REQUEST_METHOD'] === 'POST' && bx_get('ward_info_save')) {
            $iPostSpaceId = (int)bx_get('space_id');
            if($iPostSpaceId > 0) $iSpaceId = $iPostSpaceId;
            $iDropdownSpaceId = (int)bx_get('ward_space_id');
            if($iDropdownSpaceId > 0) $iSpaceId = $iDropdownSpaceId;

            $aData = array(
                'space_id'       => (int)$iSpaceId,
                'councillor_name'=> bx_get('councillor_name'),
                'ward_number'    => bx_get('ward_number'),
                'municipality'   => bx_get('municipality'),
                'province'       => bx_get('province'),
                'population'     => bx_get('population'),
                'description'    => bx_get('description'),
                'office_address' => bx_get('office_address'),
                'office_hours'   => bx_get('office_hours'),
                'contact_phone'  => bx_get('contact_phone'),
                'contact_email'  => bx_get('contact_email'),
            );
            if($this->_oDb->saveWardInfo($aData)) {
                $sMessage = '<div class="wc-success">✅ Ward info saved successfully!</div>';
                $bEditMode = false; // return to read-only after save
            } else {
                $sMessage = '<div class="wc-error">Error saving ward info.</div>';
                $bEditMode = true; // stay on edit if save failed
            }
        }

        // Load existing ward info for selected space
        $aWardInfo = $iSpaceId ? $this->_oDb->getWardInfo($iSpaceId) : array();
        $bHasRecord = !empty($aWardInfo);

        // Determine mode: show edit form if no record yet, or if edit requested, or if save failed
        $bShowEditForm = !$bHasRecord || $bEditMode;

        $v = function($key) use ($aWardInfo) {
            return htmlspecialchars(isset($aWardInfo[$key]) ? $aWardInfo[$key] : '');
        };

        // Build space selector dropdown — member spaces first, fall back to all active
        $iProfileId = (int)bx_get_logged_profile_id();
        $aSpaces = $this->_oDb->getMemberSpaces($iProfileId);
        if(empty($aSpaces)) {
            $aSpaces = $this->_oDb->getSpaces();
        }
        $sSpaceOptions = '<option value="">🌍 Select a ward/space...</option>';
        foreach($aSpaces as $aSpace) {
            $sSelected = ($iSpaceId == $aSpace['id']) ? ' selected' : '';
            $sSpaceOptions .= '<option value="' . $aSpace['id'] . '"' . $sSelected . '>🏠 ' . htmlspecialchars($aSpace['title']) . '</option>';
        }

        // Build form content
        $sFormContent = '';
        if($iSpaceId <= 0) {
            $sFormContent = '<p class="wc-empty">Select a ward/space above to manage its information.</p>';
        } elseif($bShowEditForm) {
            // EDITABLE FORM
            $sFormContent = '
                <div class="wc-form-row">
                    <div class="wc-form-group"><label>Ward Councillor</label><input type="text" name="councillor_name" value="' . $v('councillor_name') . '" placeholder="Councillor full name"></div>
                    <div class="wc-form-group"><label>Ward Number</label><input type="text" name="ward_number" value="' . $v('ward_number') . '"></div>
                </div>
                <div class="wc-form-row">
                    <div class="wc-form-group"><label>Municipality</label><input type="text" name="municipality" value="' . $v('municipality') . '"></div>
                    <div class="wc-form-group"><label>Province</label><input type="text" name="province" value="' . $v('province') . '"></div>
                </div>
                <div class="wc-form-row">
                    <div class="wc-form-group"><label>Population</label><input type="number" name="population" value="' . $v('population') . '"></div>
                    <div class="wc-form-group"><label>Office Hours</label><input type="text" name="office_hours" value="' . $v('office_hours') . '"></div>
                </div>
                <div class="wc-form-group"><label>Office Address</label><input type="text" name="office_address" value="' . $v('office_address') . '"></div>
                <div class="wc-form-row">
                    <div class="wc-form-group"><label>Contact Phone</label><input type="tel" name="contact_phone" value="' . $v('contact_phone') . '"></div>
                    <div class="wc-form-group"><label>Contact Email</label><input type="email" name="contact_email" value="' . $v('contact_email') . '"></div>
                </div>
                <div class="wc-form-group"><label>Ward Description</label><textarea name="description" rows="4">' . $v('description') . '</textarea></div>
                <button type="submit" class="wc-btn wc-btn-primary">💾 Save Ward Info</button>';
        } else {
            // READ-ONLY VIEW
            $sFormContent = '
                <div class="wc-view-grid">
                    <div class="wc-view-row">
                        <div class="wc-view-field"><span class="wc-view-label">Ward Councillor</span><span class="wc-view-sep">: </span><span class="wc-view-val">' . ($v('councillor_name') ?: '—') . '</span></div>
                        <div class="wc-view-field"><span class="wc-view-label">Ward Number</span><span class="wc-view-sep">: </span><span class="wc-view-val">' . ($v('ward_number') ?: '—') . '</span></div>
                    </div>
                    <div class="wc-view-row">
                        <div class="wc-view-field"><span class="wc-view-label">Municipality</span><span class="wc-view-sep">: </span><span class="wc-view-val">' . ($v('municipality') ?: '—') . '</span></div>
                        <div class="wc-view-field"><span class="wc-view-label">Province</span><span class="wc-view-sep">: </span><span class="wc-view-val">' . ($v('province') ?: '—') . '</span></div>
                    </div>
                    <div class="wc-view-row">
                        <div class="wc-view-field"><span class="wc-view-label">Population</span><span class="wc-view-sep">: </span><span class="wc-view-val">' . ($v('population') ? number_format((int)$aWardInfo['population']) : '—') . '</span></div>
                        <div class="wc-view-field"><span class="wc-view-label">Office Hours</span><span class="wc-view-sep">: </span><span class="wc-view-val">' . ($v('office_hours') ?: '—') . '</span></div>
                    </div>
                    <div class="wc-view-field"><span class="wc-view-label">Office Address</span><span class="wc-view-sep">: </span><span class="wc-view-val">' . ($v('office_address') ?: '—') . '</span></div>
                    <div class="wc-view-row">
                        <div class="wc-view-field"><span class="wc-view-label">Contact Phone</span><span class="wc-view-sep">: </span><span class="wc-view-val">' . ($v('contact_phone') ?: '—') . '</span></div>
                        <div class="wc-view-field"><span class="wc-view-label">Contact Email</span><span class="wc-view-sep">: </span><span class="wc-view-val">' . ($v('contact_email') ?: '—') . '</span></div>
                    </div>
                    <div class="wc-view-field"><span class="wc-view-label">Ward Description</span><span class="wc-view-sep">: </span><span class="wc-view-val wc-view-desc">' . ($v('description') ?: '—') . '</span></div>
                </div>
                <a href="page.php?i=ward-manage&manage_tab=ward_info&ward_space_id=' . (int)$iSpaceId . '&edit_ward_info=1" class="wc-btn wc-btn-secondary">✏️ Edit</a>';
        }

        return '<div class="wc-section">
            <div class="wc-section-header"><div class="wc-h2">🏘️ Ward Information</div></div>
            ' . $sMessage . '
            <form method="post" class="wc-form" id="ward-info-form">
                <input type="hidden" name="ward_info_save" value="1">
                <input type="hidden" name="space_id" value="' . (int)$iSpaceId . '">
                <div class="wc-form-group">
                    <label>Select Ward/Space</label>
                    <select name="ward_space_id" class="wc-form-control" onchange="window.location.href=\'page.php?i=ward-manage&manage_tab=ward_info&ward_space_id=\'+this.value">
                        ' . $sSpaceOptions . '
                    </select>
                </div>
            </form>
            <form method="post" class="wc-form">
                <input type="hidden" name="ward_info_save" value="1">
                <input type="hidden" name="space_id" value="' . (int)$iSpaceId . '">
                ' . $sFormContent . '
            </form>
        </div>';
    }

    // ─── Privacy Visibility Chooser ───────────────────────────────────────

    protected function _getVisibilityChooser($sObject, $iDefault = 3)
    {
        $aInput = @BxDolPrivacy::getGroupChooser($sObject, bx_get_logged_profile_id());
        if (empty($aInput))
            return $this->_getVisibilityChooserFallback($iDefault);

        // Render CSS/JS assets the privacy widget needs
        $sAssets = isset($aInput['content']) ? $aInput['content'] : '';

        // Build <select> from the returned input definition
        $sValue  = isset($aInput['value']) ? $aInput['value'] : $iDefault;
        $sName   = isset($aInput['name'])  ? $aInput['name']  : 'allow_view_to';
        $sOnChange = isset($aInput['attrs']['onchange']) ? ' onchange="' . htmlspecialchars($aInput['attrs']['onchange']) . '"' : '';
        $sClass  = 'sys-privacy-group form-control';

        $s = '<select name="' . $sName . '" class="' . $sClass . '"' . $sOnChange . '>';
        foreach ($aInput['values'] as $mKey => $mVal) {
            if (is_array($mVal)) {
                $iKey   = isset($mVal['key'])   ? $mVal['key']   : $mKey;
                $sLabel = isset($mVal['value']) ? $mVal['value'] : (isset($mVal['title']) ? $mVal['title'] : $iKey);
            } else {
                $iKey   = $mKey;
                $sLabel = $mVal;
            }
            if($iKey === '' || $iKey === null) continue;
            $bSelected = (string)$iKey === (string)$sValue;
            $s .= '<option value="' . htmlspecialchars($iKey) . '"' . ($bSelected ? ' selected' : '') . '>'
                . htmlspecialchars($sLabel) . '</option>';
        }
        $s .= '</select>';

        return $sAssets . $s;
    }

    protected function _getVisibilityChooserFallback($iDefault = 3)
    {
        $aGroups = array(
            BX_DOL_PG_ALL     => 'Everyone',
            BX_DOL_PG_MEMBERS => 'Members only',
            BX_DOL_PG_FRIENDS => 'Friends',
            BX_DOL_PG_MEONLY  => 'Only me',
        );
        $s = '<select name="allow_view_to" class="form-control">';
        foreach ($aGroups as $iVal => $sLabel)
            $s .= '<option value="' . $iVal . '"' . ($iVal == $iDefault ? ' selected' : '') . '>' . $sLabel . '</option>';
        return $s . '</select>';
    }

    // ─── Permission Gates ──────────────────────────────────────────────────

    public function checkAllowView($iEntryId)
    {
        // Layer 3: Space/Group context gate (Option A — no sys_objects_privacy)
        // ACL 'view entry' is enforced once the module is reinstalled with ACL rows.
        $aEntry = $this->_oDb->getServiceRequest($iEntryId);
        if (!empty($aEntry['space_id'])) {
            $oSpace = BxDolProfile::getInstance($aEntry['space_id']);
            if (!$oSpace || !$oSpace->isMember())
                return false;
        }

        return true;
    }

    public function checkAllowEdit($iEntryId)
    {
        $aEntry = $this->_oDb->getServiceRequest($iEntryId);
        $iProfileId = bx_get_logged_profile_id();

        // Councillor, Leadership, Moderator, Admin can edit any
        if (BxDolAcl::getInstance()->isMemberLevelInSet($this->_getModeratorLevelIds()))
            return true;

        if (BxDolAcl::getInstance()->isMemberLevelInSet('edit own entry')
            && (int)$aEntry['author_id'] === (int)$iProfileId)
            return true;

        return false;
    }

    public function checkAllowDelete($iEntryId)
    {
        $aEntry = $this->_oDb->getServiceRequest($iEntryId);
        $iProfileId = bx_get_logged_profile_id();

        if (BxDolAcl::getInstance()->isMemberLevelInSet($this->_getModeratorLevelIds()))
            return true;

        if (BxDolAcl::getInstance()->isMemberLevelInSet('delete own entry')
            && (int)$aEntry['author_id'] === (int)$iProfileId)
            return true;

        return false;
    }

    // ─── Notifications ────────────────────────────────────────────────────

    public function serviceGetNotificationsPost($aEvent)
    {
        $aEntry = $this->_oDb->getServiceRequest((int)$aEvent['object_id']);
        if(empty($aEntry)) return array();

        $sUrl = bx_absolute_url(
            BxDolPermalinks::getInstance()->permalink('page.php?i=view-ward-request&id=' . $aEntry['id']),
            '{bx_url_root}'
        );

        return array(
            'entry_sample'   => '_sa_ward_councilor_timeline_sample',
            'entry_url'      => $sUrl,
            'entry_caption'  => bx_process_output($aEntry['title']),
            'entry_summary'  => bx_process_output($aEntry['description'] ? $aEntry['description'] : ''),
            'entry_author'   => (int)$aEntry['author_id'],
            'entry_privacy'  => (int)($aEntry['allow_view_to'] ? $aEntry['allow_view_to'] : BX_DOL_PG_ALL),
        );
    }

    // ─── Timeline / Notifications Integration ─────────────────────────────

    public function serviceGetTimelineData()
    {
        $sModule = $this->_aModule['name'];
        return array(
            'handlers' => array(
                array('group' => $sModule . '_object', 'type' => 'insert', 'alert_unit' => $sModule, 'alert_action' => 'added',   'module_name' => $sModule, 'module_method' => 'get_timeline_post', 'module_class' => 'Module', 'groupable' => 0, 'group_by' => ''),
                array('group' => $sModule . '_object', 'type' => 'update', 'alert_unit' => $sModule, 'alert_action' => 'edited'),
                array('group' => $sModule . '_object', 'type' => 'delete', 'alert_unit' => $sModule, 'alert_action' => 'deleted'),
            ),
            'alerts' => array(
                array('unit' => $sModule, 'action' => 'added'),
                array('unit' => $sModule, 'action' => 'edited'),
                array('unit' => $sModule, 'action' => 'deleted'),
            ),
        );
    }

    public function serviceGetTimelinePost($aEvent, $aBrowseParams = array())
    {
        $aEntry = $this->_oDb->getServiceRequest((int)$aEvent['object_id']);
        if(!$aEntry) return false;

        $sUrl    = BX_DOL_URL_ROOT . 'page.php?i=view-ward-request&id=' . $aEntry['id'];
        $sTitle  = bx_process_output($aEntry['title']);
        $sDesc   = bx_process_output($aEntry['description'] ? $aEntry['description'] : '');
        $iAuthor = (int)$aEntry['author_id'];

        $oAuthor = BxDolProfile::getInstance($iAuthor);
        $sAuthorName = $oAuthor ? $oAuthor->getDisplayName() : '';
        $sActionDesc = ($sAuthorName ? $sAuthorName . ' ' : '') . 'submitted a ward service request';

        return array(
            'owner_id'          => $iAuthor,
            'object_owner_id'   => $iAuthor,
            'icon'              => 'landmark col-green3',
            'sample'            => '_sa_ward_councilor_timeline_sample',
            'sample_wo_article' => '_sa_ward_councilor_timeline_sample',
            'sample_action'     => '_sa_ward_councilor_timeline_sample',
            'title'             => $sTitle,
            'description'       => $sActionDesc,
            'url'               => $sUrl,
            'content'           => array(
                'url'   => $sUrl,
                'title' => $sTitle,
                'text'  => $sDesc,
            ),
            'date'     => strtotime($aEntry['created']),
            'privacy'  => (int)($aEntry['allow_view_to'] ? $aEntry['allow_view_to'] : BX_DOL_PG_ALL),
            'views'    => '',
            'votes'    => '',
            'reactions'=> '',
            'scores'   => '',
            'reports'  => '',
            'comments' => '',
        );
    }

    public function serviceGetContentInfoArray($iEntryId)
    {
        $aEntry = $this->_oDb->getServiceRequest((int)$iEntryId);
        if(!$aEntry) return false;

        return array(
            'id'          => (int)$aEntry['id'],
            'title'       => bx_process_output($aEntry['title']),
            'description' => bx_process_output($aEntry['description'] ? $aEntry['description'] : ''),
            'url'         => BX_DOL_URL_ROOT . 'page.php?i=view-ward-request&id=' . $iEntryId,
            'image'       => '',
            'author'      => (int)$aEntry['author_id'],
            'added'       => strtotime($aEntry['created']),
            'privacy'     => (int)($aEntry['allow_view_to'] ? $aEntry['allow_view_to'] : 3),
        );
    }

    // ──────────────────────────────────────────────────────────────────────

    // =========================================================
    // SPACE PAGE INLINE SUMMARY BLOCK
    // Renders compact ward stats + recent items directly on the
    // Space profile page. No navigation away required.
    // =========================================================

    public function serviceGetSpaceSummaryBlock()
    {
        $this->_oTemplate->addCss(array('main.css', 'nav.css'));

        $iSpaceId = (int)$this->_getCurrentSpaceId();
        if(!$iSpaceId) return '';

        $sRoot    = BX_DOL_URL_ROOT;
        $aStats   = $this->_oDb->getStats($iSpaceId);

        // Recent 3 requests
        $aRequests = $this->_oDb->getServiceRequests(array('space_id' => $iSpaceId, 'limit' => 3));
        $sRequests = '';
        foreach($aRequests as $r) {
            $sRequests .= '<div class="wc-sum-item">
                <span class="wc-status wc-status-' . $r['status'] . '">' . $this->_getStatusLabel($r['status']) . '</span>
                <a href="' . $sRoot . 'page.php?i=view-ward-request&id=' . $r['id'] . '&space_id=' . $iSpaceId . '">'  . htmlspecialchars(mb_strimwidth($r['title'], 0, 60, '…')) . '</a>
                <span class="wc-sum-meta">' . $this->_timeAgo($r['created']) . '</span>
            </div>';
        }

        // Next 1 meeting
        $aMeetings = $this->_oDb->getMeetings(array('space_id' => $iSpaceId, 'upcoming' => 1, 'limit' => 1));
        $sMeeting = '';
        if(!empty($aMeetings)) {
            $m = $aMeetings[0];
            $sMeeting = '<div class="wc-sum-meeting">
                <span class="wc-sum-meeting-date">' . date('d M', strtotime($m['meeting_date'])) . '</span>
                <a href="' . $sRoot . 'page.php?i=view-ward-meeting&id=' . $m['id'] . '&space_id=' . $iSpaceId . '">' . htmlspecialchars($m['title']) . '</a>
                <span class="wc-sum-meta">@ ' . htmlspecialchars($m['location']) . '</span>
            </div>';
        }

        // Latest announcement
        $aAnns = $this->_oDb->getAnnouncements(array('space_id' => $iSpaceId, 'status' => 'published', 'limit' => 1));
        $sAnn = '';
        if(!empty($aAnns)) {
            $a = $aAnns[0];
            $sAnn = '<div class="wc-sum-item">
                ' . ($a['pinned'] ? '<span class="wc-status" style="background:rgba(0,119,73,0.15);color:#6ee7b7;">Pinned</span>' : '') . '
                <a href="' . $sRoot . 'page.php?i=view-ward-announcement&id=' . $a['id'] . '&space_id=' . $iSpaceId . '">' . htmlspecialchars(mb_strimwidth($a['title'], 0, 60, '…')) . '</a>
                <span class="wc-sum-meta">' . $this->_timeAgo($a['created']) . '</span>
            </div>';
        }

        $sSubmitBtn = isLogged()
            ? '<a href="' . $sRoot . 'page.php?i=create-ward-request&space_id=' . $iSpaceId . '" class="wc-sum-action-btn">+ Submit Request</a>'
            : '';

        return '<div class="wc-space-summary">

            <div class="wc-sum-stats">
                <div class="wc-sum-stat">
                    <span class="wc-sum-stat-val">' . $aStats['total_requests'] . '</span>
                    <span class="wc-sum-stat-lbl">Requests</span>
                </div>
                <div class="wc-sum-stat wc-sum-stat-warn">
                    <span class="wc-sum-stat-val">' . $aStats['pending_requests'] . '</span>
                    <span class="wc-sum-stat-lbl">Pending</span>
                </div>
                <div class="wc-sum-stat wc-sum-stat-ok">
                    <span class="wc-sum-stat-val">' . $aStats['resolved_requests'] . '</span>
                    <span class="wc-sum-stat-lbl">Resolved</span>
                </div>
                <div class="wc-sum-stat">
                    <span class="wc-sum-stat-val">' . $aStats['upcoming_meetings'] . '</span>
                    <span class="wc-sum-stat-lbl">Meetings</span>
                </div>
            </div>

            ' . ($sRequests ? '
            <div class="wc-sum-section">
                <div class="wc-sum-section-hdr">
                    <span>Recent Requests</span>
                    <a href="' . $sRoot . 'page.php?i=ward-requests&space_id=' . $iSpaceId . '">View all &rsaquo;</a>
                </div>
                ' . $sRequests . '
            </div>' : '') . '

            ' . ($sMeeting ? '
            <div class="wc-sum-section">
                <div class="wc-sum-section-hdr">
                    <span>Next Meeting</span>
                    <a href="' . $sRoot . 'page.php?i=ward-meetings&space_id=' . $iSpaceId . '">All meetings &rsaquo;</a>
                </div>
                ' . $sMeeting . '
            </div>' : '') . '

            ' . ($sAnn ? '
            <div class="wc-sum-section">
                <div class="wc-sum-section-hdr">
                    <span>Latest Announcement</span>
                    <a href="' . $sRoot . 'page.php?i=ward-announcements&space_id=' . $iSpaceId . '">All &rsaquo;</a>
                </div>
                ' . $sAnn . '
            </div>' : '') . '

            ' . ($sSubmitBtn ? '<div class="wc-sum-actions">' . $sSubmitBtn . '</div>' : '') . '

        </div>';
    }

    // =========================================================
    // NAV STRIP + SIDEBAR — public service entry points
    // Called by sys_pages_blocks via the 'service' type.
    // =========================================================

    public function serviceGetWardNavStrip()
    {
        $iSpaceId = (int)$this->_getCurrentSpaceId();
        if(!$iSpaceId) $iSpaceId = $this->_resolveSpaceFromProfileIdentifier();
        return $this->_oTemplate->getWardNavStrip($iSpaceId);
    }

    public function serviceGetSidebarBlock()
    {
        $iSpaceId = (int)$this->_getCurrentSpaceId();
        if(!$iSpaceId) $iSpaceId = $this->_resolveSpaceFromProfileIdentifier();
        return $this->_oTemplate->getSidebarBlock($iSpaceId);
    }

    protected function _resolveSpaceFromProfileIdentifier()
    {
        $sUri = isset($_GET['profile_identifier']) ? trim($_GET['profile_identifier']) : '';
        if(!$sUri) return 0;
        $oDb = BxDolDb::getInstance();
        return (int)$oDb->getOne(
            $oDb->prepare(
                "SELECT p.id FROM sys_profiles p
                 JOIN bx_spaces_data d ON p.content_id = d.id
                 WHERE p.type = 'bx_spaces' AND p.uri = ? AND p.status = 'active'
                 LIMIT 1",
                $sUri
            )
        );
    }

    // ──────────────────────────────────────────────────────────────────────
}
