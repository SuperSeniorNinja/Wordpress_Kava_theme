<?php

namespace Hathoriel\NftMaker\Services;

class DokanService
{
    public static function getLastVisitedStores() {
        $last_visited_duplicated = get_user_meta(get_current_user_id(), 'dokan_last_visited_stores', true);
        if ($last_visited_duplicated) {
            $last_visited_duplicated = array_reverse($last_visited_duplicated);
            $last_visited_without_duplicated = [];
            foreach ($last_visited_duplicated as $key => $vendor) {
                $last_visited_without_duplicated[] = format_vendor($vendor);
            }
            return $last_visited_without_duplicated;
        }
        return [];
    }

    public static function saveDokanPageAccess() {
        $actual_page = home_url($_SERVER['REQUEST_URI']);

        if (substr($actual_page, 0, strlen(home_url('/store'))) === home_url('/store')) {
            $sellers = dokan_get_sellers();
            foreach ($sellers['users'] as $seller) {
                $vendor = dokan()->vendor->get($seller->ID);
                if ($vendor->get_shop_url() == $actual_page) {
                    $last_stores = get_user_meta(get_current_user_id(), 'dokan_last_visited_stores', true);
                    if (!$last_stores) {
                        $last_stores = [];
                    }
                    $exists = array_search($vendor->data->ID, $last_stores);
                    if ($exists !== false) {
                        array_splice($last_stores, $exists, 1);
                        $last_stores[] = $vendor->data->ID;
                    } else {
                        $last_stores[] = $vendor->data->ID;
                        if (count($last_stores) > 3) {
                            $last_stores = array_splice($last_stores, -3);
                        }
                    }
                    update_user_meta(get_current_user_id(), 'dokan_last_visited_stores', $last_stores);
                }
            }
        }
    }
}