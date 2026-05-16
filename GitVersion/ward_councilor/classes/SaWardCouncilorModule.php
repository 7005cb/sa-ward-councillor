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
            
            <div class="wc-stats-grid">
                <div class="wc-stat-card">
                    <span class="wc-stat-icon">📋</span>
                    <div class="wc-stat-content">
                        <span class="wc-stat-value">' . $aStats['total_requests'] . '</span>
                        <span class="wc-stat-label">Total Requests</span>
                    </div>
                </div>
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
                </div>
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
        // Try to get space ID from context
        $iSpaceId = bx_get('space_id');
        if($iSpaceId) return (int)$iSpaceId;
        
        // Check if we're in a space context
        if(function_exists('bx_get_space_id')) {
            return bx_get_space_id();
        }
        
        return null;
    }

    protected function _isCouncilor()
    {
        if(!isLogged()) return false;

        // isMemberLevelInSet uses current context profile which may be a space/channel.
        // Get the person profile for this account and check its level directly.
        $iAccountId = getLoggedId(); // account ID
        $oDb = BxDolDb::getInstance();

        // Get the bx_persons profile for this account
        $iPersonProfileId = (int)$oDb->getOne(
            $oDb->prepare("SELECT `id` FROM `sys_profiles` WHERE `account_id`=? AND `type`='bx_persons' AND `status`='active' LIMIT 1", $iAccountId)
        );

        if($iPersonProfileId) {
            $iLevel = (int)$oDb->getOne(
                $oDb->prepare("SELECT `IDLevel` FROM `sys_acl_levels_members` WHERE `IDMember`=? LIMIT 1", $iPersonProfileId)
            );
            // Administrator(8), Moderator(7), Leadership(10), Councillor(12)
            if(in_array($iLevel, array(7, 8, 10, 12))) return true;
        }

        // Fallback: space admin
        if(!BxDolModuleQuery::getInstance()->isModuleInstalled('bx_spaces')) return false;

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
        
        // Status tabs
        $sStatusTabs = '<a href="page.php?i=ward-requests" class="wc-tab' . (!$sStatusFilter ? ' active' : '') . '">All (' . $aStats['total_requests'] . ')</a>';
        $sStatusTabs .= '<a href="page.php?i=ward-requests&status=pending" class="wc-tab' . ($sStatusFilter == 'pending' ? ' active' : '') . '">Pending (' . $aStats['pending_requests'] . ')</a>';
        $sStatusTabs .= '<a href="page.php?i=ward-requests&status=in_progress" class="wc-tab' . ($sStatusFilter == 'in_progress' ? ' active' : '') . '">In Progress (' . $aStats['in_progress_requests'] . ')</a>';
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
            foreach($aRequests as $aRequest) {
                $sContent .= $this->_renderRequestCard($aRequest);
            }
        }
        
        return '<div class="wc-requests">
            <div class="wc-page-header">
                <div class="wc-h1">📋 Service Requests</div>
                <a href="' . BX_DOL_URL_ROOT . 'page.php?i=create-ward-request" class="wc-btn wc-btn-primary">+ Submit Request</a>
            </div>
            
            <div class="wc-status-filter">' . $sStatusTabs . '</div>
            
            <div class="wc-search-box">
                <form method="get">
                    <input type="hidden" name="i" value="ward-requests">
                    ' . ($sStatusFilter ? '<input type="hidden" name="status" value="' . htmlspecialchars($sStatusFilter) . '">' : '') . '
                    <input type="text" name="search" placeholder="Search requests..." value="' . htmlspecialchars($sSearch ? $sSearch : '') . '">
                    <button type="submit">🔍</button>
                </form>
            </div>
            
            <div class="wc-requests-grid">' . $sContent . '</div>
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
                    return MsgBox(_t('_Access Denied'));
                $sNewStatus = bx_get('response_status');
                $sNoteText  = trim(bx_get('councilor_notes'));

                $this->_oDb->updateServiceRequest($iRequestId, array('status' => $sNewStatus));

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
            return MsgBox(_t('_sys_txt_login_required'));

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
            $iAllowViewTo = (int)bx_get('allow_view_to') ?: 2;
            
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
            foreach($aMeetings as $aMeeting) {
                $sContent .= $this->_renderMeetingCard($aMeeting);
            }
        }
        
        return '<div class="wc-meetings">
            <div class="wc-page-header">
                <div class="wc-h1">📅 Ward Meetings</div>
                ' . ($this->_isCouncilor() ? '<a href="' . BX_DOL_URL_ROOT . 'page.php?i=create-ward-meeting" class="wc-btn wc-btn-primary">+ Schedule Meeting</a>' : '') . '
            </div>
            
            <div class="wc-meetings-grid">' . $sContent . '</div>
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
            foreach($aAnnouncements as $aAnn) {
                $sContent .= $this->_renderAnnouncementCard($aAnn);
            }
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
            
            <div class="wc-announcements-list">' . $sContent . '</div>
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
        foreach($aRequests as $aRequest) {
            $sContent .= $this->_renderRequestCard($aRequest);
        }
        
        return '<div class="wc-my-requests">
            <div class="wc-my-requests-header">
                <div class="wc-h2">📋 My Requests</div>
                <a href="' . BX_DOL_URL_ROOT . 'page.php?i=create-ward-request" class="wc-btn wc-btn-primary">+ New Request</a>
            </div>
            <div class="wc-requests-grid">' . $sContent . '</div>
        </div>';
    }

    // =====================================================
    // MANAGE BLOCK - COUNCILOR BACK-OFFICE
    // =====================================================

    function serviceGetManageBlock()
    {
        $this->_oTemplate->addCss(array('main.css'));

        // DEBUG — remove after confirming access
        $iProfileId = bx_get_logged_profile_id();
        $iAccountId = getLoggedId();
        $oDbg = BxDolDb::getInstance();
        $iPersonPid = (int)$oDbg->getOne($oDbg->prepare("SELECT `id` FROM `sys_profiles` WHERE `account_id`=? AND `type`='bx_persons' AND `status`='active' LIMIT 1", $iAccountId));
        $iPersonLevel = $iPersonPid ? (int)$oDbg->getOne($oDbg->prepare("SELECT `IDLevel` FROM `sys_acl_levels_members` WHERE `IDMember`=? LIMIT 1", $iPersonPid)) : 0;
        $sDebug = '<div style="background:#1a1a2e;border:1px solid #f39c12;padding:10px;margin-bottom:12px;font-size:12px;font-family:monospace;color:#f39c12;">
            DEBUG — account_id=' . $iAccountId . ' | context_profile_id=' . $iProfileId . ' | person_profile_id=' . $iPersonPid . ' | person_level=' . $iPersonLevel . '<br>
            _isCouncilor()=' . ($this->_isCouncilor()?'TRUE':'FALSE') . '
        </div>';

        if(!$this->_isCouncilor())
            return $sDebug . MsgBox(_t('_Access Denied'));

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
        $sStatus = bx_get('status');
        $aParams = array();
        if($iSpaceId) $aParams['space_id'] = $iSpaceId;
        if($sStatus) $aParams['status'] = $sStatus;

        $aRequests = $this->_oDb->getServiceRequests($aParams);
        $aStats    = $this->_oDb->getStats($iSpaceId);

        $sTabs = '<a href="page.php?i=ward-manage&manage_tab=requests" class="wc-tab' . (!$sStatus?' active':'') . '">All (' . $aStats['total_requests'] . ')</a>';
        foreach($this->_aRequestStatuses as $sKey => $sLabel) {
            $sCount = isset($aStats[$sKey.'_requests']) ? $aStats[$sKey.'_requests'] : 0;
            $sTabs .= '<a href="page.php?i=ward-manage&manage_tab=requests&status='.$sKey.'" class="wc-tab'.($sStatus==$sKey?' active':'').'">' . $sLabel . ' (' . $sCount . ')</a>';
        }

        $sRows = '';
        foreach($aRequests as $aR) {
            $sRows .= '<tr>
                <td><a href="' . BX_DOL_URL_ROOT . 'page.php?i=view-ward-request&id='.$aR['id'].'">' . htmlspecialchars($aR['reference_number']) . '</a></td>
                <td>' . htmlspecialchars(substr($aR['title'],0,50)) . '</td>
                <td>' . $this->_getCategoryLabel($aR['category']) . '</td>
                <td><span class="wc-status wc-status-'.$aR['status'].'">' . $this->_getStatusLabel($aR['status']) . '</span></td>
                <td>' . $this->_getPriorityLabel($aR['priority']) . '</td>
                <td>' . $this->_timeAgo($aR['created']) . '</td>
                <td><a href="' . BX_DOL_URL_ROOT . 'page.php?i=view-ward-request&id='.$aR['id'].'" class="wc-btn wc-btn-secondary" style="padding:4px 10px;font-size:12px;">Manage</a></td>
            </tr>';
        }
        if(!$sRows) $sRows = '<tr><td colspan="7" style="text-align:center;padding:20px;">No requests found</td></tr>';

        return '<div class="wc-section">
            <div class="wc-section-header">
                <div class="wc-h2">📋 All Service Requests</div>
                <a href="' . BX_DOL_URL_ROOT . 'page.php?i=create-ward-request" class="wc-btn wc-btn-primary" style="padding:6px 14px;font-size:13px;">+ New</a>
            </div>
            <div class="wc-status-filter">' . $sTabs . '</div>
            <div style="overflow-x:auto;">
            <table style="width:100%;border-collapse:collapse;font-size:14px;">
                <thead><tr style="border-bottom:2px solid var(--wc-border);">
                    <th style="padding:8px;text-align:left;">Ref</th>
                    <th style="padding:8px;text-align:left;">Title</th>
                    <th style="padding:8px;text-align:left;">Category</th>
                    <th style="padding:8px;text-align:left;">Status</th>
                    <th style="padding:8px;text-align:left;">Priority</th>
                    <th style="padding:8px;text-align:left;">Submitted</th>
                    <th style="padding:8px;text-align:left;">Action</th>
                </tr></thead>
                <tbody>' . $sRows . '</tbody>
            </table>
            </div>
        </div>';
    }

    protected function _renderManageMeetings($iSpaceId)
    {
        $aMeetings = $this->_oDb->getMeetings($iSpaceId ? array('space_id' => $iSpaceId) : array());
        $sRows = '';
        foreach($aMeetings as $aM) {
            $sRows .= '<tr>
                <td>' . htmlspecialchars($aM['title']) . '</td>
                <td>' . date('d M Y H:i', strtotime($aM['meeting_date'])) . '</td>
                <td>' . htmlspecialchars($aM['location']) . '</td>
                <td>' . ucfirst($aM['type']) . '</td>
                <td>' . $aM['status'] . '</td>
            </tr>';
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
        foreach($aAnns as $aA) {
            $sRows .= '<tr>
                <td>' . ($aA['pinned']?'📌 ':'') . htmlspecialchars($aA['title']) . '</td>
                <td>' . $aA['status'] . '</td>
                <td>' . $this->_timeAgo($aA['created']) . '</td>
                <td>' . (int)$aA['views'] . '</td>
                <td><a href="' . BX_DOL_URL_ROOT . 'page.php?i=view-ward-announcement&id='.$aA['id'].'" class="wc-btn wc-btn-secondary" style="padding:4px 10px;font-size:12px;">View</a></td>
            </tr>';
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
        $aWardInfo = $iSpaceId ? $this->_oDb->getWardInfo($iSpaceId) : array();

        if($_SERVER['REQUEST_METHOD'] === 'POST' && bx_get('ward_info_save')) {
            $aData = array(
                'space_id'       => (int)$iSpaceId,
                'ward_number'    => bx_get('ward_number'),
                'municipality'   => bx_get('municipality'),
                'province'       => bx_get('province'),
                'description'    => bx_get('description'),
                'office_address' => bx_get('office_address'),
                'office_hours'   => bx_get('office_hours'),
                'contact_phone'  => bx_get('contact_phone'),
                'contact_email'  => bx_get('contact_email'),
            );
            if($this->_oDb->saveWardInfo($aData)) {
                $aWardInfo = $this->_oDb->getWardInfo($iSpaceId);
            } else {
                $sMessage = '<div class="wc-error">Error saving ward info.</div>';
            }
        }

        $v = function($key) use ($aWardInfo) {
            return htmlspecialchars(isset($aWardInfo[$key]) ? $aWardInfo[$key] : '');
        };

        return '<div class="wc-section">
            <div class="wc-section-header"><div class="wc-h2">🏘️ Ward Information</div></div>
            ' . $sMessage . '
            <form method="post" class="wc-form">
                <input type="hidden" name="ward_info_save" value="1">
                <div class="wc-form-row">
                    <div class="wc-form-group"><label>Ward Number</label><input type="text" name="ward_number" value="' . $v('ward_number') . '"></div>
                    <div class="wc-form-group"><label>Municipality</label><input type="text" name="municipality" value="' . $v('municipality') . '"></div>
                </div>
                <div class="wc-form-row">
                    <div class="wc-form-group"><label>Province</label><input type="text" name="province" value="' . $v('province') . '"></div>
                    <div class="wc-form-group"><label>Office Hours</label><input type="text" name="office_hours" value="' . $v('office_hours') . '"></div>
                </div>
                <div class="wc-form-group"><label>Office Address</label><input type="text" name="office_address" value="' . $v('office_address') . '"></div>
                <div class="wc-form-row">
                    <div class="wc-form-group"><label>Contact Phone</label><input type="tel" name="contact_phone" value="' . $v('contact_phone') . '"></div>
                    <div class="wc-form-group"><label>Contact Email</label><input type="email" name="contact_email" value="' . $v('contact_email') . '"></div>
                </div>
                <div class="wc-form-group"><label>Ward Description</label><textarea name="description" rows="4">' . $v('description') . '</textarea></div>
                <button type="submit" class="wc-btn wc-btn-primary">💾 Save Ward Info</button>
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
        if (BxDolAcl::getInstance()->isMemberLevelInSet(array(7, 8, 10, 12)))
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

        if (BxDolAcl::getInstance()->isMemberLevelInSet(array(7, 8, 10, 12)))
            return true;

        if (BxDolAcl::getInstance()->isMemberLevelInSet('delete own entry')
            && (int)$aEntry['author_id'] === (int)$iProfileId)
            return true;

        return false;
    }

    // ──────────────────────────────────────────────────────────────────────
}
