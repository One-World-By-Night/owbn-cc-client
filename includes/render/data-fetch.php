<?php

/**
 * OWBN-Client Data Fetch Functions
 * location: includes/render/data-fetch.php
 * @package OWBN-Client
 * @version 2.0.0
 */

defined('ABSPATH') || exit;

// ══════════════════════════════════════════════════════════════════════════════
// DATA FETCH
// ══════════════════════════════════════════════════════════════════════════════

function owc_fetch_list(string $route)
{
    $mode = get_option(owc_option_name($route . '_mode'), 'local');

    if ($mode === 'local') {
        return owc_fetch_local_list($route);
    }
    return owc_fetch_remote_list($route);
}

function owc_fetch_detail(string $route, $identifier)
{
    $mode = get_option(owc_option_name($route . '_mode'), 'local');

    if ($mode === 'local') {
        return owc_fetch_local_detail($route, $identifier);
    }
    return owc_fetch_remote_detail($route, $identifier);
}

function owc_fetch_territories_by_slug(string $slug)
{
    $mode = get_option(owc_option_name('territories_mode'), 'local');

    if ($mode === 'local') {
        return owc_fetch_local_territories_by_slug($slug);
    }
    return owc_fetch_remote_territories_by_slug($slug);
}

// ══════════════════════════════════════════════════════════════════════════════
// LOCAL FETCH
// ══════════════════════════════════════════════════════════════════════════════

function owc_fetch_local_list(string $route)
{
    $func_map = [
        'chronicles'   => 'owbn_api_get_chronicles',
        'coordinators' => 'owbn_api_get_coordinators',
        'territories'  => 'owbn_tm_api_get_territories',
    ];

    $func = $func_map[$route] ?? null;

    if (!$func || !function_exists($func)) {
        return ['error' => 'Local source not available'];
    }

    $request = new WP_REST_Request('POST');
    $response = $func($request);

    if (is_wp_error($response)) {
        return ['error' => $response->get_error_message()];
    }

    return $response->get_data();
}

function owc_fetch_local_detail(string $route, $identifier)
{
    $func_map = [
        'chronicles'   => 'owbn_api_get_chronicle_detail',
        'coordinators' => 'owbn_api_get_coordinator_detail',
        'territories'  => 'owbn_tm_api_get_territory',
    ];

    $func = $func_map[$route] ?? null;

    if (!$func || !function_exists($func)) {
        return ['error' => 'Local source not available'];
    }

    $request = new WP_REST_Request('POST');
    $request->set_header('Content-Type', 'application/json');

    // Territories use ID, others use slug
    if ($route === 'territories') {
        $request->set_body(wp_json_encode(['id' => (int) $identifier]));
    } else {
        $request->set_body(wp_json_encode(['slug' => $identifier]));
    }

    $response = $func($request);

    return is_wp_error($response) ? ['error' => $response->get_error_message()] : $response->get_data();
}

function owc_fetch_local_territories_by_slug(string $slug)
{
    if (!function_exists('owbn_tm_api_get_territories_by_slug')) {
        return ['error' => 'Local source not available'];
    }

    $request = new WP_REST_Request('POST');
    $request->set_header('Content-Type', 'application/json');
    $request->set_body(wp_json_encode(['slug' => $slug]));

    $response = owbn_tm_api_get_territories_by_slug($request);

    return is_wp_error($response) ? ['error' => $response->get_error_message()] : $response->get_data();
}

// ══════════════════════════════════════════════════════════════════════════════
// REMOTE FETCH
// ══════════════════════════════════════════════════════════════════════════════

function owc_fetch_remote_list(string $route)
{
    $url = get_option(owc_option_name($route . '_url'), '');
    $key = get_option(owc_option_name($route . '_api_key'), '');

    if (empty($url)) {
        return ['error' => 'Remote URL not configured'];
    }

    $response = wp_remote_post(trailingslashit($url) . $route, [
        'timeout' => 15,
        'headers' => [
            'Content-Type' => 'application/json',
            'x-api-key'    => $key,
        ],
        'body' => wp_json_encode([]),
    ]);

    if (is_wp_error($response)) {
        return ['error' => $response->get_error_message()];
    }

    return json_decode(wp_remote_retrieve_body($response), true) ?: [];
}

function owc_fetch_remote_detail(string $route, $identifier)
{
    $url = get_option(owc_option_name($route . '_url'), '');
    $key = get_option(owc_option_name($route . '_api_key'), '');

    if (empty($url)) {
        return ['error' => 'Remote URL not configured'];
    }

    $endpoint_map = [
        'chronicles'   => 'chronicle-detail',
        'coordinators' => 'coordinator-detail',
        'territories'  => 'territory',
    ];

    $endpoint = $endpoint_map[$route] ?? $route . '-detail';

    // Territories use ID, others use slug
    if ($route === 'territories') {
        $body = ['id' => (int) $identifier];
    } else {
        $body = ['slug' => $identifier];
    }

    $response = wp_remote_post(trailingslashit($url) . $endpoint, [
        'timeout' => 15,
        'headers' => [
            'Content-Type' => 'application/json',
            'x-api-key'    => $key,
        ],
        'body' => wp_json_encode($body),
    ]);

    if (is_wp_error($response)) {
        return ['error' => $response->get_error_message()];
    }

    return json_decode(wp_remote_retrieve_body($response), true) ?: ['error' => 'Not found'];
}

function owc_fetch_remote_territories_by_slug(string $slug)
{
    $url = get_option(owc_option_name('territories_url'), '');
    $key = get_option(owc_option_name('territories_api_key'), '');

    if (empty($url)) {
        return ['error' => 'Remote URL not configured'];
    }

    $response = wp_remote_post(trailingslashit($url) . 'territories-by-slug', [
        'timeout' => 15,
        'headers' => [
            'Content-Type' => 'application/json',
            'x-api-key'    => $key,
        ],
        'body' => wp_json_encode(['slug' => $slug]),
    ]);

    if (is_wp_error($response)) {
        return ['error' => $response->get_error_message()];
    }

    return json_decode(wp_remote_retrieve_body($response), true) ?: [];
}
