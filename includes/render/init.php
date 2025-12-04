<?php

/**
 * OWBN-CC-Client Render Init
 * 
 * @package OWBN-CC-Client
 * @version 1.1.0
 */

defined('ABSPATH') || exit;

// Render files will be loaded here
require_once __DIR__ . '/data-fetch.php';
require_once __DIR__ . '/render-helpers.php';
// Lists
require_once __DIR__ . '/render-chronicles-list.php';
require_once __DIR__ . '/render-coordinators-list.php';
// Details
require_once __DIR__ . '/render-chronicle-detail.php';
require_once __DIR__ . '/render-coordinator-detail.php';
