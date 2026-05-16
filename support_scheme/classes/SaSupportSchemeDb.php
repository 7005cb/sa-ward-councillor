<?php defined('BX_DOL') or die('hack attempt');

bx_import('BxDolModuleDb');

class SaSupportSchemeDb extends BxDolModuleDb
{
    function __construct(&$oConfig) 
    {
        parent::__construct($oConfig);
    }

    /**
     * Get available spaces for campaign assignment
     */
    function getSpaces()
    {
        // Check if spaces table exists
        $sSql = "SHOW TABLES LIKE 'sys_spaces'";
        if(!$this->getOne($sSql)) {
            return array();
        }
        $sSql = "SELECT `id`, `title` FROM `sys_spaces` WHERE `status` = 'active' ORDER BY `title`";
        return $this->getAll($sSql);
    }

    /**
     * Get campaigns with filters
     */
    function getCampaigns($aParams = array())
    {
        $sWhere = " WHERE 1 ";
        $sOrder = " ORDER BY `created` DESC ";
        $sLimit = "";
        
        // Filter by status
        if(isset($aParams['status'])) {
            $sStatus = addslashes($aParams['status']);
            $sWhere .= " AND `status` = '$sStatus'";
        }
        
        // Filter by author
        if(isset($aParams['author_id'])) {
            $sWhere .= " AND `author_id` = " . (int)$aParams['author_id'];
        }
        
        // Filter by category
        if(isset($aParams['category'])) {
            $sCategory = addslashes($aParams['category']);
            $sWhere .= " AND `category` = '$sCategory'";
        }
        
        // Search filter
        if(isset($aParams['search'])) {
            $sSearch = addslashes($aParams['search']);
            $sWhere .= " AND (`title` LIKE '%$sSearch%' OR `description` LIKE '%$sSearch%')";
        }
        
        // Featured filter
        if(isset($aParams['featured'])) {
            $sWhere .= " AND `featured` = " . (int)$aParams['featured'];
        }
        
        // Urgent filter
        if(isset($aParams['urgent'])) {
            $sWhere .= " AND `urgent` = " . (int)$aParams['urgent'];
        }
        
        // Space filter
        if(isset($aParams['space_id'])) {
            $sWhere .= " AND `space_id` = " . (int)$aParams['space_id'];
        }
        
        // Limit
        if(isset($aParams['limit'])) {
            $sLimit = " LIMIT " . (int)$aParams['limit'];
        }
        
        $sSql = "SELECT * FROM `sa_support_scheme_campaigns` " . $sWhere . $sOrder . $sLimit;
        return $this->getAll($sSql);
    }

    /**
     * Get single campaign by ID
     */
    function getCampaign($iId)
    {
        $sSql = "SELECT * FROM `sa_support_scheme_campaigns` WHERE `id` = " . (int)$iId . " LIMIT 1";
        return $this->getRow($sSql);
    }

    /**
     * Add new campaign
     */
    function addCampaign($aData)
    {
        $sTitle = addslashes($aData['title']);
        $sDescription = addslashes($aData['description']);
        $sCategory = addslashes($aData['category']);
        $sBeneficiaryName = isset($aData['beneficiary_name']) ? addslashes($aData['beneficiary_name']) : '';
        $sEndDate = !empty($aData['end_date']) ? "'" . addslashes($aData['end_date']) . "'" : 'NULL';
        $sStatus = addslashes($aData['status']);
        $sCreated = addslashes($aData['created']);
        $fGoal = (float)$aData['goal_amount'];
        $iAuthor = (int)$aData['author_id'];
        $iSpaceId = isset($aData['space_id']) && $aData['space_id'] ? (int)$aData['space_id'] : 'NULL';
        $iUrgent = isset($aData['urgent']) ? (int)$aData['urgent'] : 0;
        $iFeatured = isset($aData['featured']) ? (int)$aData['featured'] : 0;
        
        $sSql = "INSERT INTO `sa_support_scheme_campaigns` 
            (`title`, `description`, `goal_amount`, `category`, `beneficiary_name`, 
             `end_date`, `author_id`, `space_id`, `urgent`, `featured`, `status`, `created`) 
            VALUES ('$sTitle', '$sDescription', $fGoal, '$sCategory', '$sBeneficiaryName', 
                    $sEndDate, $iAuthor, $iSpaceId, $iUrgent, $iFeatured, '$sStatus', '$sCreated')";
        
        return $this->query($sSql);
    }

    /**
     * Update campaign view count
     */
    function updateCampaignViews($iId)
    {
        return $this->query("UPDATE `sa_support_scheme_campaigns` SET `views` = `views` + 1 WHERE `id` = " . (int)$iId);
    }

    /**
     * Get donations for a campaign
     */
    function getDonations($iCampaignId, $iLimit = 10)
    {
        $sSql = "SELECT * FROM `sa_support_scheme_donations` 
                 WHERE `campaign_id` = " . (int)$iCampaignId . " 
                 AND `payment_status` = 'completed'
                 ORDER BY `created` DESC 
                 LIMIT " . (int)$iLimit;
        return $this->getAll($sSql);
    }

    /**
     * Add a donation
     */
    function addDonation($aData)
    {
        $iCampaignId = (int)$aData['campaign_id'];
        $iDonorId = $aData['donor_id'] ? (int)$aData['donor_id'] : 'NULL';
        $fAmount = (float)$aData['amount'];
        $sMessage = !empty($aData['message']) ? "'" . addslashes($aData['message']) . "'" : 'NULL';
        $iAnonymous = (int)$aData['anonymous'];
        $sStatus = addslashes($aData['payment_status']);
        $sCreated = addslashes($aData['created']);
        
        $sSql = "INSERT INTO `sa_support_scheme_donations` 
            (`campaign_id`, `donor_id`, `amount`, `message`, `anonymous`, `payment_status`, `created`) 
            VALUES ($iCampaignId, $iDonorId, $fAmount, $sMessage, $iAnonymous, '$sStatus', '$sCreated')";
        
        $iResult = $this->query($sSql);
        
        // Update campaign totals if payment completed
        if($iResult && $aData['payment_status'] == 'completed') {
            $this->query("UPDATE `sa_support_scheme_campaigns` 
                SET `current_amount` = `current_amount` + $fAmount,
                    `donations_count` = `donations_count` + 1
                WHERE `id` = $iCampaignId");
        }
        
        return $iResult;
    }

    /**
     * Get module statistics
     */
    function getStats()
    {
        $aStats = array(
            'active_campaigns' => 0,
            'total_raised' => 0,
            'total_donations' => 0
        );
        
        $sSql = "SELECT COUNT(*) FROM `sa_support_scheme_campaigns` WHERE `status` = 'active'";
        $aStats['active_campaigns'] = (int)$this->getOne($sSql);
        
        $sSql = "SELECT COALESCE(SUM(current_amount), 0) FROM `sa_support_scheme_campaigns`";
        $aStats['total_raised'] = (float)$this->getOne($sSql);
        
        $sSql = "SELECT COUNT(*) FROM `sa_support_scheme_donations` WHERE `payment_status` = 'completed'";
        $aStats['total_donations'] = (int)$this->getOne($sSql);
        
        return $aStats;
    }

    /**
     * Get categories with campaign counts
     */
    function getCategories()
    {
        $sSql = "SELECT `category`, COUNT(*) as count 
                 FROM `sa_support_scheme_campaigns` 
                 WHERE `status` = 'active' AND `category` IS NOT NULL 
                 GROUP BY `category` 
                 ORDER BY count DESC";
        return $this->getAll($sSql);
    }

    /**
     * Update campaign status
     */
    function updateCampaignStatus($iId, $sStatus)
    {
        $sStatus = addslashes($sStatus);
        return $this->query("UPDATE `sa_support_scheme_campaigns` SET `status` = '$sStatus' WHERE `id` = " . (int)$iId);
    }

    /**
     * Delete campaign
     */
    function deleteCampaign($iId)
    {
        // Delete donations first
        $this->query("DELETE FROM `sa_support_scheme_donations` WHERE `campaign_id` = " . (int)$iId);
        // Delete campaign
        return $this->query("DELETE FROM `sa_support_scheme_campaigns` WHERE `id` = " . (int)$iId);
    }
}
