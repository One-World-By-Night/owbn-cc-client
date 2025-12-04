<?php

/**
 * OWBN-CC-Client Coordinators List Render
 * 
 * @package OWBN-CC-Client
 * @version 1.1.0
 */

defined('ABSPATH') || exit;

/**
 * Render coordinators list.
 *
 * @param array $coordinators List of coordinator data
 * @return string HTML output
 */
function ccc_render_coordinators_list(array $coordinators): string
{
    if (empty($coordinators)) {
        return '<p class="ccc-no-results">' . esc_html__('No coordinators found.', 'owbn-cc-client') . '</p>';
    }

    // Group by coordinator_type
    $groups = [
        'Administrative' => [],
        'Genre'          => [],
        'Clan'           => [],
    ];

    foreach ($coordinators as $coordinator) {
        $type = $coordinator['coordinator_type'] ?? '';

        // Default to Genre if empty or unknown
        if (!isset($groups[$type])) {
            $type = 'Genre';
        }

        $groups[$type][] = $coordinator;
    }

    // Sort each group alphabetically by title
    foreach ($groups as $type => &$group) {
        usort($group, function ($a, $b) {
            $titleA = $a['title'] ?? $a['coordinator_title'] ?? '';
            $titleB = $b['title'] ?? $b['coordinator_title'] ?? '';
            return strcasecmp($titleA, $titleB);
        });
    }
    unset($group);

    $detail_page_id = get_option(ccc_option_name('coordinators_detail_page'), 0);
    $base_url = $detail_page_id ? get_permalink($detail_page_id) : '';

    ob_start();
?>
    <div class="ccc-coordinators-list">
        <?php foreach ($groups as $type => $group) : ?>
            <?php if (!empty($group)) : ?>
                <div class="ccc-coord-group">
                    <div class="ccc-coord-group-header">
                        <?php echo esc_html($type); ?>
                    </div>

                    <div class="ccc-list-header">
                        <div class="ccc-col-office"><?php esc_html_e('Office', 'owbn-cc-client'); ?></div>
                        <div class="ccc-col-coordinator"><?php esc_html_e('Coordinator', 'owbn-cc-client'); ?></div>
                        <div class="ccc-col-email"><?php esc_html_e('Contact', 'owbn-cc-client'); ?></div>
                    </div>

                    <?php foreach ($group as $coordinator) : ?>
                        <?php echo ccc_render_coordinator_row($coordinator, $base_url); ?>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        <?php endforeach; ?>
    </div>
<?php
    return ob_get_clean();
}

/**
 * Render single coordinator row.
 *
 * @param array  $coordinator Coordinator data
 * @param string $base_url    Base URL for detail page
 * @return string HTML output
 */
function ccc_render_coordinator_row(array $coordinator, string $base_url): string
{
    $slug = $coordinator['slug'] ?? '';
    $title = $coordinator['title'] ?? $coordinator['coordinator_title'] ?? __('Untitled', 'owbn-cc-client');
    $url = $base_url ? add_query_arg('slug', $slug, $base_url) : '#';

    // Coordinator info
    $coord_info = $coordinator['coord_info'] ?? [];
    $name = $coord_info['display_name'] ?? '';
    $email = $coord_info['display_email'] ?? '';

    ob_start();
?>
    <div class="ccc-list-row">
        <div class="ccc-col-office">
            <a href="<?php echo esc_url($url); ?>"><?php echo esc_html($title); ?></a>
        </div>
        <div class="ccc-col-coordinator" data-label="<?php esc_attr_e('Coordinator', 'owbn-cc-client'); ?>">
            <?php echo esc_html($name ?: '—'); ?>
        </div>
        <div class="ccc-col-email" data-label="<?php esc_attr_e('Contact', 'owbn-cc-client'); ?>">
            <?php if ($email) : ?>
                <a href="mailto:<?php echo esc_attr($email); ?>"><?php echo esc_html($email); ?></a>
            <?php else : ?>
                —
            <?php endif; ?>
        </div>
    </div>
<?php
    return ob_get_clean();
}
