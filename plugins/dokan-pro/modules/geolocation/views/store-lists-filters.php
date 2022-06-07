<div class="dokan-geolocation-location-filters">
    <div class="dokan-geo-filters-column">
        <input type="text" class="store-search-input dokan-form-control" name="dokan_seller_search" placeholder="<?php echo esc_attr( $placeholders['search_vendors'] ); ?>">
    </div>

    <div class="dokan-geo-filters-column">
        <div class="location-address">
            <input type="text" placeholder="<?php echo esc_attr( $placeholders['location'] ); ?>" value="<?php echo esc_attr( $address ); ?>">

            <?php if ( is_ssl() || ( defined( 'WP_DEBUG' ) && WP_DEBUG ) ): ?>
                <i class="locate-icon dokan-hides" style="background-image: url(<?php echo DOKAN_GEOLOCATION_URL . '/assets/images/locate.svg'; ?>)"></i>
                <i class="locate-loader dokan-hide" style="background-image: url(<?php echo DOKAN_GEOLOCATION_URL . '/assets/images/spinner.svg'; ?>)"></i>
            <?php endif; ?>
        </div>
    </div>

    <div class="range-slider-container">
        <span class="dokan-range-slider-value dokan-left">
            <?php _e( 'Radius', 'dokan' ); ?> <span><?php echo $distance; ?></span><?php echo $slider['unit']; ?>
        </span>

        <input
            class="dokan-range-slider dokan-left"
            type="range"
            value="<?php echo esc_attr( $distance ); ?>"
            min="<?php echo esc_attr( $slider['min'] ); ?>"
            max="<?php echo esc_attr( $slider['max'] ); ?>"
        >
    </div>

    <?php if ( isset( $mapbox_access_token ) ): ?>
        <input type="hidden" name="dokan_mapbox_access_token" value="<?php echo esc_attr( $mapbox_access_token ); ?>">
    <?php endif; ?>
</div>
