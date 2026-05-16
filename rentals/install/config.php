<?php defined('BX_DOL') or die('hack attempt');

$aConfig = array(
    'type'              => BX_DOL_MODULE_TYPE_MODULE,
    'name'              => 'sa_rentals',
    'title'             => 'SA Rentals',
    'note'              => 'South African rental listings — rooms, houses, flats, backyard units.',
    'version'           => '1.0.0',
    'vendor'            => 'SA Modules',
    'product_url'       => 'https://example.co.za',
    'update_url'        => '',
    'compatible_with'   => array('14.0.x'),
    'home_dir'          => 'sa/rentals/',
    'home_uri'          => 'rentals',
    'db_prefix'         => 'sa_rentals',
    'class_prefix'      => 'SaRentals',
    'language_category' => 'SA Rentals',
    'install'   => array('execute_sql' => 1, 'update_languages' => 1),
    'uninstall' => array('execute_sql' => 1, 'update_languages' => 1),
    'enable'    => array('execute_sql' => 1, 'recompile_global_paramaters' => 1, 'clear_db_cache' => 1),
    'disable'   => array('execute_sql' => 1, 'recompile_global_paramaters' => 1, 'clear_db_cache' => 1),
    'dependencies' => array(),
    'relations' => array(
        'bx_timeline',
        'bx_notifications',
    ),
);