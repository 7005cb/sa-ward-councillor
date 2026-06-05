<?php
/**
 * Copyright (c) ModForge
 *
 * @defgroup    TimelineCleanup Timeline Cleanup Tool
 * @ingroup     SAModules
 *
 * @{
 */

$aConfig = array(
    /**
     * Main Section.
     */
    'type' => BX_DOL_MODULE_TYPE_MODULE,
    'name' => 'sa_timeline_cleanup',
    'title' => 'Timeline Cleanup Tool',
    'note' => 'Safely deletes old timeline posts and associated media files using the Timeline module API',
    'version' => '1.0.0',
    'vendor' => 'ModForge',
    'product_url' => '',
    'update_url' => '',

    'compatible_with' => array(
        '14.0.x'
    ),

    /**
     * 'home_dir' and 'home_uri' - should be unique.
     */
    'home_dir' => 'sa/timeline_cleanup/',
    'home_uri' => 'timeline_cleanup',

    'db_prefix' => 'sa_timeline_cleanup',
    'class_prefix' => 'SaTimelineCleanup',

    /**
     * Category for language keys.
     */
    'language_category' => 'Timeline Cleanup Tool',

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
        'execute_sql' => 0,
        'update_relations' => 0,
    ),
    'disable' => array(
        'execute_sql' => 0,
        'update_relations' => 0,
    ),

    /**
     * Dependencies Section
     */
    'dependencies' => array(
    ),
);

/** @} */
