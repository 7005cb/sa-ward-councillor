<?php 
/**
 * Copyright (c) South Africa Community
 * 
 * @defgroup    SupportScheme Community Support Scheme module
 * @ingroup     SAModules
 *
 * @{
 */

$aConfig = array(
    /**
     * Main Section.
     */
    'type' => BX_DOL_MODULE_TYPE_MODULE,
    'name' => 'sa_support_scheme',
    'title' => 'Community Support Scheme',
    'note' => 'Local fundraising platform for South African communities - connecting those in need with willing donors',
    'version' => '1.0.0',
    'vendor' => 'SA Community',
    'product_url' => 'https://github.com/sa-community/support-scheme',
    'update_url' => '',
    
    'compatible_with' => array(
        '14.0.x'
    ),

    /**
     * 'home_dir' and 'home_uri' - should be unique. Don't use spaces in 'home_uri' and the other special chars.
     */
    'home_dir' => 'sa/support_scheme/',
    'home_uri' => 'support_scheme',
    
    'db_prefix' => 'sa_support_scheme',
    'class_prefix' => 'SaSupportScheme',

    /**
     * Category for language keys.
     */
    'language_category' => 'Community Support Scheme',

    /**
     * Installation/Uninstallation Section.
     */
    'install' => array(
        'execute_sql' => 1,
        'update_languages' => 1,
    ),
    'uninstall' => array(
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
