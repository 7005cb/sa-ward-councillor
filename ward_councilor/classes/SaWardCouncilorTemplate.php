<?php defined('BX_DOL') or die('hack attempt');

class SaWardCouncilorTemplate extends BxDolModuleTemplate
{
    function __construct(&$oConfig, &$oDb)
    {
        parent::__construct($oConfig, $oDb);
    }

    public function addCss($mixedFiles = 'main.css', $bDynamic = false)
    {
        return parent::addCss($mixedFiles, $bDynamic);
    }

    public function getWardNavStrip($iSpaceId = 0)
    {
        $this->addCss(array('nav.css'));

        $sRoot    = BX_DOL_URL_ROOT;
        $iSpaceId = (int)$iSpaceId;
        $sPanelId = 'wc-panel-' . $iSpaceId;

        $aButtons = array(
            'summary'       => array('icon' => 'landmark',     'label' => _t('_sa_ward_councilor_menu_dashboard'),     'method' => 'get_space_summary_block'),
            'requests'      => array('icon' => 'list-alt',     'label' => _t('_sa_ward_councilor_menu_requests'),      'method' => 'get_requests_block'),
            'meetings'      => array('icon' => 'far calendar', 'label' => _t('_sa_ward_councilor_menu_meetings'),      'method' => 'get_meetings_block'),
            'announcements' => array('icon' => 'bullhorn',     'label' => _t('_sa_ward_councilor_menu_announcements'), 'method' => 'get_announcements_block'),
            'my-requests'   => array('icon' => 'far user',     'label' => _t('_sa_ward_councilor_menu_my_requests'),   'method' => 'get_my_requests_block'),
        );
        if($this->_isCouncilorContext()) {
            $aButtons['manage'] = array('icon' => 'cog', 'label' => _t('_sa_ward_councilor_menu_manage'), 'method' => 'get_manage_block');
        }

        $sBtns = '';
        foreach($aButtons as $sKey => $aBtn) {
            $sBtns .= '<button type="button"'
                    . ' class="wc-nav-strip-btn"'
                    . ' data-wc-tab="' . $sKey . '"'
                    . ' data-wc-method="' . $aBtn['method'] . '"'
                    . ' data-wc-space="' . $iSpaceId . '"'
                    . ' data-wc-panel="' . $sPanelId . '"'
                    . ' onclick="wcLoadTab(this)">'
                    . '<span class="sys-icon ' . $aBtn['icon'] . ' col-green3"></span>'
                    . htmlspecialchars($aBtn['label'])
                    . '</button>';
        }

        $sWardLabel = 'Quick Preview';

        // JS: define wcLoadTab once, then auto-load first tab
        $sBaseUrl = $sRoot . 'modules/sa/ward_councilor/request.php';

        $sJs  = '<script>';
        $sJs .= '(function(){';
        $sJs .= 'if(window.wcLoadTab)return;';
        $sJs .= 'window.wcLoadTab=function(btn){';
        $sJs .=   'var pid=btn.getAttribute("data-wc-panel");';
        $sJs .=   'var method=btn.getAttribute("data-wc-method");';
        $sJs .=   'var sid=btn.getAttribute("data-wc-space");';
        $sJs .=   'var panel=document.getElementById(pid);';
        $sJs .=   'if(!panel)return;';
        $sJs .=   'var strip=btn.closest(".wc-nav-strip");';
        // If panel is already hidden (collapsed), expand it first
        $sJs .=   'if(panel.style.display==="none"){';
        $sJs .=     'panel.style.display="block";';
        $sJs .=     'strip.classList.remove("wc-collapsed");';
        $sJs .=   '}';
        // If clicking already-active tab, do nothing (don't collapse)
        $sJs .=   'if(btn.classList.contains("wc-active")){';
        $sJs .=     'return;';
        $sJs .=   '}';
        $sJs .=   'strip.querySelectorAll(".wc-nav-strip-btn").forEach(function(b){b.classList.remove("wc-active");});';
        $sJs .=   'btn.classList.add("wc-active");';
        $sJs .=   'panel.innerHTML="<div class=\"wc-panel-loading\">Loading\u2026</div>";';
        $sJs .=   'var url="' . $sBaseUrl . '?action="+encodeURIComponent(method)+"&space_id="+encodeURIComponent(sid);';
        $sJs .=   'fetch(url,{credentials:"same-origin"})';
        $sJs .=     '.then(function(r){return r.text();})';
        $sJs .=     '.then(function(html){';
        $sJs .=       'try{var j=JSON.parse(html);panel.innerHTML="<div class=\"wc-panel-inner\">"+(j.content||j.html||html)+"</div>";}';
        $sJs .=       'catch(e){panel.innerHTML="<div class=\"wc-panel-inner\">"+html+"</div>";}';
        $sJs .=     '})';
        $sJs .=     '.catch(function(){panel.innerHTML="<p style=\"padding:12px;color:#c00\">Could not load content.</p>";});';
        $sJs .= '};';
        // Chevron toggle: show/hide panel content
        $sJs .= 'window.wcTogglePreview=function(btn){';
        $sJs .=   'var strip=btn.closest(".wc-nav-strip");';
        $sJs .=   'var panel=document.getElementById(strip.getAttribute("data-wc-panel")||"");';
        $sJs .=   'if(!panel)return;';
        $sJs .=   'if(panel.style.display==="none"){';
        $sJs .=     'panel.style.display="block";';
        $sJs .=     'strip.classList.remove("wc-collapsed");';
        $sJs .=     'btn.classList.remove("wc-collapsed");';
        $sJs .=     'btn.querySelector(".wc-nav-toggle-label").textContent="Hide";';
        $sJs .=     'btn.querySelector(".wc-nav-toggle-icon").textContent="▲";';
        $sJs .=     'var first=strip.querySelector(".wc-nav-strip-btn");';
        $sJs .=     'if(first&&!strip.querySelector(".wc-nav-strip-btn.wc-active")){wcLoadTab(first);}';
        $sJs .=   '}else{';
        $sJs .=     'panel.style.display="none";';
        $sJs .=     'strip.classList.add("wc-collapsed");';
        $sJs .=     'btn.classList.add("wc-collapsed");';
        $sJs .=     'btn.querySelector(".wc-nav-toggle-label").textContent="Show";';
        $sJs .=     'btn.querySelector(".wc-nav-toggle-icon").textContent="▼";';
        $sJs .=   '}';
        $sJs .= '};';
        $sJs .= '})();';
        // Auto-load first tab on page load (default: expanded)
        $sJs .= '(function(){';
        $sJs .=   'var sid="' . $iSpaceId . '";';
        $sJs .=   'var init=function(){';
        $sJs .=     'var strip=document.querySelector(".wc-nav-strip[data-wc-space=\""+sid+"\"]");';
        $sJs .=     'if(strip){var first=strip.querySelector(".wc-nav-strip-btn");if(first)wcLoadTab(first);}';
        $sJs .=   '};';
        $sJs .=   'if(document.readyState==="loading"){document.addEventListener("DOMContentLoaded",init);}else{setTimeout(init,50);}';
        $sJs .= '})();';
        $sJs .= '</script>';

        return '<div class="wc-nav-strip" data-wc-space="' . $iSpaceId . '" data-wc-panel="' . $sPanelId . '">'
             . '<span class="wc-nav-strip-label">'
             . htmlspecialchars($sWardLabel)
             . '</span>'
             . $sBtns
             . '<button type="button" class="wc-nav-toggle" onclick="wcTogglePreview(this)" title="Show/hide preview">'
             . '<span class="wc-nav-toggle-icon">▲</span>'
             . '<span class="wc-nav-toggle-label">Hide</span>'
             . '</button>'
             . '</div>'
             . '<div id="' . $sPanelId . '" class="wc-nav-panel"></div>'
             . $sJs;
    }

    public function getSidebarBlock($iCurrentSpaceId = 0)
    {
        $this->addCss(array('nav.css'));

        $sRoot = BX_DOL_URL_ROOT;
        $sUri  = isset($_GET['i']) ? $_GET['i'] : '';

        $aWardItems = array(
            array('icon' => 'landmark',    'label' => _t('_sa_ward_councilor_menu_dashboard'),     'uri' => 'ward-councilor-dashboard'),
            array('icon' => 'list-alt',    'label' => _t('_sa_ward_councilor_menu_requests'),      'uri' => 'ward-requests'),
            array('icon' => 'far calendar','label' => _t('_sa_ward_councilor_menu_meetings'),       'uri' => 'ward-meetings'),
            array('icon' => 'bullhorn',    'label' => _t('_sa_ward_councilor_menu_announcements'), 'uri' => 'ward-announcements'),
            array('icon' => 'far user',    'label' => _t('_sa_ward_councilor_menu_my_requests'),   'uri' => 'my-ward-requests'),
        );
        if($this->_isCouncilorContext()) {
            $aWardItems[] = array('icon' => 'cog', 'label' => _t('_sa_ward_councilor_menu_manage'), 'uri' => 'ward-manage');
        }

        $sWardItems = '';
        foreach($aWardItems as $aItem) {
            $bActive = ($sUri === $aItem['uri']);
            $sParam  = $iCurrentSpaceId
                ? '?i=' . $aItem['uri'] . '&space_id=' . (int)$iCurrentSpaceId
                : '?i=' . $aItem['uri'];
            $sWardItems .= '<a href="' . $sRoot . 'page.php' . $sParam . '"'
                         . ' class="wc-sidebar-item' . ($bActive ? ' wc-active' : '') . '">'
                         . '<span class="sys-icon ' . $aItem['icon'] . ' col-green3"></span>'
                         . '<span>' . htmlspecialchars($aItem['label']) . '</span>'
                         . '</a>';
        }

        $sChildItems = '';
        if($iCurrentSpaceId) {
            $aChildren = $this->_getChildSpaces($iCurrentSpaceId);
            foreach($aChildren as $aChild) {
                $sChildItems .= '<a href="' . $sRoot . 'page.php?i=view-space-profile&profile_id=' . (int)$aChild['profile_id'] . '"'
                              . ' class="wc-sidebar-item wc-sub">'
                              . '<span class="wc-sidebar-item-dot"></span>'
                              . '<span>' . htmlspecialchars($aChild['title']) . '</span>'
                              . '</a>';
            }
        }

        $sWardSection = $this->_buildSection(_t('_sa_ward_councilor_sidebar_ward_functions'), $sWardItems, true);
        $sChildSection = $sChildItems ? $this->_buildSection(_t('_sa_ward_councilor_sidebar_child_spaces'), $sChildItems, true) : '';

        $sJs = '<script>'
             . 'document.querySelectorAll(".wc-sidebar-section-header").forEach(function(h){'
             . 'h.addEventListener("click",function(){h.parentElement.classList.toggle("wc-open");});'
             . '});'
             . '</script>';

        return '<div class="wc-sidebar">' . $sWardSection . $sChildSection . '</div>' . $sJs;
    }

    protected function _buildSection($sTitle, $sItemsHtml, $bOpen = true)
    {
        $sOpen = $bOpen ? ' wc-open' : '';
        return '<div class="wc-sidebar-section' . $sOpen . '">'
             . '<div class="wc-sidebar-section-header">'
             . '<span class="wc-sidebar-section-title">' . htmlspecialchars($sTitle) . '</span>'
             . '<span class="sys-icon chevron-down wc-sidebar-chevron"></span>'
             . '</div>'
             . '<div class="wc-sidebar-items">' . $sItemsHtml . '</div>'
             . '</div>';
    }

    protected function _getChildSpaces($iSpaceId)
    {
        $oDb  = BxDolDb::getInstance();
        $aRows = $oDb->getAll(
            $oDb->prepare(
                "SELECT d.id, d.space_name AS title, p.id AS profile_id
                 FROM bx_spaces_data d
                 JOIN sys_profiles p ON p.content_id = d.id AND p.type = 'bx_spaces'
                 WHERE d.parent_space = ? AND d.status = 'active' AND d.status_admin = 'active'
                 ORDER BY d.space_name ASC LIMIT 20",
                $iSpaceId
            )
        );
        return $aRows ? $aRows : array();
    }

    protected function _isCouncilorContext()
    {
        if(!isLogged()) return false;
        $iAccountId = getLoggedId();
        $oDb        = BxDolDb::getInstance();
        $iPersonPid = (int)$oDb->getOne(
            $oDb->prepare("SELECT id FROM sys_profiles WHERE account_id=? AND type='bx_persons' AND status='active' LIMIT 1", $iAccountId)
        );
        if(!$iPersonPid) return false;
        $iLevel = (int)$oDb->getOne(
            $oDb->prepare("SELECT IDLevel FROM sys_acl_levels_members WHERE IDMember=? LIMIT 1", $iPersonPid)
        );
        return in_array($iLevel, array(7, 8, 10, 12));
    }
}
