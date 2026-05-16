<?php defined('BX_DOL') or die('hack attempt');

bx_import('BxDolModule');

class SaSupportSchemeModule extends BxDolModule 
{
    protected $_sCurrency = 'ZAR';
    protected $_sCurrencySymbol = 'R';
    protected $_aCategories = array(
        'food' => 'Food & Nutrition',
        'medical' => 'Medical & Healthcare',
        'education' => 'Education',
        'housing' => 'Housing & Shelter',
        'emergency' => 'Emergency Relief',
        'utilities' => 'Utilities & Bills',
        'funeral' => 'Funeral Costs',
        'other' => 'Other'
    );

    function __construct(&$aModule) 
    {
        parent::__construct($aModule);
        $this->_sCurrency = getParam('sa_support_scheme_currency', 'ZAR');
        $this->_sCurrencySymbol = $this->_getCurrencySymbol($this->_sCurrency);
    }

    protected function _getCurrencySymbol($sCurrency) 
    {
        $aSymbols = array('ZAR' => 'R', 'USD' => '$', 'EUR' => '€', 'GBP' => '£');
        return isset($aSymbols[$sCurrency]) ? $aSymbols[$sCurrency] : $sCurrency;
    }

    protected function _formatCurrency($fAmount) 
    {
        return $this->_sCurrencySymbol . ' ' . number_format($fAmount, 2);
    }

    protected function _getCategoryLabel($sCategory) 
    {
        return isset($this->_aCategories[$sCategory]) ? $this->_aCategories[$sCategory] : $sCategory;
    }

    protected function _getDaysRemaining($sEndDate) 
    {
        if(empty($sEndDate) || $sEndDate == '0000-00-00 00:00:00') return null;
        $iEnd = strtotime($sEndDate);
        $iNow = time();
        if($iEnd < $iNow) return 0;
        return ceil(($iEnd - $iNow) / 86400);
    }

    function serviceGetMinDonation() 
    {
        return (int)getParam('sa_support_scheme_min_donation', 10);
    }

    /**
     * Main campaigns list block
     */
    function serviceGetCampaignsBlock()
    {
        $this->_oTemplate->addCss(array('main.css'));
        
        $aParams = array('status' => 'active');
        
        $sCategoryFilter = bx_get('category');
        if($sCategoryFilter) {
            $aParams['category'] = $sCategoryFilter;
        }
        
        $sSearch = bx_get('search');
        if($sSearch) {
            $aParams['search'] = $sSearch;
        }
        
        // Featured filter
        if(bx_get('featured')) {
            $aParams['featured'] = 1;
        }
        
        // Urgent filter
        if(bx_get('urgent')) {
            $aParams['urgent'] = 1;
        }
        
        $aCampaigns = $this->_oDb->getCampaigns($aParams);
        $aStats = $this->_oDb->getStats();
        $aCategories = $this->_oDb->getCategories();
        
        // Category tabs
        $sCategoryTabs = '<a href="?i=support-scheme-campaigns" class="sa-tab' . (!$sCategoryFilter ? ' active' : '') . '">All</a>';
        foreach($aCategories as $aCat) {
            $sActive = ($sCategoryFilter == $aCat['category']) ? ' active' : '';
            $sCategoryTabs .= '<a href="?i=support-scheme-campaigns&category=' . $aCat['category'] . '" class="sa-tab' . $sActive . '">' . $this->_getCategoryLabel($aCat['category']) . '</a>';
        }
        
        // Quick filter buttons
        $sQuickFilters = '<div class="sa-quick-filters">';
        $sQuickFilters .= '<a href="?i=support-scheme-campaigns" class="sa-filter-btn">All Campaigns</a>';
        $sQuickFilters .= '<a href="?i=support-scheme-campaigns&featured=1" class="sa-filter-btn">Featured</a>';
        $sQuickFilters .= '<a href="?i=support-scheme-campaigns&urgent=1" class="sa-filter-btn sa-filter-urgent">Urgent</a>';
        $sQuickFilters .= '</div>';
        
        $sContent = '';
        if(empty($aCampaigns)) {
            $sContent = '<div class="sa-empty-state">
                <div class="sa-empty-icon">🤝</div>
                <h3>No Campaigns Found</h3>
                <p>Be the first to create a campaign and help someone in need!</p>
                <a href="' . BX_DOL_URL_ROOT . 'page.php?i=create-support-scheme-campaign" class="sa-btn sa-btn-primary">Start a Campaign</a>
            </div>';
        } else {
            foreach($aCampaigns as $aCampaign) {
                $sContent .= $this->_renderCampaignCard($aCampaign);
            }
        }
        
        return '<div class="sa-support-scheme">
            <div class="sa-stats-banner">
                <div class="sa-stat">
                    <span class="sa-stat-value">' . (int)$aStats['active_campaigns'] . '</span>
                    <span class="sa-stat-label">Active Campaigns</span>
                </div>
                <div class="sa-stat">
                    <span class="sa-stat-value">' . $this->_formatCurrency($aStats['total_raised']) . '</span>
                    <span class="sa-stat-label">Total Raised</span>
                </div>
                <div class="sa-stat">
                    <span class="sa-stat-value">' . (int)$aStats['total_donations'] . '</span>
                    <span class="sa-stat-label">Donations</span>
                </div>
            </div>
            
            <div class="sa-campaigns-header">
                <h2>Community Support Campaigns</h2>
                <a href="' . BX_DOL_URL_ROOT . 'page.php?i=create-support-scheme-campaign" class="sa-btn sa-btn-primary">+ Create Campaign</a>
            </div>
            
            ' . $sQuickFilters . '
            
            <div class="sa-category-filter">' . $sCategoryTabs . '</div>
            
            <div class="sa-search-box">
                <form method="get">
                    <input type="hidden" name="i" value="support-scheme-campaigns">
                    <input type="text" name="search" placeholder="Search campaigns..." value="' . htmlspecialchars($sSearch ? $sSearch : '') . '">
                    <button type="submit">Search</button>
                </form>
            </div>
            
            <div class="sa-campaign-grid">' . $sContent . '</div>
        </div>';
    }

    /**
     * Render single campaign card
     */
    protected function _renderCampaignCard($aCampaign)
    {
        $fProgress = $aCampaign['goal_amount'] > 0 
            ? min(100, round(($aCampaign['current_amount'] / $aCampaign['goal_amount']) * 100, 1)) 
            : 0;
        $sUrl = BX_DOL_URL_ROOT . 'page.php?i=view-support-scheme-campaign&id=' . $aCampaign['id'];
        $sCategory = $this->_getCategoryLabel($aCampaign['category']);
        $sDesc = htmlspecialchars(substr($aCampaign['description'] ? $aCampaign['description'] : '', 0, 120));
        $iDaysLeft = $this->_getDaysRemaining($aCampaign['end_date']);
        
        // Build badges
        $sBadges = '';
        if(!empty($aCampaign['urgent'])) {
            $sBadges .= '<span class="sa-badge sa-badge-urgent">Urgent</span>';
        }
        if(!empty($aCampaign['featured'])) {
            $sBadges .= '<span class="sa-badge sa-badge-featured">Featured</span>';
        }
        if($iDaysLeft !== null && $iDaysLeft <= 7 && $iDaysLeft > 0) {
            $sBadges .= '<span class="sa-badge sa-badge-ending">' . $iDaysLeft . ' days left</span>';
        }
        if($iDaysLeft !== null && $iDaysLeft == 0) {
            $sBadges .= '<span class="sa-badge sa-badge-ended">Ended</span>';
        }
        
        return '<div class="sa-campaign-card' . (!empty($aCampaign['urgent']) ? ' sa-card-urgent' : '') . '">
            <div class="sa-campaign-header">
                <span class="sa-campaign-category">' . htmlspecialchars($sCategory) . '</span>
                ' . $sBadges . '
            </div>
            <div class="sa-campaign-body">
                <h3><a href="' . $sUrl . '">' . htmlspecialchars($aCampaign['title']) . '</a></h3>
                <p class="sa-campaign-desc">' . $sDesc . '</p>
                <div class="sa-progress-bar"><div style="width:' . $fProgress . '%"></div></div>
                <div class="sa-campaign-stats">
                    <div class="sa-stat-item">
                        <span class="sa-stat-main">' . $this->_formatCurrency($aCampaign['current_amount']) . '</span>
                        <span class="sa-stat-sub">raised of ' . $this->_formatCurrency($aCampaign['goal_amount']) . '</span>
                    </div>
                    <div class="sa-stat-item">
                        <span class="sa-stat-main">' . $fProgress . '%</span>
                        <span class="sa-stat-sub">funded</span>
                    </div>
                    <div class="sa-stat-item">
                        <span class="sa-stat-main">' . (int)$aCampaign['donations_count'] . '</span>
                        <span class="sa-stat-sub">donations</span>
                    </div>
                </div>
                <a href="' . $sUrl . '" class="sa-btn sa-btn-donate">Donate Now</a>
            </div>
            <div class="sa-campaign-footer">
                <span>' . (int)$aCampaign['views'] . ' views</span>
            </div>
        </div>';
    }

    /**
     * Campaign details block
     */
    function serviceGetCampaignDetailsBlock()
    {
        $this->_oTemplate->addCss(array('main.css'));
        
        $iCampaignId = (int)bx_get('id');
        if(!$iCampaignId) return 'Campaign not found';
        
        $aCampaign = $this->_oDb->getCampaign($iCampaignId);
        if(!$aCampaign) return 'Campaign not found';
        
        $this->_oDb->updateCampaignViews($iCampaignId);
        
        $fProgress = $aCampaign['goal_amount'] > 0 
            ? min(100, round(($aCampaign['current_amount'] / $aCampaign['goal_amount']) * 100, 1)) 
            : 0;
        $iDaysLeft = $this->_getDaysRemaining($aCampaign['end_date']);
        
        $aDonations = $this->_oDb->getDonations($iCampaignId, 10);
        $sDonations = '';
        foreach($aDonations as $aDonation) {
            $sDonorName = $aDonation['anonymous'] ? 'Anonymous' : 'Donor';
            $sDonations .= '<div class="sa-donation-item">
                <div class="sa-donation-header">
                    <strong>' . htmlspecialchars($sDonorName) . '</strong>
                    <span class="sa-donation-amount">' . $this->_formatCurrency($aDonation['amount']) . '</span>
                </div>
                ' . ($aDonation['message'] ? '<p class="sa-donation-message">"' . htmlspecialchars($aDonation['message']) . '"</p>' : '') . '
                <span class="sa-donation-date">' . date('d M Y', strtotime($aDonation['created'])) . '</span>
            </div>';
        }
        
        // Social share buttons
        $sShareUrl = BX_DOL_URL_ROOT . 'page.php?i=view-support-scheme-campaign&id=' . $aCampaign['id'];
        $sShareText = urlencode('Help support: ' . $aCampaign['title']);
        $sShareButtons = '<div class="sa-share-buttons">
            <a href="https://www.facebook.com/sharer/sharer.php?u=' . urlencode($sShareUrl) . '" target="_blank" class="sa-share-btn sa-share-facebook" title="Share on Facebook">FB</a>
            <a href="https://twitter.com/intent/tweet?url=' . urlencode($sShareUrl) . '&text=' . $sShareText . '" target="_blank" class="sa-share-btn sa-share-twitter" title="Share on Twitter">TW</a>
            <a href="https://api.whatsapp.com/send?text=' . $sShareText . '%20' . urlencode($sShareUrl) . '" target="_blank" class="sa-share-btn sa-share-whatsapp" title="Share on WhatsApp">WA</a>
        </div>';
        
        // Badges
        $sBadges = '';
        if(!empty($aCampaign['urgent'])) $sBadges .= '<span class="sa-badge sa-badge-urgent">Urgent</span>';
        if(!empty($aCampaign['featured'])) $sBadges .= '<span class="sa-badge sa-badge-featured">Featured</span>';
        
        return '<div class="sa-campaign-detail">
            <div class="sa-detail-header">
                <span class="sa-campaign-category">' . $this->_getCategoryLabel($aCampaign['category']) . '</span>
                ' . $sBadges . '
            </div>
            
            <h1>' . htmlspecialchars($aCampaign['title']) . '</h1>
            
            <div class="sa-detail-meta">
                <span>Created ' . date('d M Y', strtotime($aCampaign['created'])) . '</span>
                ' . ($iDaysLeft !== null ? '<span>' . ($iDaysLeft > 0 ? $iDaysLeft . ' days left' : 'Ended') . '</span>' : '') . '
                <span>' . (int)$aCampaign['views'] . ' views</span>
            </div>
            
            <div class="sa-detail-progress">
                <div class="sa-progress-bar sa-progress-large"><div style="width:' . $fProgress . '%"></div></div>
                <div class="sa-progress-labels">
                    <span class="sa-progress-current">' . $this->_formatCurrency($aCampaign['current_amount']) . '</span>
                    <span class="sa-progress-goal">of ' . $this->_formatCurrency($aCampaign['goal_amount']) . '</span>
                </div>
            </div>
            
            <div class="sa-detail-section">
                <h3>Campaign Story</h3>
                <p class="sa-description">' . nl2br(htmlspecialchars($aCampaign['description'] ? $aCampaign['description'] : '')) . '</p>
            </div>
            
            ' . (!empty($aCampaign['beneficiary_name']) ? '
            <div class="sa-detail-section">
                <h3>Beneficiary</h3>
                <p><strong>' . htmlspecialchars($aCampaign['beneficiary_name']) . '</strong></p>
            </div>
            ' : '') . '
            
            <div class="sa-detail-section">
                <h3>Recent Donations</h3>
                ' . ($sDonations ? $sDonations : '<p class="sa-empty-text">No donations yet. Be the first to donate!</p>') . '
            </div>
            
            <div class="sa-detail-section">
                <h3>Share This Campaign</h3>
                ' . $sShareButtons . '
            </div>
        </div>';
    }

    /**
     * Donation form block
     */
    function serviceGetDonationFormBlock()
    {
        $this->_oTemplate->addCss(array('main.css'));
        
        $iCampaignId = (int)bx_get('id');
        if(!$iCampaignId) return '';
        
        $aCampaign = $this->_oDb->getCampaign($iCampaignId);
        if(!$aCampaign) return '';
        
        $sMessage = '';
        
        if($_SERVER['REQUEST_METHOD'] === 'POST' && bx_get('amount')) {
            $fAmount = (float)bx_get('amount');
            $sMsg = bx_get('message') ? bx_get('message') : '';
            $bAnonymous = (int)bx_get('anonymous');
            
            if($fAmount >= $this->serviceGetMinDonation()) {
                $aData = array(
                    'campaign_id' => $iCampaignId,
                    'donor_id' => isLogged() ? getLoggedId() : null,
                    'amount' => $fAmount,
                    'message' => $sMsg,
                    'anonymous' => $bAnonymous,
                    'payment_status' => 'completed',
                    'created' => date('Y-m-d H:i:s')
                );
                
                if($this->_oDb->addDonation($aData)) {
                    $sMessage = '<div class="sa-success">Thank you for donating ' . $this->_formatCurrency($fAmount) . '!</div>';
                    $aCampaign = $this->_oDb->getCampaign($iCampaignId);
                }
            }
        }
        
        $fProgress = $aCampaign['goal_amount'] > 0 
            ? min(100, round(($aCampaign['current_amount'] / $aCampaign['goal_amount']) * 100, 1)) 
            : 0;
        
        // Quick amount buttons
        $sQuickAmounts = '';
        $aAmounts = array(50, 100, 250, 500);
        foreach($aAmounts as $iAmount) {
            $sQuickAmounts .= '<button type="button" class="sa-amount-btn" onclick="document.getElementById(\'donation-amount\').value=' . $iAmount . '">R' . $iAmount . '</button>';
        }
        
        return '<div class="sa-donation-sidebar">
            <div class="sa-donation-stats">
                <div class="sa-donation-amount">' . $this->_formatCurrency($aCampaign['current_amount']) . '</div>
                <div class="sa-donation-goal">raised of ' . $this->_formatCurrency($aCampaign['goal_amount']) . '</div>
                <div class="sa-progress-bar"><div style="width:' . $fProgress . '%"></div></div>
                <div class="sa-donation-meta">
                    <span>' . $fProgress . '% funded</span>
                    <span>' . (int)$aCampaign['donations_count'] . ' donations</span>
                </div>
            </div>
            ' . $sMessage . '
            <h3>Make a Donation</h3>
            <div class="sa-quick-amounts">' . $sQuickAmounts . '</div>
            <form method="post" class="sa-form">
                <div class="sa-form-group">
                    <label>Amount (R)</label>
                    <input type="number" name="amount" id="donation-amount" min="10" required>
                </div>
                <div class="sa-form-group">
                    <label>Message (Optional)</label>
                    <textarea name="message" rows="2" placeholder="Leave a message of support..."></textarea>
                </div>
                <div class="sa-form-group">
                    <label><input type="checkbox" name="anonymous" value="1"> Donate anonymously</label>
                </div>
                <button type="submit" class="sa-btn sa-btn-primary sa-btn-block">Donate Now</button>
            </form>
            <p class="sa-form-note">100% goes to the beneficiary</p>
        </div>';
    }

    /**
     * Create campaign block
     */
    function serviceGetCreateCampaignBlock()
    {
        $this->_oTemplate->addCss(array('main.css'));
        
        if(!isLogged()) {
            return '<div class="sa-error">Please <a href="' . BX_DOL_URL_ROOT . 'login.php">log in</a> to create a campaign.</div>';
        }
        
        $sMessage = '';
        
        if($_SERVER['REQUEST_METHOD'] === 'POST') {
            $sTitle = bx_get('title');
            $sDescription = bx_get('description');
            $fGoalAmount = (float)bx_get('goal_amount');
            $sCategory = bx_get('category');
            $sBeneficiaryName = bx_get('beneficiary_name');
            $sEndDate = bx_get('end_date');
            $iSpaceId = bx_get('space_id');
            $bUrgent = (int)bx_get('urgent');
            
            $aErrors = array();
            if(empty($sTitle)) $aErrors[] = 'Campaign title is required';
            if(empty($sDescription)) $aErrors[] = 'Campaign description is required';
            if($fGoalAmount < 100) $aErrors[] = 'Goal amount must be at least R100';
            if(empty($sCategory)) $aErrors[] = 'Please select a category';
            
            if(empty($aErrors)) {
                $aData = array(
                    'title' => $sTitle,
                    'description' => $sDescription,
                    'goal_amount' => $fGoalAmount,
                    'category' => $sCategory,
                    'beneficiary_name' => $sBeneficiaryName ? $sBeneficiaryName : '',
                    'end_date' => $sEndDate ? $sEndDate : null,
                    'author_id' => getLoggedId(),
                    'space_id' => $iSpaceId ? $iSpaceId : null,
                    'urgent' => $bUrgent,
                    'status' => 'active',
                    'created' => date('Y-m-d H:i:s')
                );
                
                $iCampaignId = $this->_oDb->addCampaign($aData);
                
                if($iCampaignId) {
                    header('Location: ' . BX_DOL_URL_ROOT . 'page.php?i=view-support-scheme-campaign&id=' . $iCampaignId);
                    exit;
                } else {
                    $sMessage = '<div class="sa-error">Error creating campaign. Please try again.</div>';
                }
            } else {
                $sMessage = '<div class="sa-error"><ul><li>' . implode('</li><li>', $aErrors) . '</li></ul></div>';
            }
        }
        
        $sCategoryOptions = '<option value="">Select a category...</option>';
        foreach($this->_aCategories as $sKey => $sLabel) {
            $sCategoryOptions .= '<option value="' . $sKey . '">' . $sLabel . '</option>';
        }
        
        // Space options
        $aSpaces = $this->_oDb->getSpaces();
        $sSpaceOptions = '<option value="">Global (Visible to Everyone)</option>';
        foreach($aSpaces as $aSpace) {
            $sSpaceOptions .= '<option value="' . $aSpace['id'] . '">' . htmlspecialchars($aSpace['title']) . '</option>';
        }
        
        return '<div class="sa-create-campaign">
            <div class="sa-create-header">
                <h2>Create a Campaign</h2>
                <p>Start a fundraising campaign to help someone in your community.</p>
            </div>
            
            ' . $sMessage . '
            
            <div class="sa-create-tips">
                <h4>Tips for Success</h4>
                <ul>
                    <li>Write a compelling, honest story</li>
                    <li>Be specific about how funds will be used</li>
                    <li>Set a realistic goal amount</li>
                    <li>Share your campaign on social media</li>
                </ul>
            </div>
            
            <form method="post" class="sa-form">
                <div class="sa-form-group">
                    <label>Campaign Title *</label>
                    <input type="text" name="title" placeholder="e.g., Help the Mokoena family rebuild after fire" required>
                </div>
                
                <div class="sa-form-row">
                    <div class="sa-form-group">
                        <label>Category *</label>
                        <select name="category" required>' . $sCategoryOptions . '</select>
                    </div>
                    <div class="sa-form-group">
                        <label>Goal Amount (R) *</label>
                        <input type="number" name="goal_amount" placeholder="10000" min="100" required>
                    </div>
                </div>
                
                <div class="sa-form-group">
                    <label>Description *</label>
                    <textarea name="description" rows="5" placeholder="Describe the situation and how the funds will be used..." required></textarea>
                </div>
                
                <div class="sa-form-row">
                    <div class="sa-form-group">
                        <label>Beneficiary Name</label>
                        <input type="text" name="beneficiary_name" placeholder="e.g., The Mokoena Family">
                    </div>
                    <div class="sa-form-group">
                        <label>End Date</label>
                        <input type="date" name="end_date" value="' . date('Y-m-d', strtotime('+30 days')) . '">
                    </div>
                </div>
                
                <div class="sa-form-group">
                    <label>Assign to Space</label>
                    <select name="space_id">' . $sSpaceOptions . '</select>
                    <p class="sa-hint">Global = visible to everyone. Select a space to limit visibility to that space.</p>
                </div>
                
                <div class="sa-form-group">
                    <label><input type="checkbox" name="urgent" value="1"> Mark as Urgent (for emergency situations)</label>
                </div>
                
                <button type="submit" class="sa-btn sa-btn-primary sa-btn-large">Create Campaign</button>
            </form>
        </div>';
    }

    /**
     * My campaigns block
     */
    function serviceGetMyCampaignsBlock()
    {
        $this->_oTemplate->addCss(array('main.css'));
        
        if(!isLogged()) return 'Please log in to view your campaigns.';
        
        $aCampaigns = $this->_oDb->getCampaigns(array('author_id' => getLoggedId()));
        
        if(empty($aCampaigns)) {
            return '<div class="sa-empty-state">
                <div class="sa-empty-icon">📝</div>
                <h3>You haven\'t created any campaigns yet.</h3>
                <p>Start making a difference in your community today!</p>
                <a href="' . BX_DOL_URL_ROOT . 'page.php?i=create-support-scheme-campaign" class="sa-btn sa-btn-primary">Create Your First Campaign</a>
            </div>';
        }
        
        $sContent = '';
        foreach($aCampaigns as $aCampaign) {
            $fProgress = $aCampaign['goal_amount'] > 0 
                ? min(100, round(($aCampaign['current_amount'] / $aCampaign['goal_amount']) * 100, 1)) 
                : 0;
            $sUrl = BX_DOL_URL_ROOT . 'page.php?i=view-support-scheme-campaign&id=' . $aCampaign['id'];
            
            $sStatusClass = 'sa-status-' . $aCampaign['status'];
            
            $sContent .= '<div class="sa-my-campaign-card">
                <div class="sa-my-campaign-header">
                    <span class="sa-status ' . $sStatusClass . '">' . ucfirst($aCampaign['status']) . '</span>
                </div>
                <h4><a href="' . $sUrl . '">' . htmlspecialchars($aCampaign['title']) . '</a></h4>
                <div class="sa-progress-bar"><div style="width:' . $fProgress . '%"></div></div>
                <div class="sa-my-campaign-stats">
                    <span>' . $this->_formatCurrency($aCampaign['current_amount']) . ' / ' . $this->_formatCurrency($aCampaign['goal_amount']) . '</span>
                    <span>' . $fProgress . '% funded</span>
                    <span>' . (int)$aCampaign['donations_count'] . ' donations</span>
                </div>
            </div>';
        }
        
        return '<div class="sa-my-campaigns">
            <div class="sa-my-campaigns-header">
                <h2>My Campaigns</h2>
                <a href="' . BX_DOL_URL_ROOT . 'page.php?i=create-support-scheme-campaign" class="sa-btn sa-btn-primary">+ New Campaign</a>
            </div>
            <div class="sa-my-campaigns-grid">' . $sContent . '</div>
        </div>';
    }
}
