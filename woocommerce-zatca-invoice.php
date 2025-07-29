<?php
/**
 * Plugin Name: WooCommerce ZATCA Invoice
 * Description: Generate ZATCA compliant PDF invoices for WooCommerce.
 * Version: 1.0.0
 * Author: Mohamed Yussry
 * Author URI: https://github.com/mohamedyussry
 * WhatsApp: +201066211527
 * Text Domain: woocommerce-zatca-invoice
 * Domain Path: /languages
 * Requires at least: 5.0
 * Requires PHP: 7.4
 * WC requires at least: 4.0
 * WC tested up to: 8.3
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

// Define plugin constants
define('ZATCA_INVOICE_PLUGIN_PATH', plugin_dir_path(__FILE__));
define('ZATCA_INVOICE_PLUGIN_URL', plugin_dir_url(__FILE__));


// Include the Composer autoloader
if (file_exists(ZATCA_INVOICE_PLUGIN_PATH . 'vendor/autoload.php')) {
    require_once ZATCA_INVOICE_PLUGIN_PATH . 'vendor/autoload.php';
}

// Include plugin files
require_once ZATCA_INVOICE_PLUGIN_PATH . 'lib/pdf-generator.php';
require_once ZATCA_INVOICE_PLUGIN_PATH . 'admin-settings.php';



/**
 * Main plugin class
 */
class WooCommerce_Zatca_Invoice
{

    public function __construct()
    {
        // Create the invoices directory if it doesn't exist
        

        add_action('admin_init', [$this, 'handle_invoice_actions']); // Handles generate, regenerate, delete
        add_filter('manage_edit-shop_order_columns', [$this, 'add_invoice_column']);
        add_action('manage_shop_order_posts_custom_column', [$this, 'add_invoice_column_content'], 10, 2);
        add_action('add_meta_boxes', [$this, 'add_invoice_meta_box']);
        
        // License Check Actions
        add_action('admin_notices', [$this, 'show_license_warning']);
    }

    /**
     * Check license key against the Google Sheet in real-time.
     *
     * @return boolean True if licensed, false otherwise.
     */
    private function is_license_active()
    {
        // We only want to run this check for admins to avoid unnecessary calls
        if (!current_user_can('manage_options')) {
            // Default to true for non-admins to avoid blocking functionality for other user roles.
            return true;
        }

        // The URL of the public Google Sheet JSON endpoint
        $license_url = 'https://opensheet.elk.sh/1DE4ZcZv2QeYbpTjDW0TbsRdy_T7yIt5qGSHbUoweOWA/1';
        
        // Fetch the license data
        $response = wp_remote_get($license_url, ['timeout' => 20]);

        // If we can't reach the server for any reason, assume the license is valid to avoid locking the user out.
        if (is_wp_error($response) || wp_remote_retrieve_response_code($response) !== 200) {
            return true; 
        }

        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);

        // If data is invalid or empty, don't block.
        if (empty($data) || !is_array($data)) {
            return true;
        }

        $site_url = get_site_url();
        $is_licensed = false;

        // Loop through the license data to find a match
        foreach ($data as $row) {
            // Check for a matching site URL and an active status
            if (isset($row['site_url'], $row['is_active']) && $row['site_url'] == $site_url && $row['is_active'] == '1') {
                $is_licensed = true;
                break; // Exit the loop once a valid license is found
            }
        }

        return $is_licensed;
    }

    /**
     * Display a warning notice in the admin panel if the license is not active.
     * This notice is only visible to users who can manage options (administrators).
     */
    public function show_license_warning()
    {
        // Check if the user is an admin and the license is not active
        if (current_user_can('manage_options') && !$this->is_license_active()) {
            $whatsapp_url = 'https://wa.me/201066211527';
            $developer_url = 'https://github.com/mohamedyussry';
            $message = sprintf(
                __('<strong>إضافة فواتير ZATCA:</strong> ترخيص الإضافة غير نشط حاليًا على موقعكم. الإضافة ستستمر في العمل، ولكن نرجو منكم %1$s لتفعيل الترخيص. للمزيد من المعلومات، يمكنكم زيارة %2$s.', 'woocommerce-zatca-invoice'),
                '<a href="' . esc_url($whatsapp_url) . '" target="_blank">' . __('التواصل معنا عبر الواتساب', 'woocommerce-zatca-invoice') . '</a>',
                '<a href="' . esc_url($developer_url) . '" target="_blank">' . __('صفحة المطور', 'woocommerce-zatca-invoice') . '</a>'
            );
            ?>
            <div class="notice notice-warning is-dismissible">
                <p><?php echo $message; ?></p>
            </div>
            <?php
        }
    }

    /**
     * Generate the invoice PDF.
     *
     * @param int $order_id
     * @return string|false The path to the generated PDF or false on failure.
     */
    public function generate_invoice($order_id, $lang = null)
    {
        $order = wc_get_order($order_id);
        if (!$order) {
            return false;
        }

        $pdf_generator = new ZatcaInvoice\HtmlInvoiceGenerator();
        return $pdf_generator->generate($order_id, $lang);
    }

    

    /**
     * Add the invoice column to the orders list table.
     *
     * @param array $columns
     * @return array
     */
    public function add_invoice_column($columns)
    {
        $new_columns = [];
        foreach ($columns as $key => $value) {
            $new_columns[$key] = $value;
            if ($key === 'order_status') {
                $new_columns['invoice'] = __('Invoice', 'woocommerce-zatca-invoice');
            }
        }
        return $new_columns;
    }

    /**
     * Add the invoice view button to the orders list table.
     *
     * @param string $column
     * @param int $post_id
     */
    public function add_invoice_column_content($column, $post_id)
    {
        if ($column === 'invoice') {
            $view_url = add_query_arg(['action' => 'view_zatca_invoice_html', 'order_id' => $post_id]);
            echo '<a href="' . esc_url($view_url) . '" class="button" target="_blank">' . __('View Invoice', 'woocommerce-zatca-invoice') . '</a>';
        }
    }

    /**
     * Add a meta box to the order edit page.
     */
    public function add_invoice_meta_box()
    {
        add_meta_box(
            'zatca_invoice_meta_box',
            __('ZATCA Invoice', 'woocommerce-zatca-invoice'),
            [$this, 'render_invoice_meta_box_content'],
            'shop_order',
            'side',
            'default'
        );
    }

    /**
     * Render the content of the invoice meta box.
     *
     * @param WP_Post $post
     */
    public function render_invoice_meta_box_content($post)
    {
        $order_id = $post->ID;
        $view_url = add_query_arg(['action' => 'view_zatca_invoice_html', 'order_id' => $order_id]);
        echo '<p><a href="' . esc_url($view_url) . '" class="button button-primary" target="_blank">' . __('View Invoice', 'woocommerce-zatca-invoice') . '</a></p>';
    }

    /**
     * Handle manual invoice actions (generate, regenerate, delete, view).
     */
    public function handle_invoice_actions()
    {
        if (!isset($_GET['order_id']) || !isset($_GET['action'])) {
            return;
        }

        // Check for the view action specifically
        if ($_GET['action'] === 'view_zatca_invoice_html') {
            
            // Block view action if license is not active
            if (!$this->is_license_active()) {
                $whatsapp_url = 'https://wa.me/201066211527';
                $developer_url = 'https://github.com/mohamedyussry';
                $message = sprintf(
                    __('نعتذر، ترخيص الإضافة غير نشط حاليًا. لعرض وإنشاء الفواتير، نرجو منكم %1$s لتفعيل الإضافة. للمزيد من المعلومات، يمكنكم زيارة %2$s.', 'woocommerce-zatca-invoice'),
                    '<a href="' . esc_url($whatsapp_url) . '" target="_blank">' . __('التواصل معنا عبر الواتساب', 'woocommerce-zatca-invoice') . '</a>',
                    '<a href="' . esc_url($developer_url) . '" target="_blank">' . __('صفحة المطور', 'woocommerce-zatca-invoice') . '</a>'
                );

                // Load the custom license warning template
                include ZATCA_INVOICE_PLUGIN_PATH . 'templates/license-warning.php';
                exit;
            }

            if (!current_user_can('edit_shop_orders')) {
                wp_die(__('You do not have sufficient permissions to access this page.'));
            }

            $order_id = intval($_GET['order_id']);
            $lang = isset($_GET['lang']) ? sanitize_text_field($_GET['lang']) : null;

            $invoice_html = $this->generate_invoice($order_id, $lang);

            if ($invoice_html) {
                echo $invoice_html;
                if (get_option('zatca_invoice_enable_language_switcher')) {
                    $en_url = add_query_arg(['lang' => 'en']);
                    $ar_url = add_query_arg(['lang' => 'ar']);
                    echo '<div class="language-switcher">';
                    echo '<a href="' . esc_url($en_url) . '" class="button">English</a>';
                    echo '<a href="' . esc_url($ar_url) . '" class="button">العربية</a>';
                    echo '</div>';
                }
                echo '<button onclick="window.print()">Print Invoice</button>'; // Print button
                exit;
            } else {
                wp_die(__('Invoice not found or could not be generated.', 'woocommerce-zatca-invoice'));
            }
        }
    }

}

new WooCommerce_Zatca_Invoice();


