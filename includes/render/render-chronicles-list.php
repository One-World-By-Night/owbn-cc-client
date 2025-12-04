<?php

/**
 * OWBN-CC-Client Chronicles List Render
 * 
 * @package OWBN-CC-Client
 * @version 1.1.0
 */

defined('ABSPATH') || exit;

/**
 * Render chronicles list.
 *
 * @param array $chronicles List of chronicle data
 * @return string HTML output
 */
function ccc_render_chronicles_list(array $chronicles): string
{
    if (empty($chronicles)) {
        return '<p class="ccc-no-results">' . esc_html__('No chronicles found.', 'owbn-cc-client') . '</p>';
    }

    // Sort by title ascending by default
    usort($chronicles, function ($a, $b) {
        return strcasecmp($a['title'] ?? '', $b['title'] ?? '');
    });

    $detail_page_id = get_option(ccc_option_name('chronicles_detail_page'), 0);
    $base_url = $detail_page_id ? get_permalink($detail_page_id) : '';

    ob_start();
?>
    <div class="ccc-chronicles-filters">
        <input type="text" id="ccc-filter-genres" class="ccc-filter-input" placeholder="<?php esc_attr_e('Filter Genres...', 'owbn-cc-client'); ?>" data-column="1">
        <input type="text" id="ccc-filter-region" class="ccc-filter-input" placeholder="<?php esc_attr_e('Filter Region...', 'owbn-cc-client'); ?>" data-column="2">
        <input type="text" id="ccc-filter-state" class="ccc-filter-input" placeholder="<?php esc_attr_e('Filter State...', 'owbn-cc-client'); ?>" data-column="3">
        <input type="text" id="ccc-filter-type" class="ccc-filter-input" placeholder="<?php esc_attr_e('Filter Type...', 'owbn-cc-client'); ?>" data-column="5">
        <button type="button" id="ccc-clear-filters" class="ccc-clear-filters"><?php esc_html_e('Clear', 'owbn-cc-client'); ?></button>
    </div>

    <div class="ccc-chronicles-list">
        <div class="ccc-list-header">
            <div class="ccc-col-title sort-asc"><?php esc_html_e('Chronicle', 'owbn-cc-client'); ?></div>
            <div class="ccc-col-genres"><?php esc_html_e('Genres', 'owbn-cc-client'); ?></div>
            <div class="ccc-col-region"><?php esc_html_e('Region', 'owbn-cc-client'); ?></div>
            <div class="ccc-col-state"><?php esc_html_e('State/Province', 'owbn-cc-client'); ?></div>
            <div class="ccc-col-city"><?php esc_html_e('City', 'owbn-cc-client'); ?></div>
            <div class="ccc-col-type"><?php esc_html_e('Type', 'owbn-cc-client'); ?></div>
            <div class="ccc-col-status"><?php esc_html_e('Status', 'owbn-cc-client'); ?></div>
        </div>

        <?php foreach ($chronicles as $chronicle) : ?>
            <?php echo ccc_render_chronicle_row($chronicle, $base_url); ?>
        <?php endforeach; ?>
    </div>

    <p class="ccc-no-results-filtered" style="display:none;"><?php esc_html_e('No chronicles match your filters.', 'owbn-cc-client'); ?></p>
<?php
    return ob_get_clean();
}

/**
 * Render single chronicle row.
 *
 * @param array  $chronicle Chronicle data
 * @param string $base_url  Base URL for detail page
 * @return string HTML output
 */
function ccc_render_chronicle_row(array $chronicle, string $base_url): string
{
    $slug = $chronicle['slug'] ?? $chronicle['chronicle_slug'] ?? '';
    $title = $chronicle['title'] ?? __('Untitled', 'owbn-cc-client');
    $url = $base_url ? add_query_arg('slug', $slug, $base_url) : '#';

    // Location fields
    $ooc = $chronicle['ooc_locations'] ?? [];
    $state = $ooc['region'] ?? '';
    $city = $ooc['city'] ?? '';

    // Region
    $region = $chronicle['chronicle_region'] ?? '';

    // Genres
    $genres = $chronicle['genres'] ?? [];
    $genres_display = is_array($genres) ? implode(', ', $genres) : $genres;

    // Type
    $game_type = $chronicle['game_type'] ?? '';

    // Status flags
    $status = ccc_format_status($chronicle);

    ob_start();
?>
    <div class="ccc-list-row">
        <div class="ccc-col-title">
            <a href="<?php echo esc_url($url); ?>"><?php echo esc_html($title); ?></a>
        </div>
        <div class="ccc-col-genres" data-label="<?php esc_attr_e('Genres', 'owbn-cc-client'); ?>">
            <?php echo esc_html($genres_display ?: '—'); ?>
        </div>
        <div class="ccc-col-region" data-label="<?php esc_attr_e('Region', 'owbn-cc-client'); ?>">
            <?php echo esc_html($region ?: '—'); ?>
        </div>
        <div class="ccc-col-state" data-label="<?php esc_attr_e('State/Province', 'owbn-cc-client'); ?>">
            <?php echo esc_html($state ?: '—'); ?>
        </div>
        <div class="ccc-col-city" data-label="<?php esc_attr_e('City', 'owbn-cc-client'); ?>">
            <?php echo esc_html($city ?: '—'); ?>
        </div>
        <div class="ccc-col-type" data-label="<?php esc_attr_e('Type', 'owbn-cc-client'); ?>">
            <?php echo esc_html($game_type ?: '—'); ?>
        </div>
        <div class="ccc-col-status" data-label="<?php esc_attr_e('Status', 'owbn-cc-client'); ?>">
            <?php echo esc_html($status ?: '—'); ?>
        </div>
    </div>
<?php
    return ob_get_clean();
}

/**
 * Format status flags.
 *
 * @param array $chronicle Chronicle data
 * @return string Status text
 */
function ccc_format_status(array $chronicle): string
{
    $flags = [];

    if (!empty($chronicle['chronicle_probationary']) && $chronicle['chronicle_probationary'] !== '0') {
        $flags[] = __('Probationary', 'owbn-cc-client');
    }

    if (!empty($chronicle['chronicle_satellite']) && $chronicle['chronicle_satellite'] !== '0') {
        $flags[] = __('Satellite', 'owbn-cc-client');
    }

    if (empty($flags)) {
        $flags[] = __('Full Member', 'owbn-cc-client');
    }

    return implode(', ', $flags);
}
