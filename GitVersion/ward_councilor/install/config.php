<?php 
/**
 * Copyright (c) South Africa Community
 * 
 * @defgroup    WardCouncilor Ward Councilor Portal module
 * @ingroup     SAModules
 *
 * @{
 */

$aConfig = array(
        /**
         * Main Section.
         */
        'type' => BX_DOL_MODULE_TYPE_MODULE,
    'name' => 'sa_ward_councilor',
        'title' => 'Ward Councilor Portal',
    'note' => 'Ward Councilor Portal for South African communities - service requests, meetings, announcements, and citizen engagement',
        'version' => '1.0.0',
        'vendor' => 'SA Community',
    'product_url' => 'https://github.com/sa-community/ward-councilor',
        'update_url' => '',
        
        'compatible_with' => array(
        '14.0.x'
    ),

    /**
         * 'home_dir' and 'home_uri' - should be unique. Don't use spaces in 'home_uri' and the other special chars.
         */
        'home_dir' => 'sa/ward_councilor/',
        'home_uri' => 'ward_councilor',
        
        'db_prefix' => 'sa_ward_councilor',
        'class_prefix' => 'SaWardCouncilor',

        /**
         * Category for language keys.
         */
        'language_category' => 'Ward Councilor Portal',

        /**
         * Installation/Uninstallation Section.
         */
        'install' => array(
                'execute_sql' => 1,
                'update_languages' => 1,
        ),
        'uninstall' => array (
                'execute_sql' => 1,
                'update_languages' => 1,
    ),
    'enable' => array(
        'execute_sql' => 1,
        'recompile_global_paramaters' => 1,
        'clear_db_cache' => 1,
    ),
    'disable' => array(
        'execute_sql' => 1,
        'recompile_global_paramaters' => 1,
        'clear_db_cache' => 1,
    ),

    /**
         * Dependencies Section
         */
        'dependencies' => array(
    ),
);

/** @} */
