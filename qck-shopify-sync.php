<?php
/**
 * Plugin Name: QCK Shopify Sync
 * Description: Custom Shopify product and collection display via Storefront API.
 * Version: 1.0
 * Author: Brandon C. / SitesByYogi
 */

defined('ABSPATH') || exit;

require_once plugin_dir_path(__FILE__) . 'includes/shopify-shortcodes.php';

add_action('wp_enqueue_scripts', function () {
    wp_enqueue_style('qck-shopify-style', plugin_dir_url(__FILE__) . 'assets/shopify-style.css');
});

require_once plugin_dir_path(__FILE__) . 'includes/settings-page.php';