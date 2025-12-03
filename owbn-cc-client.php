<?php

/**
 * Plugin Name: OWBN CC Client
 * Plugin URI: https://github.com/One-World-By-Night/owbn-cc-client
 * Description: Embeddable client for fetching and displaying chronicle/coordinator data from remote or local OWBN Chronicle Plugin instances.
 * Version: 1.0.0
 * Author: greghacke
 * Author URI: https://www.owbn.net
 * Text Domain: owbn-cc-client
 * License: GPL-2.0-or-later
 */

defined('ABSPATH') || exit;

define('CCC_PLUGIN_URL', plugin_dir_url(__FILE__));

require_once plugin_dir_path(__FILE__) . 'includes/activation.php';
register_activation_hook(__FILE__, 'ccc_create_default_pages');

/**
 * -----------------------------------------------------------------------------
 * LOAD INSTANCE-SPECIFIC PREFIX
 * File must define: define('CCC_PREFIX', 'YOURSITE');
 * File must define: define('CCC_LABEL', 'Your Site Label');
 * Location: owbn-cc-client/prefix.php
 * -----------------------------------------------------------------------------
 */
$prefix_file = __DIR__ . '/prefix.php';

if (!file_exists($prefix_file)) {
    wp_die(
        esc_html__('owbn-cc-client requires a prefix.php file that defines CCC_PREFIX.', 'owbn-cc-client'),
        esc_html__('Missing File: prefix.php', 'owbn-cc-client'),
        ['response' => 500]
    );
}

require_once $prefix_file;

if (!defined('CCC_PREFIX')) {
    wp_die(
        esc_html__('owbn-cc-client requires CCC_PREFIX to be defined in prefix.php.', 'owbn-cc-client'),
        esc_html__('Missing Constant: CCC_PREFIX', 'owbn-cc-client'),
        ['response' => 500]
    );
}

if (!defined('CCC_LABEL')) {
    wp_die(
        esc_html__('owbn-cc-client requires CCC_LABEL to be defined in prefix.php.', 'owbn-cc-client'),
        esc_html__('Missing Constant: CCC_LABEL', 'owbn-cc-client'),
        ['response' => 500]
    );
}

// Build computed constant prefix: e.g., 'MYSITE_CCC_'
$prefix = strtoupper(preg_replace('/[^A-Z0-9]/i', '', CCC_PREFIX)) . '_CCC_';

// Define path-related constants
if (!defined($prefix . 'FILE')) {
    define($prefix . 'FILE', __FILE__);
}
if (!defined($prefix . 'DIR')) {
    define($prefix . 'DIR', plugin_dir_path(__FILE__));
}
if (!defined($prefix . 'URL')) {
    define($prefix . 'URL', plugin_dir_url(__FILE__));
}
if (!defined($prefix . 'VERSION')) {
    define($prefix . 'VERSION', '1.0.0');
}
if (!defined($prefix . 'TEXTDOMAIN')) {
    define($prefix . 'TEXTDOMAIN', 'owbn-cc-client');
}
if (!defined($prefix . 'ASSETS_URL')) {
    define($prefix . 'ASSETS_URL', constant($prefix . 'URL') . 'includes/assets/');
}
if (!defined($prefix . 'CSS_URL')) {
    define($prefix . 'CSS_URL', constant($prefix . 'ASSETS_URL') . 'css/');
}
if (!defined($prefix . 'JS_URL')) {
    define($prefix . 'JS_URL', constant($prefix . 'ASSETS_URL') . 'js/');
}

// Bootstrap the client module
require_once constant($prefix . 'DIR') . 'includes/init.php';

// Enqueue frontend assets when shortcode is present
add_action('wp_enqueue_scripts', function () {
    global $post;

    if (!is_a($post, 'WP_Post') || !has_shortcode($post->post_content, 'cc-client')) {
        return;
    }

    wp_enqueue_style('ccc-tables', CCC_PLUGIN_URL . 'css/ccc-tables.css', [], '1.0.0');
    wp_enqueue_style('ccc-client', CCC_PLUGIN_URL . 'css/ccc-client.css', ['ccc-tables'], '1.0.0');
    wp_enqueue_script('ccc-tables', CCC_PLUGIN_URL . 'js/ccc-tables.js', [], '1.0.0', true);
});
