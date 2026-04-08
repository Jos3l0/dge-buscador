<?php
/**
 * Plugin Name: DGE Buscador
 * Plugin URI: https://example.com
 * Description: Buscador AJAX con filtros facetados para CPT recursos y futuros CPTs del Portal Educativo Mendoza
 * Version: 1.0.0
 * Requires at least: 6.0
 * Requires PHP: 7.4
 * Author: Equipo DGE
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: dge-buscador
 * Domain Path: /languages
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('DGE_BUSCADOR_VERSION', '1.0.0');
define('DGE_BUSCADOR_PATH', plugin_dir_path(__FILE__));
define('DGE_BUSCADOR_URL', plugin_dir_url(__FILE__));
define('DGE_BUSCADOR_ASSETS_URL', DGE_BUSCADOR_URL . 'assets/');

/**
 * Main Plugin Class
 */
final class DGE_Buscador {

    /**
     * Instance of the class
     */
    private static $instance = null;

    /**
     * Singleton pattern
     */
    public static function instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Constructor
     */
    private function __construct() {
        $this->init_hooks();
        $this->includes();
    }

    /**
     * Initialize hooks
     */
    private function init_hooks() {
        // Activation/Deactivation
        register_activation_hook(__FILE__, array($this, 'activate'));
        register_deactivation_hook(__FILE__, array($this, 'deactivate'));

        // Init
        add_action('init', array($this, 'init'));

        // Shortcode
        add_shortcode('dge_buscador', array($this, 'render_shortcode'));

        // AJAX
        add_action('wp_ajax_dge_buscador_search', array($this, 'ajax_search'));
        add_action('wp_ajax_nopriv_dge_buscador_search', array($this, 'ajax_search'));

        // Enqueue scripts
        add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));
    }

    /**
     * Include required files
     */
    private function includes() {
        require_once DGE_BUSCADOR_PATH . 'includes/class-ajax-handler.php';
        require_once DGE_BUSCADOR_PATH . 'includes/class-search-query.php';
        require_once DGE_BUSCADOR_PATH . 'includes/class-taxonomy-helper.php';
        require_once DGE_BUSCADOR_PATH . 'includes/class-shortcode.php';
    }

    /**
     * Plugin initialization
     */
    public function init() {
        load_plugin_textdomain('dge-buscador', false, dirname(plugin_basename(__FILE__)) . '/languages');
    }

    /**
     * Activation
     */
    public function activate() {
        // Flush rewrite rules
        flush_rewrite_rules();
    }

    /**
     * Deactivation
     */
    public function deactivate() {
        flush_rewrite_rules();
    }

    /**
     * Enqueue frontend scripts and styles
     */
    public function enqueue_scripts() {
        // CSS
        wp_enqueue_style(
            'dge-buscador',
            DGE_BUSCADOR_ASSETS_URL . 'css/frontend.css',
            array(),
            DGE_BUSCADOR_VERSION
        );

        // JS
        wp_enqueue_script(
            'dge-buscador',
            DGE_BUSCADOR_ASSETS_URL . 'js/frontend.js',
            array('jquery'),
            DGE_BUSCADOR_VERSION,
            true
        );

        // Pass data to JS
        wp_localize_script('dge-buscador', 'dgeBuscadorData', array(
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce'   => wp_create_nonce('dge_buscador_nonce'),
            'i18n'    => array(
                'search'        => __('Search', 'dge-buscador'),
                'filter'        => __('Filter', 'dge-buscador'),
                'clear'         => __('Clear', 'dge-buscador'),
                'loading'       => __('Loading...', 'dge-buscador'),
                'noResults'     => __('No results found', 'dge-buscador'),
                'resultsFound'  => __('results found', 'dge-buscador'),
                'showingResults' => __('Showing', 'dge-buscador'),
                'of'            => __('of', 'dge-buscador'),
                'sortBy'        => __('Sort by', 'dge-buscador'),
            )
        ));
    }

    /**
     * Render shortcode
     */
    public function render_shortcode($atts) {
        $atts = shortcode_atts(array(
            'post_type'          => 'recurso',
            'taxonomias'         => '',
            'per_page'           => 12,
            'columns'            => 3,
            'show_search'        => true,
            'show_filters'       => true,
            'show_sort'          => true,
            'placeholder_search' => __('Search resources...', 'dge-buscador'),
        ), $atts, 'dge_buscador');

        // Convert strings to booleans
        $atts['show_search']  = filter_var($atts['show_search'], FILTER_VALIDATE_BOOLEAN);
        $atts['show_filters'] = filter_var($atts['show_filters'], FILTER_VALIDATE_BOOLEAN);
        $atts['show_sort']    = filter_var($atts['show_sort'], FILTER_VALIDATE_BOOLEAN);

        // Get taxonomy data
        $taxonomies = DGE_Buscador_Taxonomy_Helper::get_taxonomies_for_cpt($atts['post_type']);
        
        // Get available terms for each taxonomy
        $terms_data = array();
        $requested_taxonomies = array_filter(array_map('trim', explode(',', $atts['taxonomias'])));
        
        foreach ($requested_taxonomies as $tax) {
            if (isset($taxonomies[$tax])) {
                $terms = DGE_Buscador_Taxonomy_Helper::get_terms_for_taxonomy($tax);
                if (!empty($terms)) {
                    $terms_data[$tax] = array(
                        'label' => $taxonomies[$tax],
                        'terms' => $terms
                    );
                }
            }
        }

        // Generate unique ID for this instance
        $instance_id = 'dge-buscador-' . uniqid();

        // Start output
        ob_start();
        ?>
        <div id="<?php echo esc_attr($instance_id); ?>" 
             class="dge-buscador" 
             data-post_type="<?php echo esc_attr($atts['post_type']); ?>"
             data-per_page="<?php echo esc_attr($atts['per_page']); ?>"
             data-columns="<?php echo esc_attr($atts['columns']); ?>"
             data-show_search="<?php echo esc_attr($atts['show_search'] ? '1' : '0'); ?>"
             data-show_filters="<?php echo esc_attr($atts['show_filters'] ? '1' : '0'); ?>"
             data-show_sort="<?php echo esc_attr($atts['show_sort'] ? '1' : '0'); ?>"
             data-taxonomias="<?php echo esc_attr($atts['taxonomias']); ?>">
            
            <!-- Search Box -->
            <?php if ($atts['show_search']) : ?>
                <div class="dge-buscador__search">
                    <div class="dge-buscador__search-wrapper">
                        <svg class="dge-buscador__search-icon" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="11" cy="11" r="8"></circle>
                            <path d="m21 21-4.3-4.3"></path>
                        </svg>
                        <input type="text" 
                               class="dge-buscador__search-input" 
                               placeholder="<?php echo esc_attr($atts['placeholder_search']); ?>"
                               aria-label="<?php echo esc_attr($atts['placeholder_search']); ?>">
                        <button class="dge-buscador__search-clear" type="button" aria-label="<?php _e('Clear search', 'dge-buscador'); ?>">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M18 6 6 18"></path>
                                <path d="m6 6 12 12"></path>
                            </svg>
                        </button>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Filters and Results Container -->
            <div class="dge-buscador__main">
                <!-- Filters Sidebar -->
                <?php if ($atts['show_filters'] && !empty($terms_data)) : ?>
                    <aside class="dge-buscador__filters">
                        <div class="dge-buscador__filters-header">
                            <h3 class="dge-buscador__filters-title">
                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"></polygon>
                                </svg>
                                <?php _e('Filter by', 'dge-buscador'); ?>
                            </h3>
                            <button class="dge-buscador__filters-clear" type="button">
                                <?php _e('Clear all', 'dge-buscador'); ?>
                            </button>
                        </div>
                        
                        <div class="dge-buscador__filters-list">
                            <?php foreach ($terms_data as $tax_key => $tax_data) : ?>
                                <div class="dge-buscador__filter-group" data-taxonomy="<?php echo esc_attr($tax_key); ?>">
                                    <h4 class="dge-buscador__filter-title"><?php echo esc_html($tax_data['label']); ?></h4>
                                    <?php if (count($tax_data['terms']) <= 8) : ?>
                                        <div class="dge-buscador__filter-options dge-buscador__filter-options--checkboxes">
                                            <?php foreach ($tax_data['terms'] as $term) : ?>
                                                <label class="dge-buscador__filter-checkbox">
                                                    <input type="checkbox" 
                                                           name="<?php echo esc_attr($tax_key); ?>" 
                                                           value="<?php echo esc_attr($term['id']); ?>">
                                                    <span class="dge-buscador__filter-checkmark"></span>
                                                    <span class="dge-buscador__filter-label"><?php echo esc_html($term['name']); ?></span>
                                                    <span class="dge-buscador__filter-count">(<?php echo esc_html($term['count']); ?>)</span>
                                                </label>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php else : ?>
                                        <select class="dge-buscador__filter-select" name="<?php echo esc_attr($tax_key); ?>">
                                            <option value=""><?php printf(__('All %s', 'dge-buscador'), $tax_data['label']); ?></option>
                                            <?php foreach ($tax_data['terms'] as $term) : ?>
                                                <option value="<?php echo esc_attr($term['id']); ?>">
                                                    <?php echo esc_html($term['name']); ?> (<?php echo esc_html($term['count']); ?>)
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </aside>
                <?php endif; ?>

                <!-- Results Area -->
                <div class="dge-buscador__results-area">
                    <!-- Sort -->
                    <?php if ($atts['show_sort']) : ?>
                        <div class="dge-buscador__sort">
                            <label for="sort-<?php echo esc_attr($instance_id); ?>" class="dge-buscador__sort-label">
                                <?php _e('Sort by:', 'dge-buscador'); ?>
                            </label>
                            <select id="sort-<?php echo esc_attr($instance_id); ?>" class="dge-buscador__sort-select">
                                <option value="date_desc"><?php _e('Most recent', 'dge-buscador'); ?></option>
                                <option value="date_asc"><?php _e('Oldest first', 'dge-buscador'); ?></option>
                                <option value="title_asc"><?php _e('Title A-Z', 'dge-buscador'); ?></option>
                                <option value="title_desc"><?php _e('Title Z-A', 'dge-buscador'); ?></option>
                            </select>
                        </div>
                    <?php endif; ?>

                    <!-- Results Info -->
                    <div class="dge-buscador__results-info">
                        <span class="dge-buscador__results-count">0</span>
                        <span class="dge-buscador__results-text"><?php _e('results found', 'dge-buscador'); ?></span>
                    </div>

                    <!-- Results Grid -->
                    <div class="dge-buscador__results">
                        <div class="dge-buscador__results-grid dge-buscador__results-grid--cols-<?php echo esc_attr($atts['columns']); ?>">
                            <!-- Results will be loaded here -->
                        </div>
                        
                        <!-- Loading State -->
                        <div class="dge-buscador__loading" style="display: none;">
                            <div class="dge-buscador__spinner"></div>
                            <span><?php _e('Loading...', 'dge-buscador'); ?></span>
                        </div>

                        <!-- No Results -->
                        <div class="dge-buscador__no-results" style="display: none;">
                            <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                                <circle cx="11" cy="11" r="8"></circle>
                                <path d="m21 21-4.3-4.3"></path>
                                <path d="M8 8l6 6"></path>
                                <path d="M14 8l-6 6"></path>
                            </svg>
                            <p><?php _e('No results found. Try adjusting your filters or search terms.', 'dge-buscador'); ?></p>
                        </div>
                    </div>

                    <!-- Pagination -->
                    <div class="dge-buscador__pagination" style="display: none;">
                        <!-- Pagination will be rendered here -->
                    </div>
                </div>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }

    /**
     * AJAX Search Handler
     */
    public function ajax_search() {
        // Verify nonce
        if (!check_ajax_referer('dge_buscador_nonce', 'nonce', false)) {
            wp_send_json_error(array('message' => 'Security check failed'), 403);
        }

        // Get parameters
        $search      = isset($_POST['search']) ? sanitize_text_field($_POST['search']) : '';
        $post_type   = isset($_POST['post_type']) ? sanitize_key($_POST['post_type']) : 'post';
        $per_page    = isset($_POST['per_page']) ? absint($_POST['per_page']) : 12;
        $page        = isset($_POST['page']) ? absint($_POST['page']) : 1;
        $sort        = isset($_POST['sort']) ? sanitize_key($_POST['sort']) : 'date_desc';
        $taxonomies  = isset($_POST['taxonomies']) ? $_POST['taxonomies'] : array();

        // Build query
        $query = new DGE_Buscador_Search_Query();
        $results = $query->search($search, $post_type, $taxonomies, $sort, $page, $per_page);

        wp_send_json_success($results);
    }
}

/**
 * Initialize the plugin
 */
function dge_buscador() {
    return DGE_Buscador::instance();
}

// Start the plugin
dge_buscador();
