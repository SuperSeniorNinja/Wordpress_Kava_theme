<?php
require_once('constants.php');

function isProductFromNanoverse($product_id) {
    $vendor_id = get_post_field('post_author', $product_id);
    // Get the WP_User object (the vendor) from author ID
    $vendor = new WP_User($vendor_id);
    return $vendor->display_name === NANOVERSE_SHOP_DISPLAY_NAME;
}

function uploadFile($file_name, $source, $source_dir_path) {
    $destination = trailingslashit($source_dir_path) . $file_name;
    return move_uploaded_file($source, $destination);
}

function createDir($path) {
    if (!is_dir($path)) {
        wp_mkdir_p($path);
    }
}

function filterProductsFromNanoverseInCart() {
    return array_filter(WC()->cart->get_cart(), function ($product) {
        return isProductFromNanoverse($product['product_id']);
    });
}

function cartContainsProductFromNanoverse() {
    return count(filterProductsFromNanoverseInCart()) > 0;
}

function isNanoverseStorePage() {
    $store_user = dokan()->vendor->get(get_query_var('author'));
    if ($store_user && $store_user->data) {
        return $store_user->data->display_name === NANOVERSE_SHOP_DISPLAY_NAME;
    }
    return false;

}