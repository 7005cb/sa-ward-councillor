<?php defined('BX_DOL') or die('hack attempt');

bx_import('BxDolModule');

class SaRentalsModule extends BxDolModule
{
    function __construct(&$aModule)
    {
        parent::__construct($aModule);
    }

    function serviceGetListingsBlock()
    {
        $this->_oTemplate->addCss(array('main.css'));
        $aFilters = array();
        if (!empty($_GET['province'])) $aFilters['province'] = bx_process_input($_GET['province']);
        if (!empty($_GET['type']))     $aFilters['type']     = bx_process_input($_GET['type']);
        $aListings = $this->_oDb->getListings($aFilters);

        $sFilter = '<div class="sar-filters"><form method="get">
            <select name="province">
                <option value="">All Provinces</option>
                <option value="Gauteng">Gauteng</option>
                <option value="Western Cape">Western Cape</option>
                <option value="KwaZulu-Natal">KwaZulu-Natal</option>
                <option value="Eastern Cape">Eastern Cape</option>
                <option value="Limpopo">Limpopo</option>
                <option value="Mpumalanga">Mpumalanga</option>
                <option value="North West">North West</option>
                <option value="Free State">Free State</option>
                <option value="Northern Cape">Northern Cape</option>
            </select>
            <select name="type">
                <option value="">All Types</option>
                <option value="room">Room</option>
                <option value="house">House</option>
                <option value="flat">Flat/Apartment</option>
                <option value="backyard">Backyard Unit</option>
                <option value="townhouse">Townhouse</option>
            </select>
            <button type="submit">Filter</button>
        </form></div>';

        $sCta = '<div class="sar-cta"><a href="' . BX_DOL_URL_ROOT . 'page.php?i=create-rentals-listing" class="sar-btn-primary">' . _t('_sa_rentals_post_listing') . '</a></div>';

        if (empty($aListings)) {
            $sContent = '<div class="sar-empty"><p>' . _t('_sa_rentals_no_listings') . '</p></div>';
        } else {
            $sContent = '<div class="sar-grid">';
            foreach ($aListings as $aItem) $sContent .= $this->_renderCard($aItem);
            $sContent .= '</div>';
        }
        return '<div class="sar-wrap">' . $sFilter . $sCta . $sContent . '</div>';
    }

    function serviceGetListingDetailBlock()
    {
        $this->_oTemplate->addCss(array('main.css'));
        $iId = (int)bx_get('id');
        if (!$iId) return '<p>' . _t('_sa_rentals_not_found') . '</p>';
        $a = $this->_oDb->getListing($iId);
        if (!$a) return '<p>' . _t('_sa_rentals_not_found') . '</p>';
        return '<div class="sar-detail">
            <h1>' . htmlspecialchars($a['title']) . '</h1>
            <div class="sar-meta">
                <span class="sar-badge">' . htmlspecialchars($a['property_type']) . '</span>
                <span class="sar-price">R ' . number_format((float)$a['rent_zar'],2) . ' /month</span>
            </div>
            <div class="sar-location">&#128205; ' . htmlspecialchars($a['address']) . ', ' . htmlspecialchars($a['city']) . ', ' . htmlspecialchars($a['province']) . '</div>
            <div class="sar-desc">' . nl2br(htmlspecialchars($a['description'])) . '</div>
            <div class="sar-contact"><h3>' . _t('_sa_rentals_contact_landlord') . '</h3><p>' . htmlspecialchars($a['contact']) . '</p></div>
            <a href="' . BX_DOL_URL_ROOT . 'page.php?i=rentals-listings" class="sar-back">&larr; ' . _t('_sa_rentals_back') . '</a>
        </div>';
    }

    function serviceGetCreateListingBlock()
    {
        $this->_oTemplate->addCss(array('main.css'));
        $iUserId = bx_get_logged_profile_id();
        if (!$iUserId) return '<p>' . _t('_sa_rentals_login_required') . '</p>';
        $sMsg = '';
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $aData = array(
                'title'         => bx_process_input(bx_get('title')),
                'description'   => bx_process_input(bx_get('description')),
                'property_type' => bx_process_input(bx_get('property_type')),
                'province'      => bx_process_input(bx_get('province')),
                'city'          => bx_process_input(bx_get('city')),
                'address'       => bx_process_input(bx_get('address')),
                'rent_zar'      => bx_process_input(bx_get('rent_zar')),
                'contact'       => bx_process_input(bx_get('contact')),
                'author_id'     => $iUserId,
            );
            if (empty($aData['title']) || empty($aData['rent_zar'])) {
                $sMsg = '<div class="sar-error">' . _t('_sa_rentals_error_required') . '</div>';
            } else {
                $this->_oDb->addListing($aData);
                header('Location: ' . BX_DOL_URL_ROOT . 'page.php?i=rentals-listings');
                exit;
            }
        }
        return $sMsg . '<div class="sar-form-wrap"><form method="post" class="sar-form">
            <label>' . _t('_sa_rentals_field_title') . ' *<input type="text" name="title" required></label>
            <label>' . _t('_sa_rentals_field_type') . '<select name="property_type">
                <option value="room">Room</option><option value="house">House</option>
                <option value="flat">Flat/Apartment</option><option value="backyard">Backyard Unit</option>
                <option value="townhouse">Townhouse</option></select></label>
            <label>' . _t('_sa_rentals_field_province') . '<select name="province">
                <option value="Gauteng">Gauteng</option><option value="Western Cape">Western Cape</option>
                <option value="KwaZulu-Natal">KwaZulu-Natal</option><option value="Eastern Cape">Eastern Cape</option>
                <option value="Limpopo">Limpopo</option><option value="Mpumalanga">Mpumalanga</option>
                <option value="North West">North West</option><option value="Free State">Free State</option>
                <option value="Northern Cape">Northern Cape</option></select></label>
            <label>' . _t('_sa_rentals_field_city') . '<input type="text" name="city"></label>
            <label>' . _t('_sa_rentals_field_address') . '<input type="text" name="address"></label>
            <label>' . _t('_sa_rentals_field_rent') . ' (ZAR) *<input type="number" name="rent_zar" step="0.01" required></label>
            <label>' . _t('_sa_rentals_field_description') . '<textarea name="description" rows="5"></textarea></label>
            <label>' . _t('_sa_rentals_field_contact') . '<input type="text" name="contact"></label>
            <button type="submit" class="sar-btn-primary">' . _t('_sa_rentals_submit') . '</button>
        </form></div>';
    }

    function serviceGetMyListingsBlock()
    {
        $this->_oTemplate->addCss(array('main.css'));
        $iUserId = bx_get_logged_profile_id();
        if (!$iUserId) return '<p>' . _t('_sa_rentals_login_required') . '</p>';
        $aListings = $this->_oDb->getListings(array('author_id' => $iUserId, 'all_statuses' => true));
        if (empty($aListings))
            return '<div class="sar-empty"><p>' . _t('_sa_rentals_no_listings') . '</p>
                <a href="' . BX_DOL_URL_ROOT . 'page.php?i=create-rentals-listing" class="sar-btn-primary">' . _t('_sa_rentals_post_listing') . '</a></div>';
        $sOut = '<div class="sar-my-listings">';
        foreach ($aListings as $aItem) $sOut .= $this->_renderCard($aItem, true);
        return $sOut . '</div>';
    }

    protected function _renderCard($a, $bOwner = false)
    {
        $sUrl   = BX_DOL_URL_ROOT . 'page.php?i=view-rentals-listing&id=' . (int)$a['id'];
        $sOwner = $bOwner ? '<div class="sar-owner-actions"><span class="sar-status sar-status-' . $a['status'] . '">' . $a['status'] . '</span></div>' : '';
        return '<div class="sar-card">
            <div class="sar-card-badge">' . htmlspecialchars($a['property_type']) . '</div>
            <h3><a href="' . $sUrl . '">' . htmlspecialchars($a['title']) . '</a></h3>
            <div class="sar-card-location">&#128205; ' . htmlspecialchars($a['city']) . ', ' . htmlspecialchars($a['province']) . '</div>
            <div class="sar-card-price">R ' . number_format((float)$a['rent_zar'],2) . ' /month</div>
            ' . $sOwner . '</div>';
    }
}
