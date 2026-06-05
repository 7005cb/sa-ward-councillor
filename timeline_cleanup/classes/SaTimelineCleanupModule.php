<?php defined('BX_DOL') or die('hack attempt');


class SaTimelineCleanupModule extends BxDolModule
{
    function __construct(&$aModule)
    {
        parent::__construct($aModule);
    }

    /**
     * Check if current user is admin
     */
    protected function _isAdmin()
    {
        return isAdmin();
    }

    /**
     * Main service: Run the cleanup (called from request.php)
     */
    public function serviceRunCleanup()
    {
        if (!$this->_isAdmin()) {
            return MsgBox(_t('_sa_timeline_cleanup_access_denied'));
        }

        // Read settings from options
        $iDaysOld    = (int)getParam('sa_timeline_cleanup_days');
        $iBatch      = (int)getParam('sa_timeline_cleanup_batch');
        $bDryRun     = (bool)getParam('sa_timeline_cleanup_dry_run');
        $sTypesRaw   = getParam('sa_timeline_cleanup_event_types');
        $aEventTypes = array_filter(array_map('trim', explode(',', $sTypesRaw)));

        if ($iDaysOld <= 0)  $iDaysOld = 90;
        if ($iBatch <= 0)    $iBatch = 50;
        if (empty($aEventTypes)) $aEventTypes = array('timeline_common_post', 'timeline_common_repost');

        $iCutoff = time() - (86400 * $iDaysOld);

        // Load Timeline module
        $oTimeline = BxDolModule::getInstance('bx_timeline');
        if (!$oTimeline) {
            return MsgBox(_t('_sa_timeline_cleanup_module_not_found'));
        }

        $oDb = $this->_oDb;

        // ===== DIAGNOSTICS =====
        $sHtml = '<div class="sa-tlc-results">';
        $sHtml .= '<h3>' . _t('_sa_timeline_cleanup_diagnostics') . '</h3>';
        $sHtml .= '<table class="sa-tlc-table">';
        $sHtml .= '<tr><th>' . _t('_sa_timeline_cleanup_event_type') . '</th><th>' . _t('_sa_timeline_cleanup_count') . '</th></tr>';

        $aTypeStats = $oDb->getEventTypeStats();
        if (!empty($aTypeStats)) {
            foreach ($aTypeStats as $aType) {
                $sHtml .= '<tr><td>' . htmlspecialchars($aType['type']) . '</td><td>' . (int)$aType['count'] . '</td></tr>';
            }
        }
        $sHtml .= '</table>';

        // Date range
        $aOldest = $oDb->getOldestMatchingEvent($aEventTypes, $iCutoff);
        $aNewest = $oDb->getNewestMatchingEvent($aEventTypes);
        if ($aOldest && $aNewest) {
            $sHtml .= '<p>' . _t('_sa_timeline_cleanup_oldest') . ': ' . $aOldest['date'] . ' (ID: ' . $aOldest['id'] . ')<br>';
            $sHtml .= _t('_sa_timeline_cleanup_newest') . ': ' . $aNewest['date'] . ' (ID: ' . $aNewest['id'] . ')</p>';
        }

        // Matching count
        $iMatchCount = $oDb->countMatchingEvents($aEventTypes, $iCutoff);
        $sHtml .= '<p><strong>' . _t('_sa_timeline_cleanup_matching') . ': ' . $iMatchCount . '</strong></p>';
        $sHtml .= '<p>' . ($bDryRun ? _t('_sa_timeline_cleanup_dry_run_on') : _t('_sa_timeline_cleanup_dry_run_off')) . '</p>';
        $sHtml .= '</div>';

        if ($iMatchCount === 0) {
            $sHtml .= '<div class="sa-tlc-summary">' . _t('_sa_timeline_cleanup_no_events') . '</div>';
            return $sHtml;
        }

        // ===== PROCESS DELETIONS =====
        $aEventIds = $oDb->getOldEventIds($aEventTypes, $iCutoff, $iBatch);

        if (empty($aEventIds)) {
            $sHtml .= '<div class="sa-tlc-summary">' . _t('_sa_timeline_cleanup_no_events') . '</div>';
            return $sHtml;
        }

        $iDeleted = 0;
        $iFailed  = 0;
        $iSkipped = 0;
        $aLog     = array();

        foreach ($aEventIds as $iEventId) {
            $aEvent = $oDb->getEvent($iEventId);

            if (empty($aEvent)) {
                $iSkipped++;
                $aLog[] = array('status' => 'skipped', 'id' => $iEventId, 'msg' => 'Already deleted');
                continue;
            }

            if ($bDryRun) {
                $iDeleted++;
                $aLog[] = array(
                    'status'  => 'dry_run',
                    'id'      => $iEventId,
                    'owner'   => $aEvent['owner_id'],
                    'date'    => date('Y-m-d', $aEvent['date']),
                    'msg'     => '[DRY RUN] Would delete event ' . $iEventId . ' (owner: ' . $aEvent['owner_id'] . ')',
                );
                continue;
            }

            // ACTUAL DELETION via Timeline module API
            try {
                $bResult = $oTimeline->deleteEvent($aEvent);
                if ($bResult) {
                    $iDeleted++;
                    $aLog[] = array(
                        'status' => 'deleted',
                        'id'     => $iEventId,
                        'owner'  => $aEvent['owner_id'],
                        'msg'    => 'Deleted event ' . $iEventId,
                    );
                } else {
                    $iFailed++;
                    $aLog[] = array(
                        'status' => 'failed',
                        'id'     => $iEventId,
                        'msg'    => 'Failed to delete event ' . $iEventId,
                    );
                }
            } catch (Exception $e) {
                $iFailed++;
                $aLog[] = array(
                    'status' => 'error',
                    'id'     => $iEventId,
                    'msg'    => 'Exception: ' . $e->getMessage(),
                );
            }

            usleep(50000); // 0.05s delay between deletions
        }

        // ===== RESULTS TABLE =====
        $sHtml .= '<div class="sa-tlc-results">';
        $sHtml .= '<h3>' . _t('_sa_timeline_cleanup_results') . '</h3>';
        $sHtml .= '<table class="sa-tlc-table">';
        $sHtml .= '<tr><th>Status</th><th>ID</th><th>Details</th></tr>';
        foreach ($aLog as $aEntry) {
            $sClass = '';
            if ($aEntry['status'] === 'deleted' || $aEntry['status'] === 'dry_run') $sClass = 'sa-tlc-ok';
            elseif ($aEntry['status'] === 'skipped') $sClass = 'sa-tlc-warn';
            else $sClass = 'sa-tlc-err';
            $sHtml .= '<tr class="' . $sClass . '">';
            $sHtml .= '<td>' . htmlspecialchars(ucfirst($aEntry['status'])) . '</td>';
            $sHtml .= '<td>' . (int)$aEntry['id'] . '</td>';
            $sHtml .= '<td>' . htmlspecialchars($aEntry['msg']) . '</td>';
            $sHtml .= '</tr>';
        }
        $sHtml .= '</table>';
        $sHtml .= '</div>';

        // ===== SUMMARY =====
        $sHtml .= '<div class="sa-tlc-summary">';
        $sHtml .= '<p><strong>' . _t('_sa_timeline_cleanup_processed') . ': ' . count($aEventIds) . '</strong><br>';
        $sHtml .= _t('_sa_timeline_cleanup_deleted') . ': ' . $iDeleted . '<br>';
        $sHtml .= _t('_sa_timeline_cleanup_failed') . ': ' . $iFailed . '<br>';
        $sHtml .= _t('_sa_timeline_cleanup_skipped') . ': ' . $iSkipped . '</p>';

        if ($bDryRun) {
            $sHtml .= '<p class="sa-tlc-warn">' . _t('_sa_timeline_cleanup_dry_run_complete') . '</p>';
        } else {
            $sHtml .= '<p class="sa-tlc-ok">' . _t('_sa_timeline_cleanup_live_complete') . '</p>';
        }
        $sHtml .= '</div>';

        return $sHtml;
    }

    /**
     * Service: Get the cleanup page HTML (settings form + run button)
     */
    public function serviceGetCleanupPage()
    {
        if (!$this->_isAdmin()) {
            return MsgBox(_t('_sa_timeline_cleanup_access_denied'));
        }

        $sHtml = '<div class="sa-tlc-container">';

        // Settings form
        $sHtml .= '<form method="post" action="' . BX_DOL_URL_ROOT . 'modules/sa/timeline_cleanup/request.php?action=run" id="sa-tlc-form">';
        $sHtml .= '<h2>' . _t('_sa_timeline_cleanup') . '</h2>';

        // Days
        $iDaysOld = (int)getParam('sa_timeline_cleanup_days');
        if ($iDaysOld <= 0) $iDaysOld = 90;
        $sHtml .= '<div class="sa-tlc-field"><label>' . _t('_sa_timeline_cleanup_days') . '</label>';
        $sHtml .= '<input type="number" name="days" value="' . $iDaysOld . '" min="1" max="3650" /></div>';

        // Batch
        $iBatch = (int)getParam('sa_timeline_cleanup_batch');
        if ($iBatch <= 0) $iBatch = 50;
        $sHtml .= '<div class="sa-tlc-field"><label>' . _t('_sa_timeline_cleanup_batch') . '</label>';
        $sHtml .= '<input type="number" name="batch" value="' . $iBatch . '" min="1" max="5000" /></div>';

        // Event types
        $sTypes = getParam('sa_timeline_cleanup_event_types');
        if (empty($sTypes)) $sTypes = 'timeline_common_post,timeline_common_repost';
        $sHtml .= '<div class="sa-tlc-field"><label>' . _t('_sa_timeline_cleanup_event_types') . '</label>';
        $sHtml .= '<input type="text" name="event_types" value="' . htmlspecialchars($sTypes) . '" style="width:100%" /></div>';

        // Dry run toggle
        $bDryRun = (bool)getParam('sa_timeline_cleanup_dry_run');
        $sHtml .= '<div class="sa-tlc-field"><label>';
        $sHtml .= '<input type="checkbox" name="dry_run" value="1"' . ($bDryRun ? ' checked' : '') . ' /> ';
        $sHtml .= _t('_sa_timeline_cleanup_dry_run') . '</label></div>';

        // Submit
        $sHtml .= '<div class="sa-tlc-field"><button type="submit" class="sa-tlc-btn" id="sa-tlc-submit">';
        $sHtml .= _t('_sa_timeline_cleanup_run') . '</button></div>';

        $sHtml .= '</form>';

        // Results area
        $sHtml .= '<div id="sa-tlc-results"></div>';
        $sHtml .= '</div>';

        // JS for AJAX submission
        $sHtml .= <<<JS
<script>
document.getElementById('sa-tlc-form').addEventListener('submit', function(e) {
    e.preventDefault();
    var form = this;
    var results = document.getElementById('sa-tlc-results');
    var btn = document.getElementById('sa-tlc-submit');
    btn.disabled = true;
    btn.textContent = 'Running...';
    results.innerHTML = '<p>Processing...</p>';
    var xhr = new XMLHttpRequest();
    xhr.open('POST', form.action, true);
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    xhr.onload = function() {
        btn.disabled = false;
        btn.textContent = 'Run Cleanup';
        results.innerHTML = xhr.responseText;
    };
    xhr.onerror = function() {
        btn.disabled = false;
        btn.textContent = 'Run Cleanup';
        results.innerHTML = '<p class="sa-tlc-err">Request failed</p>';
    };
    var params = new URLSearchParams(new FormData(form)).toString();
    xhr.send(params);
});
</script>
JS;

        return $sHtml;
    }
}
