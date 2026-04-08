<?php
/**
 * Taxonomy Helper Class
 * Handles taxonomy-related operations for the search
 */

if (!defined('ABSPATH')) {
    exit;
}

class DGE_Buscador_Taxonomy_Helper {

    /**
     * Get all taxonomies associated with a CPT
     */
    public static function get_taxonomies_for_cpt($post_type) {
        $taxonomies = get_object_taxonomies($post_type, 'objects');
        $result = array();

        foreach ($taxonomies as $tax) {
            if ($tax->public && !$tax->_builtin) {
                $result[$tax->name] = $tax->labels->singular_name;
            }
        }

        return $result;
    }

    /**
     * Get all terms for a taxonomy
     */
    public static function get_terms_for_taxonomy($taxonomy, $args = array()) {
        $defaults = array(
            'hide_empty' => false,
            'orderby'    => 'name',
            'order'      => 'ASC',
        );

        $args = wp_parse_args($args, $defaults);
        $terms = get_terms($taxonomy, $args);

        if (is_wp_error($terms)) {
            return array();
        }

        $result = array();
        foreach ($terms as $term) {
            $result[] = array(
                'id'    => $term->term_id,
                'name'  => $term->name,
                'slug'  => $term->slug,
                'count' => $term->count,
            );
        }

        return $result;
    }

    /**
     * Get term names for a post
     */
    public static function get_post_terms($post_id, $taxonomies) {
        $result = array();

        foreach ((array) $taxonomies as $tax) {
            $terms = get_the_terms($post_id, $tax);
            if (!empty($terms) && !is_wp_error($terms)) {
                foreach ($terms as $term) {
                    $result[$tax][] = array(
                        'id'   => $term->term_id,
                        'name' => $term->name,
                        'slug' => $term->slug,
                    );
                }
            }
        }

        return $result;
    }

    /**
     * Get all public custom taxonomies
     */
    public static function get_all_custom_taxonomies() {
        $taxonomies = get_taxonomies(array(
            'public'   => true,
            '_builtin' => false,
        ), 'objects');

        return $taxonomies;
    }
}
