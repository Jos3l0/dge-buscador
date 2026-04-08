<?php
/**
 * AJAX Handler Class
 * Handles AJAX requests (placeholder for future expansion)
 */

if (!defined('ABSPATH')) {
    exit;
}

class DGE_Buscador_AJAX_Handler {

    /**
     * Constructor
     */
    public function __construct() {
        // Future AJAX handlers can be added here
    }

    /**
     * Validate nonce
     */
    public static function validate_nonce($nonce) {
        return wp_verify_nonce($nonce, 'dge_buscador_nonce');
    }

    /**
     * Sanitize search input
     */
    public static function sanitize_search($search) {
        return sanitize_text_field($search);
    }

    /**
     * Sanitize taxonomy filters
     */
    public static function sanitize_taxonomies($taxonomies) {
        $sanitized = array();

        if (is_array($taxonomies)) {
            foreach ($taxonomies as $tax => $terms) {
                $tax = sanitize_key($tax);
                
                if (is_array($terms)) {
                    $sanitized[$tax] = array_filter(array_map('absint', $terms));
                } elseif (is_numeric($terms)) {
                    $sanitized[$tax] = array(absint($terms));
                }
            }
        }

        return $sanitized;
    }
}
