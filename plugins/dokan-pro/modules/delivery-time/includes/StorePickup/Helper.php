<?php


namespace WeDevs\DokanPro\Modules\DeliveryTime\StorePickup;

use WC_Countries;

/**
 * Class Helper
 *
 * @package WeDevs\DokanPro\Modules\DeliveryTime\StorePickup
 */
class Helper {

    /**
     * Gets all location of a specific vendors
     *
     * @since 3.3.7
     *
     * @param int $vendor_id
     * @param bool $exclude_default (Optional)
     * @param bool $multiple_check (Optional)
     *
     * @return array
     */
    public static function get_vendor_store_pickup_locations( $vendor_id, $exclude_default = false, $multiple_check = false ) {
        $vendor_store_locations = [];

        if ( ! isset( $vendor_id ) ) {
            return $vendor_store_locations;
        }

        // Getting vendor settings
        $vendor_settings = dokan_get_store_info( $vendor_id );

        // Setting location name for default address
        $vendor_settings['address']['location_name'] = isset( $vendor_settings['vendor_store_location_pickup']['default_location_name'] ) ? $vendor_settings['vendor_store_location_pickup']['default_location_name'] : __( 'Default', 'dokan' );

        $has_default_address = self::vendor_has_default_address( $vendor_settings );

        // Returning empty location array if there's no default address
        if ( ! $has_default_address ) {
            return $vendor_store_locations;
        }

        $vendor_default_address[] = $vendor_settings['address'];

        // If vendor turned off multiple location, return the default address
        if ( $multiple_check && ! self::is_multiple_store_location_active_for_vendor( $vendor_id ) ) {
            return $vendor_default_address;
        }

        // Getting vendor store locations
        $vendor_store_locations = isset( $vendor_settings['store_locations'] ) ? $vendor_settings['store_locations'] : [];

        // Populating store locations based on default address and store locations
        $locations = $exclude_default ? $vendor_store_locations : array_merge( $vendor_default_address, $vendor_store_locations );

        return $locations;
    }

    /**
     * Gets formatted store pickup location
     *
     * @since 3.3.7
     *
     * @param array $address
     * @param string $separator (Optional)
     * @param string $location_name (Optional)
     *
     * @return string
     */
    public static function get_formatted_vendor_store_pickup_location( $address, $separator = '<br/>', $location_name = '' ) {
        if ( ! isset( $address ) ) {
            return __( 'N/A', 'dokan' );
        }

        $street_1     = isset( $address['street_1'] ) ? $address['street_1'] : '';
        $street_2     = isset( $address['street_2'] ) ? $address['street_2'] : '';
        $city         = isset( $address['city'] ) ? $address['city'] : '';

        $zip          = isset( $address['zip'] ) ? $address['zip'] : '';
        $country_code = isset( $address['country'] ) ? $address['country'] : '';
        $state_code   = isset( $address['state'] ) ? ( 'N/A' === $address['state'] ) ? '' : $address['state'] : '';

        $country           = new WC_Countries();
        $formatted_address = $country->get_formatted_address(
            [
                'address_1' => $street_1,
                'address_2' => $street_2,
                'city'      => $city,
                'postcode'  => $zip,
                'state'     => $state_code,
                'country'   => $country_code,
            ],
            $separator
        );

        if ( empty( $location_name ) ) {
            return $formatted_address;
        }

        return $location_name . ' ( ' . $formatted_address . ' )';
    }

    /**
     * Get formatted store pickup location by location index
     *
     * @since 3.3.7
     *
     * @param int $vendor_id
     * @param string $location_index
     *
     * @return string
     */
    public static function get_formatted_vendor_store_pickup_location_by_index( $vendor_id, $location_index ) {
        $vendor_locations = self::get_vendor_store_pickup_locations( $vendor_id );

        $location           = isset( $vendor_locations[ $location_index ] ) ? $vendor_locations[ $location_index ] : '';
        $formatted_location = self::get_formatted_vendor_store_pickup_location( $location, ' ' );

        return $formatted_location;
    }

    /**
     * Is enable setting for vendor store pickup location,
     * pass $bool = false for getting active status on 'yes' or 'no'
     *
     * @since 3.3.7
     *
     * @param int $vendor_id
     * @param bool $bool (Optional)
     *
     * @return bool|string
     */
    public static function is_store_pickup_location_active_for_vendor( $vendor_id, $bool = true ) {
        $vendor_settings = dokan_get_store_info( $vendor_id );

        if ( isset( $vendor_settings['vendor_store_location_pickup']['enable_store_pickup_location'] ) && 'yes' === $vendor_settings['vendor_store_location_pickup']['enable_store_pickup_location'] ) {
            return $bool ? true : 'yes';
        }

        return $bool ? false : 'no';
    }

    /**
     * Gets formatted date and store location string
     *
     * @since 3.3.7
     *
     * @param $date
     * @param $location
     *
     * @return string
     */
    public static function get_formatted_date_store_location_string( $date, $location, $slot ) {
        if ( empty( $date ) || empty( $location ) || empty( $slot ) || ! strtotime( $date ) ) {
            return '';
        }

        $formatted_date = dokan_format_date( $date );

        return $formatted_date . ' @ ' . $slot . ' : ' . $location;
    }

    /**
     * Checks if multiple store location is active for vendor
     *
     * @param int $vendor_id
     *
     * @return bool
     */
    public static function is_multiple_store_location_active_for_vendor( $vendor_id ) {
        $vendor_settings = dokan_get_store_info( $vendor_id );

        return 'yes' === $vendor_settings['vendor_store_location_pickup']['multiple_store_location'];
    }

    /**
     * Checks if vendor has default address
     *
     * @param $vendor_settings
     *
     * @return bool
     */
    public static function vendor_has_default_address( $vendor_settings ) {
        // Checking if address is empty, if true, return empty array
        if ( empty( $vendor_settings['address']['country'] ) ) {
            return false;
        }

        return true;
    }

    /**
     *Get translated delivary type string
     *
     * @param string $type
     *
     * @return string
     */
    public static function get_formatted_delivery_type( $type ) {
        switch ( $type ) {
            case 'delivery':
                return __( 'Delivery', 'dokan' );
            case 'store-pickup':
                return __( 'Store Pickup', 'dokan' );
        }
    }
}

