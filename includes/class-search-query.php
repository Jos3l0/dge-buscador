<?php
/**
 * Search Query Class
 * Handles building and executing search queries
 */

if (!defined('ABSPATH')) {
    exit;
}

class DGE_Buscador_Search_Query {

    /**
     * Perform a search
     */
    public function search($search, $post_type, $taxonomies = array(), $sort = 'date_desc', $page = 1, $per_page = 12) {
        // Build WP_Query arguments
        $args = $this->build_args($search, $post_type, $taxonomies, $sort, $page, $per_page);

        // Execute query
        $query = new WP_Query($args);

        // Format results
        $results = $this->format_results($query, $page, $per_page);

        return $results;
    }

    /**
     * Build WP_Query arguments
     */
    private function build_args($search, $post_type, $taxonomies, $sort, $page, $per_page) {
        $args = array(
            'post_type'      => $post_type,
            'post_status'    => 'publish',
            'posts_per_page'  => $per_page,
            'paged'          => $page,
            's'              => $search,
        );

        // Sort
        $sort_args = $this->get_sort_args($sort);
        $args = array_merge($args, $sort_args);

        // Taxonomy filters
        if (!empty($taxonomies)) {
            $tax_query = $this->build_tax_query($taxonomies);
            if (!empty($tax_query)) {
                $args['tax_query'] = $tax_query;
            }
        }

        return $args;
    }

    /**
     * Build taxonomy query
     */
    private function build_tax_query($taxonomies) {
        $tax_query = array('relation' => 'AND');

        foreach ($taxonomies as $tax => $term_ids) {
            if (empty($term_ids)) {
                continue;
            }

            // Ensure term_ids is an array
            if (!is_array($term_ids)) {
                $term_ids = array($term_ids);
            }

            // Filter out empty values
            $term_ids = array_filter(array_map('absint', $term_ids));

            if (!empty($term_ids)) {
                $tax_query[] = array(
                    'taxonomy' => $tax,
                    'field'    => 'term_id',
                    'terms'    => $term_ids,
                    'operator' => 'IN',
                );
            }
        }

        // Remove relation if no valid tax queries
        if (count($tax_query) === 1) {
            return array();
        }

        return $tax_query;
    }

    /**
     * Get sort arguments
     */
    private function get_sort_args($sort) {
        $sort_map = array(
            'date_desc'   => array('orderby' => 'date', 'order' => 'DESC'),
            'date_asc'    => array('orderby' => 'date', 'order' => 'ASC'),
            'title_asc'   => array('orderby' => 'title', 'order' => 'ASC'),
            'title_desc'  => array('orderby' => 'title', 'order' => 'DESC'),
            'modified_desc' => array('orderby' => 'modified', 'order' => 'DESC'),
            'modified_asc'  => array('orderby' => 'modified', 'order' => 'ASC'),
            'rand'        => array('orderby' => 'rand'),
        );

        return isset($sort_map[$sort]) ? $sort_map[$sort] : $sort_map['date_desc'];
    }

    /**
     * Format query results
     */
    private function format_results($query, $page, $per_page) {
        $posts = array();

        if ($query->have_posts()) {
            while ($query->have_posts()) {
                $query->the_post();
                $post = $this->format_post(get_post());
                $posts[] = $post;
            }
            wp_reset_postdata();
        }

        return array(
            'posts'        => $posts,
            'total'        => (int) $query->found_posts,
            'pages'        => (int) $query->max_num_pages,
            'current_page' => (int) $page,
            'per_page'     => (int) $per_page,
        );
    }

    /**
     * Format a single post
     */
    private function format_post($post) {
        // Get featured image
        $thumbnail_id = get_post_thumbnail_id($post->ID);
        $thumbnail_url = $thumbnail_id ? wp_get_attachment_image_url($thumbnail_id, 'medium') : '';

        // Get excerpt
        $excerpt = $post->post_excerpt;
        if (empty($excerpt)) {
            $excerpt = wp_trim_words(strip_tags($post->post_content), 25, '...');
        }

        // Get taxonomies for this post
        $taxonomies = get_object_taxonomies($post->post_type, 'names');
        $terms = DGE_Buscador_Taxonomy_Helper::get_post_terms($post->ID, $taxonomies);

        return array(
            'id'         => $post->ID,
            'title'      => $post->post_title,
            'slug'       => $post->post_name,
            'excerpt'    => $excerpt,
            'url'        => get_permalink($post->ID),
            'date'       => get_the_date('d/m/Y', $post->ID),
            'thumbnail'  => $thumbnail_url,
            'terms'      => $terms,
        );
    }
}
