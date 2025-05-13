<?php
function yogisvps_shopify_products_shortcode($atts) {
    $atts = shortcode_atts([
        'limit'     => 6,
        'collection'=> '',
    ], $atts);

    $store_url = rtrim(get_option('qck_shopify_store_url'), '/');
    $access_token = get_option('qck_shopify_api_key');

    if (empty($store_url) || empty($access_token)) {
        return '<p>Shopify integration is not configured. Please check plugin settings.</p>';
    }

    $shop_url = "{$store_url}/api/2023-10/graphql.json";

    // Query for collection OR global products
    if (!empty($atts['collection'])) {
        $handle = sanitize_text_field($atts['collection']);
        $query = <<<GQL
        {
            collectionByHandle(handle: "{$handle}") {
                products(first: {$atts['limit']}) {
                    edges {
                        node {
                            id
                            title
                            onlineStoreUrl
                            images(first: 1) {
                                edges {
                                    node {
                                        url
                                        altText
                                    }
                                }
                            }
                            priceRange {
                                minVariantPrice {
                                    amount
                                    currencyCode
                                }
                            }
                        }
                    }
                }
            }
        }
        GQL;
    } else {
        $query = <<<GQL
        {
            products(first: {$atts['limit']}) {
                edges {
                    node {
                        id
                        title
                        onlineStoreUrl
                        images(first: 1) {
                            edges {
                                node {
                                    url
                                    altText
                                }
                            }
                        }
                        priceRange {
                            minVariantPrice {
                                amount
                                currencyCode
                            }
                        }
                    }
                }
            }
        }
        GQL;
    }

    $response = wp_remote_post($shop_url, [
        'headers' => [
            'Content-Type' => 'application/json',
            'X-Shopify-Storefront-Access-Token' => $access_token,
        ],
        'body' => json_encode(['query' => $query]),
    ]);

    if (is_wp_error($response)) {
        return 'Error fetching products.';
    }

    $body = json_decode(wp_remote_retrieve_body($response), true);
    $products = !empty($atts['collection'])
        ? $body['data']['collectionByHandle']['products']['edges'] ?? []
        : $body['data']['products']['edges'] ?? [];

    if (empty($products)) {
        return '<p>No products found.</p>';
    }

    // Layout for single product
    if (count($products) === 1) {
        $product = $products[0]['node'];
        $image = $product['images']['edges'][0]['node']['url'] ?? '';
        $alt = esc_attr($product['images']['edges'][0]['node']['altText'] ?? $product['title']);
        $title = esc_html($product['title']);
        $url = esc_url($product['onlineStoreUrl']);
        $price = $product['priceRange']['minVariantPrice']['amount'] ?? '';
        $currency = $product['priceRange']['minVariantPrice']['currencyCode'] ?? '';

        $output = "<div class='shopify-product-featured'>
            <div class='shopify-product-featured-inner'>
                <img src='{$image}' alt='{$alt}' />
                <div class='shopify-product-featured-content'>
                    <h2>{$title}</h2>
                    <p class='price'>{$currency} {$price}</p>
                    <a href='{$url}' target='_blank' class='shopify-featured-button'>Shop Now</a>
                </div>
            </div>
        </div>";
    } else {
        // Grid layout
        $output = '<div class="shopify-product-grid">';
        foreach ($products as $edge) {
            $product = $edge['node'];
            $image = $product['images']['edges'][0]['node']['url'] ?? '';
            $alt = esc_attr($product['images']['edges'][0]['node']['altText'] ?? $product['title']);
            $title = esc_html($product['title']);
            $url = esc_url($product['onlineStoreUrl']);
            $price = $product['priceRange']['minVariantPrice']['amount'] ?? '';
            $currency = $product['priceRange']['minVariantPrice']['currencyCode'] ?? '';

            $output .= "<div class='shopify-product'>
                <a href='{$url}' target='_blank'>
                    <img src='{$image}' alt='{$alt}' />
                    <h3>{$title}</h3>
                    <p>{$currency} {$price}</p>
                    <span class='shopify-view-button'>View Product</span>
                </a>
            </div>";
        }
        $output .= '</div>';
    }

    return $output;
}
add_shortcode('shopify_products', 'yogisvps_shopify_products_shortcode');

function yogisvps_single_shopify_product_shortcode($atts) {
    $atts = shortcode_atts([
        'product' => '',
    ], $atts);

    if (empty($atts['product'])) {
        return '<p>No product handle provided.</p>';
    }

    $store_url = rtrim(get_option('qck_shopify_store_url'), '/');
    $access_token = get_option('qck_shopify_api_key');

    if (empty($store_url) || empty($access_token)) {
        return '<p>Shopify integration is not configured. Please check plugin settings.</p>';
    }

    $shop_url = "{$store_url}/api/2023-10/graphql.json";

    $query = <<<GQL
    {
        productByHandle(handle: "{$atts['product']}") {
            title
            onlineStoreUrl
            images(first: 1) {
                edges {
                    node {
                        url
                        altText
                    }
                }
            }
            priceRange {
                minVariantPrice {
                    amount
                    currencyCode
                }
            }
        }
    }
    GQL;

    $response = wp_remote_post($shop_url, [
        'headers' => [
            'Content-Type' => 'application/json',
            'X-Shopify-Storefront-Access-Token' => $access_token,
        ],
        'body' => json_encode(['query' => $query]),
    ]);

    if (is_wp_error($response)) {
        return 'Error fetching product.';
    }

    $body = json_decode(wp_remote_retrieve_body($response), true);
    $product = $body['data']['productByHandle'] ?? null;

    if (!$product || !is_array($product)) {
        return '<p>Product not found or unexpected API response.</p>';
    }

    $image = $product['images']['edges'][0]['node']['url'] ?? '';
    $alt = esc_attr($product['images']['edges'][0]['node']['altText'] ?? $product['title']);
    $title = esc_html($product['title']);
    $url = esc_url($product['onlineStoreUrl']);
    $price = $product['priceRange']['minVariantPrice']['amount'] ?? '';
    $currency = $product['priceRange']['minVariantPrice']['currencyCode'] ?? '';

    $output = "<div class='shopify-product-featured'>
        <div class='shopify-product-featured-inner'>
            <img src='{$image}' alt='{$alt}' />
            <div class='shopify-product-featured-content'>
                <h2>{$title}</h2>
                <p class='price'>{$currency} {$price}</p>
                <a href='{$url}' target='_blank' class='shopify-featured-button'>Shop Now</a>
            </div>
        </div>
    </div>";

    return $output;
}
add_shortcode('shopify_product', 'yogisvps_single_shopify_product_shortcode');
