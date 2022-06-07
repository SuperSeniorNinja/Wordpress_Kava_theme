<?php

namespace WeDevs\DokanPro\Modules\Elementor;

use WeDevs\Dokan\Traits\Singleton;

class StoreData {

    use Singleton;

    /**
     * Holds the store data for a real store
     *
     * @since 2.9.11
     *
     * @var array
     */
    protected $store_data = [];

    /**
     * Default dynamic store data for widgets
     *
     * @since 2.9.11
     *
     * @param string $prop
     *
     * @return mixed
     */
    public function get_data( $prop = null ) {
        if ( dokan_elementor()->is_edit_or_preview_mode() ) {
            $data = $this->get_store_data_for_editing();
        } else {
            $data = $this->get_store_data();
        }

        return ( $prop && isset( $data[ $prop ] ) ) ? $data[ $prop ] : $data;
    }

    /**
     * Data for non-editing purpose
     *
     * @since 2.9.11
     *
     * @return array
     */
    protected function get_store_data() {
        if ( ! empty( $this->store_data ) ) {
            return $this->store_data;
        }

        /**
         * Filter to modify default
         *
         * Defaults are intentionally skipped from translating
         *
         * @since 2.9.11
         *
         * @param array $data
         */
        $this->store_data = apply_filters( 'dokan_elementor_store_data_defaults', [
            'id'              => 0,
            'banner'          => [
                'id'  => 0,
                'url' => DOKAN_PLUGIN_ASSEST . '/images/default-store-banner.png',
            ],
            'name'            => '',
            'profile_picture' => [
                'id'  => 0,
                'url' => get_avatar_url( 0 ),
            ],
            'address'         => '',
            'phone'           => '',
            'email'           => '',
            'rating'          => '',
            'open_close'      => '',
        ] );


        $store = dokan()->vendor->get( get_query_var( 'author' ) );

        if ( $store->id ) {
            $this->store_data['id'] = $store->id;

            $banner_id = $store->get_banner_id();

            if ( $banner_id ) {
                $this->store_data['banner'] = [
                    'id'  => $banner_id,
                    'url' => $store->get_banner(),
                ];
            }

            $this->store_data['name'] = $store->get_shop_name();

            $profile_picture_id = $store->get_avatar_id();

            if ( $profile_picture_id ) {
                $this->store_data['profile_picture'] = [
                    'id'  => $profile_picture_id,
                    'url' => $store->get_avatar(),
                ];
            }

            $address = dokan_get_seller_short_address( $store->get_id(), false );

            if ( ! empty( $address ) ) {
                $this->store_data['address'] = $address;
            }

            $phone = $store->get_phone();

            if ( ! empty( $phone ) ) {
                $this->store_data['phone'] = $phone;
            }

            $email = $store->get_email();

            if ( ! empty( $email ) ) {
                $this->store_data['email'] = $store->show_email() ? $email : '';
            }

            $rating = $store->get_readable_rating( false );

            if ( ! empty( $rating ) ) {
                $this->store_data['rating'] = $rating;
            }

            $show_store_open_close = dokan_get_option( 'store_open_close', 'dokan_general', 'on' );

            if ( $show_store_open_close == 'on' && $store->is_store_time_enabled() ) {
                if ( dokan_is_store_open( $store->get_id() ) ) {
                    $this->store_data['open_close'] = $store->get_store_open_notice();
                } else {
                    $this->store_data['open_close'] = $store->get_store_close_notice();
                }
            }

            /**
             * Filter to modify store data
             *
             * @since 2.9.11
             *
             * @param array $this->store_data
             */
            $this->store_data = apply_filters( 'dokan_elementor_store_data', $this->store_data );
        }

        return $this->store_data;
    }

    /**
     * Data for editing/previewing purpose
     *
     * @since 2.9.11
     *
     * @return array
     */
    protected function get_store_data_for_editing() {
        /**
         * Filter to modify default
         *
         * Defaults are intentionally skipped from translating
         *
         * @since 2.9.11
         *
         * @param array $this->store_data_editing
         */
        return apply_filters( 'dokan_elementor_store_data_defaults_for_editing', [
            'id'              => 0,
            'banner'          => [
                'id'  => 0,
                'url' => DOKAN_PLUGIN_ASSEST . '/images/default-store-banner.png',
            ],
            'name'            => 'Store Name',
            'profile_picture' => [
                'id'  => 0,
                'url' => get_avatar_url( 0 ),
            ],
            'address'         => 'New York, United States (US)',
            'phone'           => '123-456-7890',
            'email'           => 'mail@store.com',
            'rating'          => '5 rating from 100 reviews',
            'open_close'      => 'Store is open',
        ] );
    }
}
