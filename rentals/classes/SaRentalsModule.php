<?php
defined('BX_DOL') or die('hack attempt');

class SaRentalsModule extends BxDolModule
{
    function __construct(&$aModule)
    {
        parent::__construct($aModule);
    }

    // ── Lifecycle ──────────────────────────────────────────────────────────────

    public function onEnable()
    {
        parent::onEnable();
        // Register Timeline handlers
        if (BxDolModule::getInstance('bx_timeline')) {
            $oTimeline = BxDolModule::getInstance('bx_timeline');
            if ($oTimeline && method_exists($oTimeline, 'serviceAddHandlers'))
                $oTimeline->serviceAddHandlers($this->serviceGetTimelineData());
        }
        // Register Notifications handlers
        if (BxDolModule::getInstance('bx_notifications')) {
            $oNotifs = BxDolModule::getInstance('bx_notifications');
            if ($oNotifs && method_exists($oNotifs, 'serviceAddHandlers'))
                $oNotifs->serviceAddHandlers($this->serviceGetNotificationsData());
        }
        return true;
    }

    public function onDisable()
    {
        parent::onDisable();
        // Deregister Timeline handlers
        if (BxDolModule::getInstance('bx_timeline')) {
            $oTimeline = BxDolModule::getInstance('bx_timeline');
            if ($oTimeline && method_exists($oTimeline, 'serviceRemoveHandlers'))
                $oTimeline->serviceRemoveHandlers($this->serviceGetTimelineData());
        }
        // Deregister Notifications handlers
        if (BxDolModule::getInstance('bx_notifications')) {
            $oNotifs = BxDolModule::getInstance('bx_notifications');
            if ($oNotifs && method_exists($oNotifs, 'serviceRemoveHandlers'))
                $oNotifs->serviceRemoveHandlers($this->serviceGetNotificationsData());
        }
        return true;
    }

    // ── Listings page ──────────────────────────────────────────────────────────
    function serviceGetListingsBlock()
    {
        $this->_oTemplate->addCss(array('main.css'));
        $iUserId = bx_get_logged_profile_id();

        // Filter
        $sProvince  = bx_process_input(bx_get('province'),      BX_DATA_TEXT);
        $sType      = bx_process_input(bx_get('property_type'), BX_DATA_TEXT);
        $iMinRent   = (int)bx_get('min_rent');
        $iMaxRent   = (int)bx_get('max_rent');
        $iBedrooms  = (int)bx_get('bedrooms');
        $sStatus    = bx_process_input(bx_get('status'), BX_DATA_TEXT);

        $aFilter = array();
        if ($sProvince)         $aFilter['province']      = $sProvince;
        if ($sType)             $aFilter['property_type'] = $sType;
        if ($iMinRent > 0)      $aFilter['min_rent']      = $iMinRent;
        if ($iMaxRent > 0)      $aFilter['max_rent']      = $iMaxRent;
        if ($iBedrooms > 0)     $aFilter['bedrooms']      = $iBedrooms;
        if ($sStatus)           $aFilter['status']        = $sStatus;

        // Visibility filter — show public + spaces/groups user belongs to
        $aFilter['viewer_id'] = $iUserId;

        $sFilter = '<form class="sar-filter" method="get">
            <select name="property_type">
                <option value="">' . _t('_sa_rentals_all_types') . '</option>
                <option value="room"' . ($sType==='room'?' selected':'') . '>' . _t('_sa_rentals_type_room') . '</option>
                <option value="house"' . ($sType==='house'?' selected':'') . '>' . _t('_sa_rentals_type_house') . '</option>
                <option value="flat"' . ($sType==='flat'?' selected':'') . '>' . _t('_sa_rentals_type_flat') . '</option>
                <option value="backyard"' . ($sType==='backyard'?' selected':'') . '>' . _t('_sa_rentals_type_backyard') . '</option>
                <option value="townhouse"' . ($sType==='townhouse'?' selected':'') . '>' . _t('_sa_rentals_type_townhouse') . '</option>
            </select>
            <select name="status">
                <option value="">' . _t('_sa_rentals_all_statuses') . '</option>
                <option value="available"' . ($sStatus==='available'?' selected':'') . '>' . _t('_sa_rentals_status_available') . '</option>
                <option value="hold"' . ($sStatus==='hold'?' selected':'') . '>' . _t('_sa_rentals_status_hold') . '</option>
                <option value="booked"' . ($sStatus==='booked'?' selected':'') . '>' . _t('_sa_rentals_status_booked') . '</option>
                <option value="taken"' . ($sStatus==='taken'?' selected':'') . '>' . _t('_sa_rentals_status_taken') . '</option>
            </select>
            <input type="number" name="min_rent" placeholder="' . _t('_sa_rentals_min_rent') . '" value="' . ($iMinRent ?: '') . '">
            <input type="number" name="max_rent" placeholder="' . _t('_sa_rentals_max_rent') . '" value="' . ($iMaxRent ?: '') . '">
            <input type="number" name="bedrooms" placeholder="' . _t('_sa_rentals_bedrooms') . '" value="' . ($iBedrooms ?: '') . '" min="0" max="10">
            <button type="submit">' . _t('_sa_rentals_filter') . '</button>
            <a href="page.php?i=rentals-listings">' . _t('_sa_rentals_clear') . '</a>
        </form>';

        $sCta = '';
        if ($iUserId)
            $sCta = '<a href="' . BX_DOL_URL_ROOT . 'page.php?i=create-rentals-listing" class="sar-btn-create">' . _t('_sa_rentals_post_listing') . '</a>';

        $aListings = $this->_oDb->getListings($aFilter);
        $sContent = '';
        if (empty($aListings)) {
            $sContent = '<p class="sar-empty">' . _t('_sa_rentals_no_listings') . '</p>';
        } else {
            $sContent = '<div class="sar-grid">';
            foreach ($aListings as $aItem) $sContent .= $this->_renderCard($aItem);
            $sContent .= '</div>';
        }
        return '<div class="sar-wrap">' . $sFilter . $sCta . $sContent . '</div>';
    }

    // ── Detail page ────────────────────────────────────────────────────────────
    function serviceGetListingDetailBlock()
    {
        $this->_oTemplate->addCss(array('main.css'));
        $iUserId = bx_get_logged_profile_id();
        $iId = (int)bx_get('id');
        if (!$iId) return '<p>' . _t('_sa_rentals_not_found') . '</p>';
        $a = $this->_oDb->getListing($iId);
        if (!$a) return '<p>' . _t('_sa_rentals_not_found') . '</p>';
        if (!$this->_oDb->canViewListing($a, $iUserId))
            return '<p>' . _t('_sa_rentals_no_permission') . '</p>';

        $this->_oDb->incrementViews($iId);

        // Auto-expire if past expires_at
        $this->_checkExpiry($a);

        // Media gallery
        $sGallery = '';
        if (!empty($a['media_storage_ids'])) {
            $oStorage = BxDolStorage::getObjectInstance('sa_rentals_files');
            if ($oStorage) {
                $sGallery = '<div class="sar-gallery">';
                foreach (explode(',', $a['media_storage_ids']) as $sFileId) {
                    $sFileId = trim($sFileId);
                    if (!$sFileId) continue;
                    $aFile = $oStorage->getFile((int)$sFileId);
                    if (!$aFile) continue;
                    $sUrl = $oStorage->getFileUrlById((int)$sFileId);
                    $sMime = $aFile['mime_type'];
                    if (strpos($sMime, 'video') !== false)
                        $sGallery .= '<video src="' . bx_html_attribute($sUrl) . '" controls></video>';
                    else
                        $sGallery .= '<img src="' . bx_html_attribute($sUrl) . '" alt="' . bx_html_attribute($a['title']) . '">';
                }
                $sGallery .= '</div>';
            }
        }

        // Specs
        $aSpecItems = array();
        if ($a['bedrooms'])            $aSpecItems[] = array('Bedrooms',    $a['bedrooms']);
        if ($a['bathrooms'])           $aSpecItems[] = array('Bathrooms',   $a['bathrooms']);
        if ($a['lease_term'])          $aSpecItems[] = array('Lease',       $a['lease_term']);
        if ($a['deposit_zar'] > 0)     $aSpecItems[] = array('Deposit',     'R ' . number_format((float)$a['deposit_zar'], 2));
        if (!empty($a['available_from'])) $aSpecItems[] = array('Available', date('d M Y', strtotime($a['available_from'])));
        $aSpecItems[] = array('Furnished',   ucfirst(str_replace('-', ' ', $a['furnished'])));
        $aSpecItems[] = array('Parking',     $a['parking'] ? 'Yes' : 'No');
        $aSpecItems[] = array('Pets',        $a['pets_allowed'] ? 'Allowed' : 'No pets');
        $aSpecItems[] = array('Utilities',   $a['utilities_included'] ? 'Included' : 'Not included');
        $aSpecItems[] = array('Electricity', $a['prepaid_electricity'] ? 'Prepaid meter' : 'Conventional');
        if ($a['wifi_available'])      $aSpecItems[] = array('WiFi', 'Available');

        $sSpecs = '<div class="sar-specs">';
        foreach ($aSpecItems as $aS)
            $sSpecs .= '<div class="sar-spec-item"><strong>' . htmlspecialchars($aS[0]) . '</strong>' . htmlspecialchars($aS[1]) . '</div>';
        $sSpecs .= '</div>';

        // Status badge
        $aStatusColors = array('available'=>'green','hold'=>'orange','booked'=>'blue','taken'=>'red');
        $sStatusColor = isset($aStatusColors[$a['status']]) ? $aStatusColors[$a['status']] : 'green';
        $sStatusBadge = '<span class="sar-status sar-status-' . $sStatusColor . '">' . _t('_sa_rentals_status_' . $a['status']) . '</span>';

        // View count
        $sViews = '<span class="sar-views">&#128065; ' . (int)($a['views_count'] + 1) . ' ' . _t('_sa_rentals_views') . '</span>';

        // Owner controls
        $sOwnerControls = '';
        if ($iUserId && (int)$a['author_id'] === $iUserId) {
            $sOwnerControls = '<div class="sar-owner-controls">
                <a href="' . BX_DOL_URL_ROOT . 'rentals-edit?id=' . $iId . '" class="sar-btn-edit">&#9998; ' . _t('_sa_rentals_edit') . '</a>
                <a href="' . BX_DOL_URL_ROOT . 'page.php?i=my-rentals-listings" class="sar-btn-my">&#8592; ' . _t('_sa_rentals_my_listings') . '</a>
            </div>';
        }

        // Contact block + direct message
        $sContact = '<div class="sar-contact"><h3>' . _t('_sa_rentals_contact_landlord') . '</h3>';
        if (!empty($a['contact_phone']))    $sContact .= '<p>&#128222; <a href="tel:' . bx_html_attribute($a['contact_phone']) . '">' . htmlspecialchars($a['contact_phone']) . '</a></p>';
        // WhatsApp button — controlled by sys_options toggle
        if (!empty($a['contact_whatsapp']) && getParam('sa_rentals_whatsapp_enabled')) {
            $sWaNumber = preg_replace('/[^0-9]/', '', $a['contact_whatsapp']);
            $sContact .= '<p><a href="https://wa.me/' . $sWaNumber . '" target="_blank" rel="noopener" class="sar-btn-whatsapp">&#128241; ' . _t('_sa_rentals_btn_whatsapp') . '</a></p>';
        } elseif (!empty($a['contact_whatsapp'])) {
            $sContact .= '<p>&#128241; ' . htmlspecialchars($a['contact_whatsapp']) . '</p>';
        }
        if (!empty($a['contact_email']))    $sContact .= '<p>&#9993; <a href="mailto:' . bx_html_attribute($a['contact_email']) . '">' . htmlspecialchars($a['contact_email']) . '</a></p>';
        if (!empty($a['contact']))          $sContact .= '<p>' . htmlspecialchars($a['contact']) . '</p>';
        // Direct message button — only show if viewer is logged in and not the owner
        if ($iUserId && (int)$a['author_id'] !== $iUserId) {
            $sContact .= '<a href="' . BX_DOL_URL_ROOT . 'page.php?i=create-conversation&profile_id=' . (int)$a['author_id'] . '" class="sar-btn-msg">&#128172; ' . _t('_sa_rentals_message_landlord') . '</a>';
        }
        $sContact .= '</div>';

        $sFeatured  = $a['featured'] ? '<span class="sar-featured">&#11088; Featured</span>' : '';
        $sVerified  = (!empty($a['verified']) && getParam('sa_rentals_verified_badge'))
            ? '<span class="sar-badge-verified">&#10003; ' . _t('_sa_rentals_badge_verified') . '</span>' : '';
        $sGroupTag  = ($a['group_id'] > 0) ? '<span class="sar-group-tag">&#128101; Community Listing</span>' : '';
        $sSpaceTag  = ($a['space_id'] > 0) ? '<span class="sar-space-tag">&#127968; Space Listing</span>' : '';

        // Expiry notice for the listing owner
        $sExpiryNotice = '';
        if ($iUserId && (int)$a['author_id'] === $iUserId && !empty($a['expires_at'])) {
            $iDaysLeft = (int)ceil((strtotime($a['expires_at']) - time()) / 86400);
            $iReminderDays = (int)getParam('sa_rentals_expiry_reminder_days');
            if ($iDaysLeft <= 0)
                $sExpiryNotice = '<div class="sar-error">' . _t('_sa_rentals_msg_listing_expired') . '</div>';
            elseif ($iReminderDays > 0 && $iDaysLeft <= $iReminderDays)
                $sExpiryNotice = '<div class="sar-notice">&#9888; ' . _t('_sa_rentals_expires_label') . ': ' . htmlspecialchars($a['expires_at']) . ' (' . $iDaysLeft . ' days)</div>';
        }

        return '<div class="sar-detail">' .
            $sGallery .
            $sExpiryNotice .
            $sOwnerControls .
            '<h1>' . htmlspecialchars($a['title']) . $sFeatured . $sVerified . '</h1>' .
            $sGroupTag . $sSpaceTag .
            '<div class="sar-meta">
                <span class="sar-card-badge">' . htmlspecialchars($a['property_type']) . '</span>
                <span class="sar-price">R ' . number_format((float)$a['rent_zar'], 2) . ' /month</span>
                ' . $sStatusBadge . '
                ' . $sViews . '
            </div>' .
            '<div class="sar-location">&#128205; ' . implode(', ', array_filter(array(htmlspecialchars($a['address']), htmlspecialchars($a['city']), htmlspecialchars($a['province'])))) . '</div>' .
            $sSpecs .
            '<div class="sar-desc">' . nl2br(htmlspecialchars($a['description'])) . '</div>' .
            $sContact .
            '<a href="' . BX_DOL_URL_ROOT . 'page.php?i=rentals-listings" class="sar-back">' . _t('_sa_rentals_back') . '</a>' .
            '</div>';
    }

    // ── Create listing ─────────────────────────────────────────────────────────
    function serviceGetCreateListingBlock()
    {
        $this->_oTemplate->addCss(array('main.css'));
        $iUserId = bx_get_logged_profile_id();
        if (!$iUserId) return '<p>' . _t('_sa_rentals_login_required') . '</p>';

        // Require Landlord or Estate Agent level to post
        if (!$this->_isAllowed('create entry'))
            return '<div class="sar-form-wrap"><div class="sar-error">' . _t('_sa_rentals_msg_upgrade_required') . '</div></div>';

        // Quota check — block if over limit for their level
        if (!$this->_checkListingQuota($iUserId))
            return '<div class="sar-form-wrap"><div class="sar-error">' . _t('_sa_rentals_msg_quota_reached') . '</div></div>';

        $sMsg = '';
        $aValues = array();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $aData = $this->_collectFormData($iUserId);
            $aValues = $aData;
            if (empty($aData['title']) || empty($aData['rent_zar'])) {
                $sMsg = '<div class="sar-error">' . _t('_sa_rentals_error_required') . '</div>';
            } else {
                // Moderation toggle: if enabled, new listings start as 'pending'
                if (getParam('sa_rentals_moderation') == 'on')
                    $aData['status'] = 'pending';
                // Set expiry date from Studio setting (0 = never)
                $iExpiryDays = (int)getParam('sa_rentals_expiry_days');
                if ($iExpiryDays > 0)
                    $aData['expires_at'] = date('Y-m-d', strtotime('+' . $iExpiryDays . ' days'));
                $bResult = $this->_oDb->addListing($aData);
                $iListingId = $this->_oDb->getLastInsertId();
                if ($bResult && $iListingId > 0) {
                    $this->_handleMediaUpload($iListingId, $iUserId);
                    $iOwnerId = !empty($aData['space_id']) ? (int)$aData['space_id'] : $iUserId;
                    $oAlert = new BxDolAlerts('sa_rentals', 'added', $iListingId, $iUserId, array('owner_id' => $iOwnerId));
                    $oAlert->alert();
                    // If listing is pending, stay on page with a message instead of redirecting
                    if (!empty($aData['status']) && $aData['status'] === 'pending') {
                        return '<div class="sar-form-wrap"><div class="sar-success">' . _t('_sa_rentals_pending_submitted') . '</div></div>';
                    }
                    header('Location: ' . BX_DOL_URL_ROOT . 'page.php?i=view-rentals-listing&id=' . $iListingId);
                    exit;
                }

                $sMsg = '<div class="sar-error">The listing could not be saved. Please try again.</div>';
            }
        }

        return '<div class="sar-form-wrap">' . $sMsg . $this->_renderForm($aValues) . '</div>';
    }

    // ── Edit listing ───────────────────────────────────────────────────────────
    function serviceGetEditListingBlock()
    {
        $this->_oTemplate->addCss(array('main.css'));
        $iUserId = bx_get_logged_profile_id();
        if (!$iUserId) return '<p>' . _t('_sa_rentals_login_required') . '</p>';

        $iId = (int)bx_get('id');
        if (!$iId) return '<p>' . _t('_sa_rentals_not_found') . '</p>';

        $a = $this->_oDb->getListing($iId);
        if (!$a) return '<p>' . _t('_sa_rentals_not_found') . '</p>';
        if ((int)$a['author_id'] !== $iUserId) return '<p>' . _t('_sa_rentals_no_permission') . '</p>';

        $sMsg = '';
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Handle delete
            if (!empty($_POST['delete_listing'])) {
                $this->_oDb->deleteListing($iId);
                $oAlert = new BxDolAlerts('sa_rentals', 'deleted', $iId, $iUserId);
                $oAlert->alert();
                header('Location: ' . BX_DOL_URL_ROOT . 'page.php?i=my-rentals-listings');
                exit;
            }
            $aData = $this->_collectFormData($iUserId);
            if (empty($aData['title']) || empty($aData['rent_zar'])) {
                $sMsg = '<div class="sar-error">' . _t('_sa_rentals_error_required') . '</div>';
            } else {
                $this->_oDb->updateListing($iId, $aData);
                $this->_handleMediaUpload($iId, $iUserId);
                $iOwnerId = !empty($aData['space_id']) ? (int)$aData['space_id'] : $iUserId;
                $oAlert = new BxDolAlerts('sa_rentals', 'edited', $iId, $iUserId, array('owner_id' => $iOwnerId));
                $oAlert->alert();
                header('Location: ' . BX_DOL_URL_ROOT . 'page.php?i=view-rentals-listing&id=' . $iId);
                exit;
            }
            $a = array_merge($a, $aData);
        }

        return '<div class="sar-form-wrap">' . $sMsg . $this->_renderForm($a, $iId) . '</div>';
    }

    // ── My listings ────────────────────────────────────────────────────────────
    function serviceGetMyListingsBlock()
    {
        $this->_oTemplate->addCss(array('main.css'));
        $iUserId = bx_get_logged_profile_id();
        if (!$iUserId) return '<p>' . _t('_sa_rentals_login_required') . '</p>';

        $aListings = $this->_oDb->getListingsByAuthor($iUserId);
        if (empty($aListings))
            return '<p class="sar-empty">' . _t('_sa_rentals_no_my_listings') . '</p>
                    <a href="' . BX_DOL_URL_ROOT . 'page.php?i=create-rentals-listing" class="sar-btn-create">' . _t('_sa_rentals_post_listing') . '</a>';

        $aStatusColors = array('available'=>'green','hold'=>'orange','booked'=>'blue','taken'=>'red','pending'=>'grey');
        $sOut = '<div class="sar-my-listings">';
        $sOut .= '<a href="' . BX_DOL_URL_ROOT . 'page.php?i=create-rentals-listing" class="sar-btn-create">' . _t('_sa_rentals_post_listing') . '</a>';
        foreach ($aListings as $a) {
            $sColor = isset($aStatusColors[$a['status']]) ? $aStatusColors[$a['status']] : 'green';
            $sOut .= '<div class="sar-my-item">
                <div class="sar-my-info">
                    <strong>' . htmlspecialchars($a['title']) . '</strong>
                    <span class="sar-status sar-status-' . $sColor . '">' . _t('_sa_rentals_status_' . $a['status']) . '</span>
                    <span class="sar-views">&#128065; ' . (int)$a['views_count'] . ' ' . _t('_sa_rentals_views') . '</span>
                    <span class="sar-price">R ' . number_format((float)$a['rent_zar'], 2) . '</span>
                </div>
                <div class="sar-my-actions">
                    <a href="' . BX_DOL_URL_ROOT . 'page.php?i=view-rentals-listing&id=' . $a['id'] . '">' . _t('_sa_rentals_view') . '</a>
                    <a href="' . BX_DOL_URL_ROOT . 'rentals-edit?id=' . $a['id'] . '">' . _t('_sa_rentals_edit') . '</a>
                </div>
            </div>';
        }
        $sOut .= '</div>';
        return $sOut;
    }

    // ── Internal helpers ───────────────────────────────────────────────────────
    protected function _collectFormData($iUserId)
    {
        $sPropertyType = bx_process_input(isset($_POST['property_type']) ? $_POST['property_type'] : 'room', BX_DATA_TEXT);
        if (!in_array($sPropertyType, array('room', 'house', 'flat', 'backyard', 'townhouse'), true))
            $sPropertyType = 'room';

        $sLeaseTerm = bx_process_input(isset($_POST['lease_term']) ? $_POST['lease_term'] : 'month-to-month', BX_DATA_TEXT);
        if (!in_array($sLeaseTerm, array('month-to-month', '6 months', '12 months', '24 months'), true))
            $sLeaseTerm = 'month-to-month';

        $sFurnished = bx_process_input(isset($_POST['furnished']) ? $_POST['furnished'] : 'unfurnished', BX_DATA_TEXT);
        if (!in_array($sFurnished, array('unfurnished', 'semi-furnished', 'fully-furnished'), true))
            $sFurnished = 'unfurnished';

        $sStatus = bx_process_input(isset($_POST['status']) ? $_POST['status'] : 'available', BX_DATA_TEXT);
        if (!in_array($sStatus, array('available', 'hold', 'booked', 'taken'), true))
            $sStatus = 'available';

        $sVisibility = bx_process_input(isset($_POST['visibility']) ? $_POST['visibility'] : 'public', BX_DATA_TEXT);
        if (!in_array($sVisibility, array('public', 'space', 'group'), true))
            $sVisibility = 'public';

        $sAvailableFrom = bx_process_input(isset($_POST['available_from']) ? $_POST['available_from'] : '', BX_DATA_TEXT);
        $iGroupId = (int)(isset($_POST['group_id']) ? $_POST['group_id'] : 0);
        $iSpaceId = (int)(isset($_POST['space_id']) ? $_POST['space_id'] : 0);

        if ($sVisibility !== 'group')
            $iGroupId = 0;
        if ($sVisibility !== 'space')
            $iSpaceId = 0;

        return array(
            'title'               => bx_process_input(isset($_POST['title'])               ? $_POST['title']               : '', BX_DATA_TEXT),
            'description'         => bx_process_input(isset($_POST['description'])         ? $_POST['description']         : '', BX_DATA_TEXT),
            'property_type'       => $sPropertyType,
            'province'            => bx_process_input(isset($_POST['province'])            ? $_POST['province']            : '', BX_DATA_TEXT),
            'city'                => bx_process_input(isset($_POST['city'])                ? $_POST['city']                : '', BX_DATA_TEXT),
            'address'             => bx_process_input(isset($_POST['address'])             ? $_POST['address']             : '', BX_DATA_TEXT),
            'rent_zar'            => (float)(isset($_POST['rent_zar'])     ? $_POST['rent_zar']     : 0),
            'deposit_zar'         => (float)(isset($_POST['deposit_zar']) ? $_POST['deposit_zar'] : 0),
            'contact'             => bx_process_input(isset($_POST['contact'])             ? $_POST['contact']             : '', BX_DATA_TEXT),
            'contact_phone'       => bx_process_input(isset($_POST['contact_phone'])       ? $_POST['contact_phone']       : '', BX_DATA_TEXT),
            'contact_whatsapp'    => bx_process_input(isset($_POST['contact_whatsapp'])    ? $_POST['contact_whatsapp']    : '', BX_DATA_TEXT),
            'contact_email'       => bx_process_input(isset($_POST['contact_email'])       ? $_POST['contact_email']       : '', BX_DATA_TEXT),
            'available_from'      => $sAvailableFrom !== '' ? $sAvailableFrom : null,
            'lease_term'          => $sLeaseTerm,
            'bedrooms'            => bx_process_input(isset($_POST['bedrooms'])            ? $_POST['bedrooms']            : 0,   BX_DATA_INT),
            'bathrooms'           => bx_process_input(isset($_POST['bathrooms'])           ? $_POST['bathrooms']           : 0,   BX_DATA_INT),
            'parking'             => !empty($_POST['parking'])             ? 1 : 0,
            'pets_allowed'        => !empty($_POST['pets_allowed'])        ? 1 : 0,
            'furnished'           => $sFurnished,
            'utilities_included'  => !empty($_POST['utilities_included'])  ? 1 : 0,
            'prepaid_electricity' => !empty($_POST['prepaid_electricity'])  ? 1 : 0,
            'wifi_available'      => !empty($_POST['wifi_available'])      ? 1 : 0,
            'security_features'   => bx_process_input(isset($_POST['security_features'])   ? $_POST['security_features']   : '', BX_DATA_TEXT),
            'status'              => $sStatus,
            'visibility'          => $sVisibility,
            'group_id'            => $iGroupId,
            'space_id'            => $iSpaceId,
            'author_id'           => $iUserId,
        );
    }

    protected function _handleMediaUpload($iListingId, $iUserId)
    {
        if (empty($_FILES['media']['name'][0])) return;
        $oStorage = BxDolStorage::getObjectInstance('sa_rentals_files');
        if (!$oStorage) return;
        $aExisting = array();
        $aListing = $this->_oDb->getListing($iListingId);
        if (!empty($aListing['media_storage_ids']))
            $aExisting = array_filter(explode(',', $aListing['media_storage_ids']));
        $aFileIds = $aExisting;

        // Enforce photo quota from sys_options
        $iPhotoQuota = $this->_getPhotoQuota($iUserId);
        $iCurrentCount = count($aFileIds);

        foreach ($_FILES['media']['name'] as $k => $sName) {
            if (empty($sName)) continue;
            // Stop if quota reached (0 = unlimited)
            if ($iPhotoQuota > 0 && $iCurrentCount >= $iPhotoQuota) break;
            $aFile = array(
                'name'     => $_FILES['media']['name'][$k],
                'type'     => $_FILES['media']['type'][$k],
                'tmp_name' => $_FILES['media']['tmp_name'][$k],
                'error'    => $_FILES['media']['error'][$k],
                'size'     => $_FILES['media']['size'][$k],
            );
            $iFileId = $oStorage->storeFileFromForm($aFile, false, $iUserId);
            if ($iFileId) {
                $aFileIds[] = $iFileId;
                $iCurrentCount++;
            }
        }
        if (!empty($aFileIds))
            $this->_oDb->updateListing($iListingId, array('media_storage_ids' => implode(',', $aFileIds)));
    }

    protected function _renderForm($aValues = array(), $iEditId = 0)
    {
        $sAction = $iEditId
            ? BX_DOL_URL_ROOT . 'rentals-edit?id=' . $iEditId
            : BX_DOL_URL_ROOT . 'create-rentals-listing';
        $sTitle  = htmlspecialchars(isset($aValues['title'])       ? $aValues['title']       : '');
        $sDesc   = htmlspecialchars(isset($aValues['description'])  ? $aValues['description'] : '');
        $sProv   = htmlspecialchars(isset($aValues['province'])     ? $aValues['province']    : '');
        $sCity   = htmlspecialchars(isset($aValues['city'])         ? $aValues['city']        : '');
        $sAddr   = htmlspecialchars(isset($aValues['address'])      ? $aValues['address']     : '');
        $fRent   = isset($aValues['rent_zar'])   ? (float)$aValues['rent_zar']   : '';
        $fDep    = isset($aValues['deposit_zar']) ? (float)$aValues['deposit_zar'] : '';
        $sCont   = htmlspecialchars(isset($aValues['contact'])         ? $aValues['contact']         : '');
        $sPhone  = htmlspecialchars(isset($aValues['contact_phone'])   ? $aValues['contact_phone']   : '');
        $sWA     = htmlspecialchars(isset($aValues['contact_whatsapp']) ? $aValues['contact_whatsapp'] : '');
        $sEmail  = htmlspecialchars(isset($aValues['contact_email'])   ? $aValues['contact_email']   : '');
        $sFrom   = isset($aValues['available_from']) ? $aValues['available_from'] : '';
        $iBeds   = isset($aValues['bedrooms'])  ? (int)$aValues['bedrooms']  : 0;
        $iBaths  = isset($aValues['bathrooms']) ? (int)$aValues['bathrooms'] : 0;
        $sSec    = htmlspecialchars(isset($aValues['security_features']) ? $aValues['security_features'] : '');
        $sPropType  = isset($aValues['property_type']) ? $aValues['property_type'] : 'room';
        $sLease     = isset($aValues['lease_term'])    ? $aValues['lease_term']    : 'month-to-month';
        $sFurnished = isset($aValues['furnished'])     ? $aValues['furnished']     : 'unfurnished';
        $sStatus    = isset($aValues['status'])        ? $aValues['status']        : 'available';
        $sVisibility= isset($aValues['visibility'])    ? $aValues['visibility']    : 'public';
        $iGroupId   = isset($aValues['group_id'])      ? (int)$aValues['group_id'] : 0;
        $iSpaceId   = isset($aValues['space_id'])      ? (int)$aValues['space_id'] : 0;

        $chk = function($field, $val) use ($aValues) {
            return (!empty($aValues[$field]) && $aValues[$field]) ? ' checked' : '';
        };
        $sel = function($field, $val) use ($aValues) {
            return (isset($aValues[$field]) && $aValues[$field] == $val) ? ' selected' : '';
        };

        // Space/Group dropdowns from DB
        $iUserId = bx_get_logged_profile_id();
        $aSpaces = $this->_oDb->getUserSpaces($iUserId);
        $aGroups = $this->_oDb->getUserGroups($iUserId);

        $sSpaceOpts = '<option value="0">' . _t('_sa_rentals_no_space') . '</option>';
        foreach ($aSpaces as $oS)
            $sSpaceOpts .= '<option value="' . (int)$oS['id'] . '" ' . ($iSpaceId == $oS['id'] ? ' selected' : '') . '>' . htmlspecialchars($oS['title']) . '</option>';

        $sGroupOpts = '<option value="0">' . _t('_sa_rentals_no_group') . '</option>';
        foreach ($aGroups as $oG)
            $sGroupOpts .= '<option value="' . (int)$oG['id'] . '" ' . ($iGroupId == $oG['id'] ? ' selected' : '') . '>' . htmlspecialchars($oG['title']) . '</option>';

        $sDeleteBtn = $iEditId ? '<button type="submit" name="delete_listing" value="1" class="sar-btn-delete" onclick="return confirm(\'Delete this listing?\')">' . _t('_sa_rentals_delete') . '</button>' : '';
        $sSubmitLabel = $iEditId ? _t('_sa_rentals_save_changes') : _t('_sa_rentals_submit');

        return '<form class="sar-form" method="post" action="' . $sAction . '" enctype="multipart/form-data">

            <div class="sar-form-section">
            <h3>' . _t('_sa_rentals_section_basic') . '</h3>
            <label>' . _t('_sa_rentals_title') . ' *<input type="text" name="title" value="' . $sTitle . '" required></label>
            <label>' . _t('_sa_rentals_description') . '<textarea name="description">' . $sDesc . '</textarea></label>
            <label>' . _t('_sa_rentals_property_type') . '
                <select name="property_type">
                    <option value="room"' . $sel('property_type','room') . '>' . _t('_sa_rentals_type_room') . '</option>
                    <option value="house"' . $sel('property_type','house') . '>' . _t('_sa_rentals_type_house') . '</option>
                    <option value="flat"' . $sel('property_type','flat') . '>' . _t('_sa_rentals_type_flat') . '</option>
                    <option value="backyard"' . $sel('property_type','backyard') . '>' . _t('_sa_rentals_type_backyard') . '</option>
                    <option value="townhouse"' . $sel('property_type','townhouse') . '>' . _t('_sa_rentals_type_townhouse') . '</option>
                </select>
            </label>
            </div>

            <div class="sar-form-section">
            <h3>' . _t('_sa_rentals_section_location') . '</h3>
            <label>' . _t('_sa_rentals_province') . '<input type="text" name="province" value="' . $sProv . '"></label>
            <label>' . _t('_sa_rentals_city') . '<input type="text" name="city" value="' . $sCity . '"></label>
            <label>' . _t('_sa_rentals_address') . '<input type="text" name="address" value="' . $sAddr . '"></label>
            </div>

            <div class="sar-form-section">
            <h3>' . _t('_sa_rentals_section_pricing') . '</h3>
            <label>' . _t('_sa_rentals_rent') . ' * (R)<input type="number" name="rent_zar" value="' . $fRent . '" min="0" step="0.01" required></label>
            <label>' . _t('_sa_rentals_deposit') . ' (R)<input type="number" name="deposit_zar" value="' . $fDep . '" min="0" step="0.01"></label>
            </div>

            <div class="sar-form-section">
            <h3>' . _t('_sa_rentals_section_details') . '</h3>
            <label>' . _t('_sa_rentals_bedrooms') . '<input type="number" name="bedrooms" value="' . $iBeds . '" min="0" max="20"></label>
            <label>' . _t('_sa_rentals_bathrooms') . '<input type="number" name="bathrooms" value="' . $iBaths . '" min="0" max="20"></label>
            <label>' . _t('_sa_rentals_lease_term') . '
                <select name="lease_term">
                    <option value="month-to-month"' . $sel('lease_term','month-to-month') . '>' . _t('_sa_rentals_lease_monthly') . '</option>
                    <option value="6 months"' . $sel('lease_term','6 months') . '>' . _t('_sa_rentals_lease_6m') . '</option>
                    <option value="12 months"' . $sel('lease_term','12 months') . '>' . _t('_sa_rentals_lease_12m') . '</option>
                    <option value="24 months"' . $sel('lease_term','24 months') . '>' . _t('_sa_rentals_lease_24m') . '</option>
                </select>
            </label>
            <label>' . _t('_sa_rentals_furnished') . '
                <select name="furnished">
                    <option value="unfurnished"' . $sel('furnished','unfurnished') . '>' . _t('_sa_rentals_unfurnished') . '</option>
                    <option value="semi-furnished"' . $sel('furnished','semi-furnished') . '>' . _t('_sa_rentals_semi_furnished') . '</option>
                    <option value="fully-furnished"' . $sel('furnished','fully-furnished') . '>' . _t('_sa_rentals_fully_furnished') . '</option>
                </select>
            </label>
            <label>' . _t('_sa_rentals_available_from') . '<input type="date" name="available_from" value="' . $sFrom . '"></label>
            <label>' . _t('_sa_rentals_security') . '<input type="text" name="security_features" value="' . $sSec . '"></label>
            <div class="sar-checkbox-row"><input type="checkbox" name="parking" value="1"' . $chk('parking',1) . '><label>' . _t('_sa_rentals_parking') . '</label></div>
            <div class="sar-checkbox-row"><input type="checkbox" name="pets_allowed" value="1"' . $chk('pets_allowed',1) . '><label>' . _t('_sa_rentals_pets') . '</label></div>
            <div class="sar-checkbox-row"><input type="checkbox" name="utilities_included" value="1"' . $chk('utilities_included',1) . '><label>' . _t('_sa_rentals_utilities') . '</label></div>
            <div class="sar-checkbox-row"><input type="checkbox" name="prepaid_electricity" value="1"' . $chk('prepaid_electricity',1) . '><label>' . _t('_sa_rentals_prepaid') . '</label></div>
            <div class="sar-checkbox-row"><input type="checkbox" name="wifi_available" value="1"' . $chk('wifi_available',1) . '><label>' . _t('_sa_rentals_wifi') . '</label></div>
            </div>

            <div class="sar-form-section">
            <h3>' . _t('_sa_rentals_section_status') . '</h3>
            <label>' . _t('_sa_rentals_status_label') . '
                <select name="status">
                    <option value="available"' . $sel('status','available') . '>' . _t('_sa_rentals_status_available') . '</option>
                    <option value="hold"' . $sel('status','hold') . '>' . _t('_sa_rentals_status_hold') . '</option>
                    <option value="booked"' . $sel('status','booked') . '>' . _t('_sa_rentals_status_booked') . '</option>
                    <option value="taken"' . $sel('status','taken') . '>' . _t('_sa_rentals_status_taken') . '</option>
                </select>
            </label>
            </div>

            <div class="sar-form-section">
            <h3>' . _t('_sa_rentals_section_visibility') . '</h3>
            <label>' . _t('_sa_rentals_visibility_label') . '
                <select name="visibility" id="sar-visibility">
                    <option value="public"' . $sel('visibility','public') . '>' . _t('_sa_rentals_visibility_public') . '</option>
                    <option value="space"' . $sel('visibility','space') . '>' . _t('_sa_rentals_visibility_space') . '</option>
                    <option value="group"' . $sel('visibility','group') . '>' . _t('_sa_rentals_visibility_group') . '</option>
                </select>
            </label>
            <div class="sar-target-space" id="sar-target-space" style="display:none">
                <label>' . _t('_sa_rentals_assign_space') . '<select name="space_id">' . $sSpaceOpts . '</select></label>
            </div>
            <div class="sar-target-group" id="sar-target-group" style="display:none">
                <label>' . _t('_sa_rentals_assign_group') . '<select name="group_id">' . $sGroupOpts . '</select></label>
            </div>
            </div>

            <div class="sar-form-section">
            <h3>' . _t('_sa_rentals_section_contact') . '</h3>
            <label>' . _t('_sa_rentals_contact_name') . '<input type="text" name="contact" value="' . $sCont . '"></label>
            <label>' . _t('_sa_rentals_phone') . '<input type="tel" name="contact_phone" value="' . $sPhone . '"></label>
            <label>' . _t('_sa_rentals_whatsapp') . '<input type="tel" name="contact_whatsapp" value="' . $sWA . '"></label>
            <label>' . _t('_sa_rentals_email') . '<input type="email" name="contact_email" value="' . $sEmail . '"></label>
            </div>

            <div class="sar-form-section">
            <h3>' . _t('_sa_rentals_section_media') . '</h3>
            <label>' . _t('_sa_rentals_photos') . '<input type="file" name="media[]" multiple accept="image/*,video/*"></label>
            </div>

            <div class="sar-form-actions">
                <button type="submit" class="sar-btn-primary">' . $sSubmitLabel . '</button>
                ' . $sDeleteBtn . '
                <a href="' . BX_DOL_URL_ROOT . 'page.php?i=my-rentals-listings" class="sar-btn-cancel">' . _t('_sa_rentals_cancel') . '</a>
            </div>
        </form>
        <script>
        (function(){
            var vis = document.getElementById("sar-visibility");
            var spaceDiv = document.getElementById("sar-target-space");
            var groupDiv = document.getElementById("sar-target-group");
            function toggle(){
                spaceDiv.style.display = vis.value === "space" ? "" : "none";
                groupDiv.style.display = vis.value === "group" ? "" : "none";
            }
            vis.addEventListener("change", toggle);
            toggle();
        })();
        </script>';
    }

    protected function _renderCard($a)
    {
        $aStatusColors = array('available'=>'green','hold'=>'orange','booked'=>'blue','taken'=>'red');
        $sColor  = isset($aStatusColors[$a['status']]) ? $aStatusColors[$a['status']] : 'green';
        $sThumb  = '';
        if (!empty($a['media_storage_ids'])) {
            $oStorage = BxDolStorage::getObjectInstance('sa_rentals_files');
            if ($oStorage) {
                $iFirst = (int)trim(explode(',', $a['media_storage_ids'])[0]);
                if ($iFirst) {
                    $sUrl = $oStorage->getFileUrlById($iFirst);
                    if ($sUrl) $sThumb = '<img class="sar-card-thumb" src="' . bx_html_attribute($sUrl) . '" alt="">';
                }
            }
        }
        $sVerifiedBadge = (!empty($a['verified']) && getParam('sa_rentals_verified_badge'))
            ? '<span class="sar-badge-verified-sm">&#10003; ' . _t('_sa_rentals_badge_verified') . '</span>' : '';
        $sFeaturedBadge = $a['featured'] ? '<span class="sar-featured-sm">&#11088;</span>' : '';
        // WhatsApp quick-contact on card (number only — no full button to keep card compact)
        $sWaBtn = '';
        if (!empty($a['contact_whatsapp']) && getParam('sa_rentals_whatsapp_enabled')) {
            $sWaNum = preg_replace('/[^0-9]/', '', $a['contact_whatsapp']);
            $sWaBtn = '<a href="https://wa.me/' . $sWaNum . '" target="_blank" rel="noopener" class="sar-card-wa" onclick="event.stopPropagation()">&#128241;</a>';
        }
        return '<div class="sar-card">
            <a href="' . BX_DOL_URL_ROOT . 'page.php?i=view-rentals-listing&id=' . $a['id'] . '">' . $sThumb . '
            <div class="sar-card-body">
                <div class="sar-card-badges">
                    <span class="sar-card-badge">' . htmlspecialchars($a['property_type']) . '</span>
                    <span class="sar-status sar-status-' . $sColor . '">' . _t('_sa_rentals_status_' . $a['status']) . '</span>
                    ' . $sFeaturedBadge . $sVerifiedBadge . '
                </div>
                <h3>' . htmlspecialchars($a['title']) . '</h3>
                <div class="sar-card-location">&#128205; ' . htmlspecialchars($a['city']) . ', ' . htmlspecialchars($a['province']) . '</div>
                <div class="sar-card-price">R ' . number_format((float)$a['rent_zar'], 2) . ' /month</div>
                <div class="sar-card-meta">&#128065; ' . (int)$a['views_count'] . ' &nbsp; &#128716; ' . $a['bedrooms'] . ' bed ' . $sWaBtn . '</div>
            </div></a>
        </div>';
    }

    // ─── Admin manage block ────────────────────────────────────────────────────

    public function serviceGetAdminListingsBlock()
    {
        $this->_oTemplate->addCss(array('main.css'));
        $iUserId = bx_get_logged_profile_id();

        // Only moderators and admins
        if (!$this->_isAllowed('approve entry')
            && !$this->_isAllowed('edit any entry'))
            return '<p>' . _t('_sa_rentals_no_permission') . '</p>';

        // Handle actions
        $sAction = bx_process_input(bx_get('sar_action'), BX_DATA_TEXT);
        $iTargetId = (int)bx_get('sar_id');

        $sMsg = '';
        if ($sAction && $iTargetId) {
            switch ($sAction) {
                case 'approve':
                    if ($this->checkAllowApprove($iTargetId)) {
                        $this->_oDb->approveListing($iTargetId);
                        $sMsg = '<div class="sar-success">' . _t('_sa_rentals_admin_approved') . '</div>';
                    }
                    break;
                case 'feature':
                    if ($this->checkAllowFeature($iTargetId)) {
                        $this->_oDb->featureListing($iTargetId, true);
                        $sMsg = '<div class="sar-success">' . _t('_sa_rentals_admin_featured') . '</div>';
                    }
                    break;
                case 'unfeature':
                    if ($this->checkAllowFeature($iTargetId)) {
                        $this->_oDb->featureListing($iTargetId, false);
                        $sMsg = '<div class="sar-success">' . _t('_sa_rentals_admin_unfeatured') . '</div>';
                    }
                    break;
                case 'delete':
                    if ($this->checkAllowDelete($iTargetId)) {
                        $this->_oDb->deleteListing($iTargetId);
                        $oAlert = new BxDolAlerts('sa_rentals', 'deleted', $iTargetId, $iUserId);
                        $oAlert->alert();
                        $sMsg = '<div class="sar-success">' . _t('_sa_rentals_admin_deleted') . '</div>';
                    }
                    break;
                case 'verify':
                    if ($this->_isAllowed('verify listing')) {
                        $this->_oDb->verifyListing($iTargetId, $iUserId);
                        $sMsg = '<div class="sar-success">' . _t('_sa_rentals_admin_verified') . '</div>';
                    }
                    break;
                case 'unverify':
                    if ($this->_isAllowed('verify listing')) {
                        $this->_oDb->unverifyListing($iTargetId);
                        $sMsg = '<div class="sar-success">' . _t('_sa_rentals_admin_unverified') . '</div>';
                    }
                    break;
            }
        }
        $sStatusFilter = bx_process_input(bx_get('sar_status'), BX_DATA_TEXT);
        $aFilter = array();
        if ($sStatusFilter) $aFilter['status'] = $sStatusFilter;

        $aListings = $this->_oDb->getListingsAdmin($aFilter);

        $sBaseUrl = BX_DOL_URL_ROOT . 'page.php?i=sa-rentals-admin';

        // Status filter tabs
        $aStatuses = array('' => _t('_sa_rentals_admin_all'), 'pending' => _t('_sa_rentals_status_pending'),
            'available' => _t('_sa_rentals_status_available'), 'hold' => _t('_sa_rentals_status_hold'),
            'booked' => _t('_sa_rentals_status_booked'), 'taken' => _t('_sa_rentals_status_taken'));
        $sTabs = '<div class="sar-admin-tabs">';
        foreach ($aStatuses as $sVal => $sLabel) {
            $sActive = ($sStatusFilter === $sVal) ? ' sar-tab-active' : '';
            $sTabs .= '<a class="sar-tab' . $sActive . '" href="' . $sBaseUrl . '&sar_status=' . urlencode($sVal) . '">' . $sLabel . '</a>';
        }
        $sTabs .= '</div>';

        if (empty($aListings)) {
            return '<div class="sar-wrap">' . $sMsg . $sTabs . '<p class="sar-empty">' . _t('_sa_rentals_no_listings') . '</p></div>';
        }

        $sTable = '<table class="sar-admin-table">
            <thead><tr>
                <th>' . _t('_sa_rentals_admin_col_id') . '</th>
                <th>' . _t('_sa_rentals_title') . '</th>
                <th>' . _t('_sa_rentals_province') . '</th>
                <th>' . _t('_sa_rentals_rent') . '</th>
                <th>' . _t('_sa_rentals_status_label') . '</th>
                <th>' . _t('_sa_rentals_admin_col_featured') . '</th>
                <th>' . _t('_sa_rentals_admin_col_created') . '</th>
                <th>' . _t('_sa_rentals_admin_col_actions') . '</th>
            </tr></thead><tbody>';

        $aStatusColors = array('available'=>'green','hold'=>'orange','booked'=>'blue','taken'=>'red','pending'=>'grey');
        foreach ($aListings as $a) {
            $iId = (int)$a['id'];
            $sColor = isset($aStatusColors[$a['status']]) ? $aStatusColors[$a['status']] : 'green';
            $sFeatBadge = $a['featured'] ? '<span class="sar-featured">&#11088;</span>' : '—';

            $sActions = '<a href="' . BX_DOL_URL_ROOT . 'page.php?i=view-rentals-listing&id=' . $iId . '" class="sar-admin-act sar-admin-act-view">' . _t('_sa_rentals_view') . '</a>';
            if ($a['status'] === 'pending')
                $sActions .= ' <a href="' . $sBaseUrl . '&sar_action=approve&sar_id=' . $iId . '&sar_status=' . urlencode($sStatusFilter) . '" class="sar-admin-act sar-admin-act-approve">' . _t('_sa_rentals_admin_approve') . '</a>';
            if ($a['featured'])
                $sActions .= ' <a href="' . $sBaseUrl . '&sar_action=unfeature&sar_id=' . $iId . '&sar_status=' . urlencode($sStatusFilter) . '" class="sar-admin-act sar-admin-act-unfeature">' . _t('_sa_rentals_admin_unfeature') . '</a>';
            else
                $sActions .= ' <a href="' . $sBaseUrl . '&sar_action=feature&sar_id=' . $iId . '&sar_status=' . urlencode($sStatusFilter) . '" class="sar-admin-act sar-admin-act-feature">' . _t('_sa_rentals_admin_feature') . '</a>';
            if (getParam('sa_rentals_verified_badge')) {
                if (!empty($a['verified']))
                    $sActions .= ' <a href="' . $sBaseUrl . '&sar_action=unverify&sar_id=' . $iId . '&sar_status=' . urlencode($sStatusFilter) . '" class="sar-admin-act sar-admin-act-unfeature">' . _t('_sa_rentals_admin_unverify') . '</a>';
                else
                    $sActions .= ' <a href="' . $sBaseUrl . '&sar_action=verify&sar_id=' . $iId . '&sar_status=' . urlencode($sStatusFilter) . '" class="sar-admin-act sar-admin-act-verify">' . _t('_sa_rentals_admin_verify') . '</a>';
            }
            $sActions .= ' <a href="' . $sBaseUrl . '&sar_action=delete&sar_id=' . $iId . '&sar_status=' . urlencode($sStatusFilter) . '" class="sar-admin-act sar-admin-act-delete" onclick="return confirm(\'' . _t('_sa_rentals_admin_confirm_delete') . '\')">' . _t('_sa_rentals_delete') . '</a>';

            $sTable .= '<tr>
                <td>' . $iId . '</td>
                <td><a href="' . BX_DOL_URL_ROOT . 'page.php?i=view-rentals-listing&id=' . $iId . '">' . htmlspecialchars($a['title']) . '</a></td>
                <td>' . htmlspecialchars($a['province']) . '</td>
                <td>R ' . number_format((float)$a['rent_zar'], 2) . '</td>
                <td><span class="sar-status sar-status-' . $sColor . '">' . _t('_sa_rentals_status_' . $a['status']) . '</span></td>
                <td>' . $sFeatBadge . '</td>
                <td>' . date('d M Y', strtotime($a['created'])) . '</td>
                <td>' . $sActions . '</td>
            </tr>';
        }
        $sTable .= '</tbody></table>';

        return '<div class="sar-wrap">' . $sMsg . $sTabs . $sTable . '</div>';
    }

    // ─── Permission Gates ──────────────────────────────────────────────────────

    public function checkAllowView($iEntryId)
    {
        if (!$this->_isAllowed('view entry'))
            return false;
        $aEntry = $this->_oDb->getListing($iEntryId);
        if (!$aEntry)
            return false;
        if (!$this->_oDb->canViewListing($aEntry, bx_get_logged_profile_id()))
            return false;
        return true;
    }

    public function checkAllowEdit($iEntryId)
    {
        $aEntry = $this->_oDb->getListing($iEntryId);
        $iProfileId = bx_get_logged_profile_id();
        if ($this->_isAllowed('edit any entry'))
            return true;
        if ($this->_isAllowed('edit own entry')
            && (int)$aEntry['author_id'] === (int)$iProfileId)
            return true;
        return false;
    }

    public function checkAllowDelete($iEntryId)
    {
        $aEntry = $this->_oDb->getListing($iEntryId);
        $iProfileId = bx_get_logged_profile_id();
        if ($this->_isAllowed('delete any entry'))
            return true;
        if ($this->_isAllowed('delete own entry')
            && (int)$aEntry['author_id'] === (int)$iProfileId)
            return true;
        return false;
    }

    public function checkAllowApprove($iEntryId)
    {
        return $this->_isAllowed('approve entry');
    }

    public function checkAllowFeature($iEntryId)
    {
        return $this->_isAllowed('feature entry');
    }

    // ─── Timeline Integration ──────────────────────────────────────────────────

    public function serviceGetTimelineData()
    {
        $sModule = $this->_aModule['name'];
        // Format mirrors BxBaseModGeneralModule::serviceGetTimelineData() exactly.
        // Only the insert handler carries content fields (module_name/method/class/groupable/group_by).
        // Extra fields (title, icon, active, etc.) must NOT be here — they cause INSERT failures.
        // The 'alerts' key (not 'groups') is what serviceAddHandlers reads.
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
        $iEntryId = (int)($aEvent['object_id'] ?? 0);
        $aEntry   = $this->_oDb->getListing($iEntryId);
        if (!$aEntry) return false;

        $iAuthorId = (int)$aEntry['author_id'];
        $sUrl      = BX_DOL_URL_ROOT . 'page.php?i=view-rentals-listing&id=' . $iEntryId;

        // Build content array — must be a non-empty array for getData() to accept this post
        $aContent = array(
            'url'   => $sUrl,
            'title' => bx_process_output($aEntry['title']),
            'text'  => bx_process_output($aEntry['description'] ?? ''),
        );

        // Attach thumbnail from storage if available
        if (!empty($aEntry['media_storage_ids'])) {
            $oStorage = BxDolStorage::getObjectInstance('sa_rentals_files');
            if ($oStorage) {
                $iFirst = (int)trim(explode(',', $aEntry['media_storage_ids'])[0]);
                if ($iFirst) {
                    $sImageUrl = (string)$oStorage->getFileUrlById($iFirst);
                    if ($sImageUrl)
                        $aContent['images'] = array($sImageUrl);
                }
            }
        }

        // Canonical return format expected by BxTimelineTemplate::getData():
        // object_owner_id and content (non-empty array) are required.
        return array(
            '_cache'           => true,
            'object_owner_id'  => $iAuthorId,
            'url'              => $sUrl,
            'content'          => $aContent,
            'title'            => bx_process_output($aEntry['title']),
            'description'      => '',
        );
    }

    public function serviceGetContentInfoArray($iEntryId)
    {
        $aEntry = $this->_oDb->getListing((int)$iEntryId);
        if (!$aEntry) return false;

        return array(
            'id'          => (int)$aEntry['id'],
            'title'       => bx_process_output($aEntry['title']),
            'description' => bx_process_output($aEntry['description'] ?? ''),
            'url'         => BX_DOL_URL_ROOT . 'page.php?i=view-rentals-listing&id=' . $iEntryId,
            'image'       => '',
            'author'      => (int)$aEntry['author_id'],
            'added'       => strtotime($aEntry['created']),
            'privacy'     => 1,
        );
    }

    // ─── Notifications Integration ─────────────────────────────────────────────

    public function serviceGetNotificationsData()
    {
        $sModule = $this->_aModule['name'];
        // Canonical format from BxBaseModGeneralModule::serviceGetNotificationsData().
        // 'handlers'  → rows in bx_notifications_handlers (only INSERT carries content fields)
        // 'settings'  → rows in bx_notifications_settings (controls the notifications settings page)
        // 'alerts'    → rows in sys_alerts (wires alert unit/action to the bx_notifications handler)
        return array(
            'handlers' => array(
                array('group' => $sModule . '_object', 'type' => 'insert', 'alert_unit' => $sModule, 'alert_action' => 'added',   'module_name' => $sModule, 'module_method' => 'get_notifications_post', 'module_class' => 'Module', 'module_event_privacy' => ''),
                array('group' => $sModule . '_object', 'type' => 'update', 'alert_unit' => $sModule, 'alert_action' => 'edited'),
                array('group' => $sModule . '_object', 'type' => 'delete', 'alert_unit' => $sModule, 'alert_action' => 'deleted'),
            ),
            'settings' => array(
                // 'group' = visual group on settings page, 'types' = who can receive ('personal' = listing owner, 'follow_member' = followers)
                array('group' => 'object', 'unit' => $sModule, 'action' => 'added', 'types' => array('personal', 'follow_member', 'follow_context')),
            ),
            'alerts' => array(
                array('unit' => $sModule, 'action' => 'added'),
                array('unit' => $sModule, 'action' => 'edited'),
                array('unit' => $sModule, 'action' => 'deleted'),
            ),
        );
    }

    public function serviceGetNotificationsPost($aEvent)
    {
        $iEntryId = (int)($aEvent['object_id'] ?? 0);
        $aEntry   = $this->_oDb->getListing($iEntryId);
        if (!$aEntry) return false;

        $sAction = ($aEvent['action'] ?? '') === 'edited'
            ? _t('_sa_rentals_notification_edited')
            : _t('_sa_rentals_notification_added');

        return array(
            'id'      => (int)($aEvent['id'] ?? 0),
            'title'   => bx_process_output($aEntry['title']),
            'content' => $sAction,
            'url'     => BX_DOL_URL_ROOT . 'page.php?i=view-rentals-listing&id=' . $iEntryId,
            'image'   => '',
            'author'  => (int)$aEntry['author_id'],
        );
    }

    // ─── Phase 1 Enhancement helpers ──────────────────────────────────────────

    /**
     * Auto-expire a listing if its expires_at date is in the past.
     * Called on every detail page load for the listing.
     * Silently flips status to 'hold' so the owner still sees it in My Listings.
     */
    private function _checkExpiry($aListing)
    {
        if (empty($aListing['expires_at'])) return;
        if ($aListing['status'] !== 'available') return;
        if (strtotime($aListing['expires_at']) < time())
            $this->_oDb->expireListing($aListing['id']);
    }

    private function _isAllowed($sAction)
{
    $iLandlordLevel = (int)getParam('sa_rentals_landlord_level_id');
    $iAgentLevel    = (int)getParam('sa_rentals_agent_level_id');

    // Moderator=7, Admin=8 always included
    $aModAdmin = array(7, 8);

    switch ($sAction) {
        case 'view entry':
            // Everyone logged in can view
            return BxDolAcl::getInstance()->isMemberLevelInSet(
                array_filter(array_merge(array(2, 3, 4, 5, 9, 10, 11, 12, 13,
                    $iLandlordLevel, $iAgentLevel), $aModAdmin))
            );
        case 'create entry':
            $aLevels = array_filter(array($iLandlordLevel, $iAgentLevel));
            if (empty($aLevels)) return false;
            return BxDolAcl::getInstance()->isMemberLevelInSet(
                array_merge($aLevels, $aModAdmin)
            );
        case 'edit own entry':
        case 'delete own entry':
            $aLevels = array_filter(array($iLandlordLevel, $iAgentLevel));
            if (empty($aLevels)) return false;
            return BxDolAcl::getInstance()->isMemberLevelInSet(
                array_merge($aLevels, $aModAdmin)
            );
        case 'edit any entry':
        case 'delete any entry':
        case 'approve entry':
            return BxDolAcl::getInstance()->isMemberLevelInSet($aModAdmin);
        case 'verify listing':
        case 'feature entry':
            // Admin only
            return BxDolAcl::getInstance()->isMemberLevelInSet(array(8));
        default:
            return false;
    }
}
    /**
     * Returns true if the member is on the Estate Agent level.
     * Reads the level ID from sys_options — 0 means not yet configured.
     */
    private function _isEstateAgent($iMemberId)
    {
        $iAgentLevelId = (int)getParam('sa_rentals_agent_level_id');
        if ($iAgentLevelId === 0) return false;
        $oProfile = BxDolProfile::getInstance($iMemberId);
        if (!$oProfile) return false;
        $oAccount = BxDolAccount::getInstance($oProfile->getAccountId());
        if (!$oAccount) return false;
        $aMembership = BxDolAcl::getInstance()->getMemberMembershipInfo($iMemberId);
        return is_array($aMembership) && (int)$aMembership['id'] === $iAgentLevelId;
    }

    /**
     * Returns true if the member is on the Landlord level.
     */
    private function _isLandlord($iMemberId)
    {
        $iLandlordLevelId = (int)getParam('sa_rentals_landlord_level_id');
        if ($iLandlordLevelId === 0) return false;
        return (int)BxDolAcl::getInstance()->getMemberMembershipInfo($iMemberId)['id'] === $iLandlordLevelId;
    }

    /**
     * Check listing quota before allowing a new listing to be created.
     * Estate Agents: sa_rentals_agent_quota (0 = unlimited)
     * Landlords: sa_rentals_landlord_quota (0 = unlimited, but default is 3)
     * Moderators/Admins: always allowed.
     */
    private function _checkListingQuota($iMemberId)
    {
        // Mods and admins are never quota-blocked
        if ($this->_isAllowed('edit any entry'))
            return true;

        $iQuota = $this->_isEstateAgent($iMemberId)
            ? (int)getParam('sa_rentals_agent_quota')
            : (int)getParam('sa_rentals_landlord_quota');

        // 0 = unlimited
        if ($iQuota === 0) return true;

        $iCount = $this->_oDb->getActiveListingCountByAuthor($iMemberId);
        return $iCount < $iQuota;
    }

    /**
     * Check photo quota before allowing additional uploads.
     * Returns the max allowed photos for the member's level, or 0 if no limit applies.
     */
    private function _getPhotoQuota($iMemberId)
    {
        if ($this->_isAllowed('edit any entry'))
            return 0; // unlimited for mods/admins
        return $this->_isEstateAgent($iMemberId)
            ? (int)getParam('sa_rentals_agent_photos')
            : (int)getParam('sa_rentals_landlord_photos');
    }
}
