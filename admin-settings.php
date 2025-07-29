<?php

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

class ZatcaInvoiceSettings {

    public function __construct() {
        add_action('admin_menu', [$this, 'add_settings_page']);
        add_action('admin_init', [$this, 'register_settings']);
    }

    public function add_settings_page() {
        add_submenu_page(
            'woocommerce',
            __('ZATCA Invoice Settings', 'woocommerce-zatca-invoice'),
            __('ZATCA Invoice', 'woocommerce-zatca-invoice'),
            'manage_woocommerce',
            'zatca-invoice-settings',
            [$this, 'render_settings_page']
        );
    }

    public function register_settings() {
        register_setting('zatca_invoice_settings_group', 'zatca_invoice_seller_name');
        register_setting('zatca_invoice_settings_group', 'zatca_invoice_vat_number');
        register_setting('zatca_invoice_settings_group', 'zatca_invoice_cr_number');
        register_setting('zatca_invoice_settings_group', 'zatca_invoice_logo_url');
        register_setting('zatca_invoice_settings_group', 'zatca_invoice_template_style');
        register_setting('zatca_invoice_settings_group', 'zatca_invoice_language');
        register_setting('zatca_invoice_settings_group', 'zatca_invoice_direction');
        register_setting('zatca_invoice_settings_group', 'zatca_invoice_enable_language_switcher');
    }

    public function render_settings_page() {
        $templates = [];
        $template_dir = ZATCA_INVOICE_PLUGIN_PATH . 'templates/';
        if (is_dir($template_dir)) {
            foreach (scandir($template_dir) as $file) {
                if (pathinfo($file, PATHINFO_EXTENSION) === 'php' && strpos($file, 'invoice-') === 0) {
                    $template_name = str_replace(['invoice-', '.php'], '', $file);
                    $templates[$template_name] = ucfirst($template_name);
                }
            }
        }

        ?>
        <div class="wrap">
            <h1><?php echo esc_html__('ZATCA Invoice Settings', 'woocommerce-zatca-invoice'); ?></h1>
            <form method="post" action="options.php">
                <?php settings_fields('zatca_invoice_settings_group'); ?>
                <?php do_settings_sections('zatca-invoice-settings'); ?>
                <table class="form-table">
                    <tr valign="top">
                        <th scope="row"><?php echo esc_html__('Seller Name', 'woocommerce-zatca-invoice'); ?></th>
                        <td><input type="text" name="zatca_invoice_seller_name" value="<?php echo esc_attr(get_option('zatca_invoice_seller_name')); ?>" /></td>
                    </tr>
                    <tr valign="top">
                        <th scope="row"><?php echo esc_html__('VAT Number', 'woocommerce-zatca-invoice'); ?></th>
                        <td><input type="text" name="zatca_invoice_vat_number" value="<?php echo esc_attr(get_option('zatca_invoice_vat_number')); ?>" /></td>
                    </tr>
                    <tr valign="top">
                        <th scope="row"><?php echo esc_html__('CR Number', 'woocommerce-zatca-invoice'); ?></th>
                        <td><input type="text" name="zatca_invoice_cr_number" value="<?php echo esc_attr(get_option('zatca_invoice_cr_number')); ?>" /></td>
                    </tr>
                    <tr valign="top">
                        <th scope="row"><?php echo esc_html__('Logo URL', 'woocommerce-zatca-invoice'); ?></th>
                        <td><input type="text" name="zatca_invoice_logo_url" value="<?php echo esc_attr(get_option('zatca_invoice_logo_url')); ?>" /></td>
                    </tr>
                    <tr valign="top">
                        <th scope="row"><?php echo esc_html__('Invoice Template Style', 'woocommerce-zatca-invoice'); ?></th>
                        <td>
                            <select name="zatca_invoice_template_style">
                                <?php foreach ($templates as $value => $label) : ?>
                                    <option value="<?php echo esc_attr($value); ?>" <?php selected(get_option('zatca_invoice_template_style'), $value); ?>><?php echo esc_html($label); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </td>
                    </tr>
                    <tr valign="top">
                        <th scope="row"><?php echo esc_html__('Invoice Language', 'woocommerce-zatca-invoice'); ?></th>
                        <td>
                            <select name="zatca_invoice_language">
                                <option value="en" <?php selected(get_option('zatca_invoice_language'), 'en'); ?>><?php echo esc_html__('English', 'woocommerce-zatca-invoice'); ?></option>
                                <option value="ar" <?php selected(get_option('zatca_invoice_language'), 'ar'); ?>><?php echo esc_html__('Arabic', 'woocommerce-zatca-invoice'); ?></option>
                            </select>
                        </td>
                    </tr>
                    <tr valign="top">
                        <th scope="row"><?php echo esc_html__('Enable Language Switcher', 'woocommerce-zatca-invoice'); ?></th>
                        <td>
                            <input type="checkbox" name="zatca_invoice_enable_language_switcher" value="1" <?php checked(get_option('zatca_invoice_enable_language_switcher'), 1); ?> />
                            <p class="description"><?php echo esc_html__('Show the language switcher on the invoice view page.', 'woocommerce-zatca-invoice'); ?></p>
                        </td>
                    </tr>
                </table>
                <?php submit_button(); ?>
            </form>
        </div>
        <?php
    }
}

new ZatcaInvoiceSettings();
