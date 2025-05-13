<?php
// Add menu item
add_action('admin_menu', function () {
    add_options_page(
        'QCK Shopify Settings',
        'QCK Shopify',
        'manage_options',
        'qck-shopify-settings',
        'qck_shopify_settings_page'
    );
});

// Register settings
add_action('admin_init', function () {
    register_setting('qck_shopify_settings', 'qck_shopify_api_key');
    register_setting('qck_shopify_settings', 'qck_shopify_store_url');
});

function qck_shopify_settings_page() {
    ?>
    <div class="wrap">
        <h1>QCK Shopify Sync Settings</h1>
        <form method="post" action="options.php">
            <?php
            settings_fields('qck_shopify_settings');
            do_settings_sections('qck_shopify_settings');
            ?>
            <table class="form-table">
                <tr valign="top">
                    <th scope="row">Shopify Storefront API Key</th>
                    <td><input type="text" name="qck_shopify_api_key" value="<?php echo esc_attr(get_option('qck_shopify_api_key')); ?>" size="50" /></td>
                </tr>
                <tr valign="top">
                    <th scope="row">Shopify Store URL</th>
                    <td><input type="text" name="qck_shopify_store_url" value="<?php echo esc_attr(get_option('qck_shopify_store_url')); ?>" size="50" /></td>
                </tr>
            </table>
            <?php submit_button(); ?>
        </form>
    </div>
    <?php
}
