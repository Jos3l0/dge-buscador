<?php
/**
 * Shortcode Class
 * Handles shortcode attributes and rendering logic
 */

if (!defined('ABSPATH')) {
    exit;
}

class DGE_Buscador_Shortcode {

    /**
     * Default attributes
     */
    public static function get_defaults() {
        return array(
            'post_type'          => 'recurso',
            'taxonomias'         => '',
            'per_page'           => 12,
            'columns'            => 3,
            'show_search'        => 'true',
            'show_filters'       => 'true',
            'show_sort'          => 'true',
            'placeholder_search' => 'Search resources...',
        );
    }

    /**
     * Parse attributes
     */
    public static function parse_atts($atts) {
        $defaults = self::get_defaults();
        $atts = shortcode_atts($defaults, $atts, 'dge_buscador');

        // Convert string booleans
        $bool_atts = array('show_search', 'show_filters', 'show_sort');
        foreach ($bool_atts as $key) {
            $atts[$key] = filter_var($atts[$key], FILTER_VALIDATE_BOOLEAN);
        }

        // Convert integers
        $int_atts = array('per_page', 'columns');
        foreach ($int_atts as $key) {
            $atts[$key] = absint($atts[$key]);
        }

        return $atts;
    }

    /**
     * Validate taxonomy exists
     */
    public static function validate_taxonomy($taxonomy) {
        return taxonomy_exists($taxonomy);
    }

    /**
     * Get allowed sort options
     */
    public static function get_sort_options() {
        return array(
            'date_desc'   => __('Most recent', 'dge-buscador'),
            'date_asc'    => __('Oldest first', 'dge-buscador'),
            'title_asc'   => __('Title A-Z', 'dge-buscador'),
            'title_desc'  => __('Title Z-A', 'dge-buscador'),
            'rand'        => __('Random', 'dge-buscador'),
        );
    }
}
