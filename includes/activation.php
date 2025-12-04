<?php

/**
 * OWBN-CC-Client Activation
 * 
 * @package OWBN-CC-Client
 * @version 1.1.0
 */

defined('ABSPATH') || exit;

/**
 * Plugin activation - create default pages.
 */
function ccc_create_default_pages()
{
    $pages = [
        'chronicles_list_page' => [
            'title'   => __('Chronicles', 'owbn-cc-client'),
            'content' => '[cc-client type="chronicle-list"]',
        ],
        'chronicles_detail_page' => [
            'title'   => __('Chronicle Detail', 'owbn-cc-client'),
            'content' => '[cc-client type="chronicle-detail"]',
        ],
        'coordinators_list_page' => [
            'title'   => __('Coordinators', 'owbn-cc-client'),
            'content' => '[cc-client type="coordinator-list"]',
        ],
        'coordinators_detail_page' => [
            'title'   => __('Coordinator Detail', 'owbn-cc-client'),
            'content' => '[cc-client type="coordinator-detail"]',
        ],
    ];

    foreach ($pages as $option_key => $page_data) {
        // Skip if page already set
        $existing_id = get_option(ccc_option_name($option_key), 0);
        if ($existing_id && get_post_status($existing_id)) {
            continue;
        }

        // Create page
        $page_id = wp_insert_post([
            'post_title'   => $page_data['title'],
            'post_content' => $page_data['content'],
            'post_status'  => 'publish',
            'post_type'    => 'page',
        ]);

        if ($page_id && !is_wp_error($page_id)) {
            update_option(ccc_option_name($option_key), $page_id);
        }
    }
}
