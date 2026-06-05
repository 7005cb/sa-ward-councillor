<?php defined('BX_DOL') or die('hack attempt');


class SaTimelineCleanupDb extends BxDolModuleDb
{
    function __construct(&$oConfig)
    {
        parent::__construct($oConfig);
    }

    /**
     * Get distinct event types and their counts from bx_timeline_events
     */
    function getEventTypeStats()
    {
        return $this->getAll("SELECT DISTINCT `type`, COUNT(*) as `count` FROM `bx_timeline_events` GROUP BY `type`");
    }

    /**
     * Get the most recent event
     */
    function getMostRecentEvent()
    {
        return $this->getRow("SELECT `id`, `type`, `date`, FROM_UNIXTIME(`date`) as `readable_date` FROM `bx_timeline_events` ORDER BY `date` DESC LIMIT 1");
    }

    /**
     * Get the oldest event matching the given types and cutoff
     */
    function getOldestMatchingEvent($aEventTypes, $iCutoff)
    {
        $sTypeList = "'" . implode("','", $aEventTypes) . "'";
        return $this->getRow("SELECT `id`, FROM_UNIXTIME(`date`) as `date` FROM `bx_timeline_events` WHERE `type` IN ($sTypeList) ORDER BY `date` ASC LIMIT 1");
    }

    /**
     * Get the newest event matching the given types
     */
    function getNewestMatchingEvent($aEventTypes)
    {
        $sTypeList = "'" . implode("','", $aEventTypes) . "'";
        return $this->getRow("SELECT `id`, FROM_UNIXTIME(`date`) as `date` FROM `bx_timeline_events` WHERE `type` IN ($sTypeList) ORDER BY `date` DESC LIMIT 1");
    }

    /**
     * Count events matching the criteria
     */
    function countMatchingEvents($aEventTypes, $iCutoff)
    {
        $sTypeList = "'" . implode("','", $aEventTypes) . "'";
        return (int)$this->getOne("SELECT COUNT(*) FROM `bx_timeline_events` WHERE `type` IN ($sTypeList) AND `date` < ?", [$iCutoff]);
    }

    /**
     * Get a batch of old event IDs matching the criteria
     */
    function getOldEventIds($aEventTypes, $iCutoff, $iLimit)
    {
        $sTypeList = "'" . implode("','", $aEventTypes) . "'";
        $iLimit = (int)$iLimit;
        return $this->getColumn("SELECT `id` FROM `bx_timeline_events` WHERE `type` IN ($sTypeList) AND `date` < ? ORDER BY `date` ASC LIMIT $iLimit", [$iCutoff]);
    }

    /**
     * Get a single event by ID
     */
    function getEvent($iEventId)
    {
        return $this->getRow("SELECT * FROM `bx_timeline_events` WHERE `id` = ?", [(int)$iEventId]);
    }
}
