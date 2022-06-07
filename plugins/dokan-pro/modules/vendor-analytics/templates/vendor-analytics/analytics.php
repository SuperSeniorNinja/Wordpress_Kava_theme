<?php
/**
 *  Dokan Dashboard analytics Template
 *
 *  Load analytics related template
 *
 *  @since 2.4
 *
 *  @package dokan
 */
?>

<?php do_action( 'dokan_dashboard_wrap_start' ); ?>

<div class="dokan-dashboard-wrap">

    <?php

        /**
         *  dokan_dashboard_content_before hook
         *
         *  @hooked get_dashboard_side_navigation
         *
         *  @since 2.4
         */
        do_action( 'dokan_dashboard_content_before' );
        do_action( 'dokan_analytics_content_before' );

    ?>

    <div class="dokan-dashboard-content dokan-reports-content">

        <?php

            /**
             *  dokan_analytics_content_inside_before hook
             *
             *  @hooked show_seller_enable_message
             *
             *  @since 2.4
             */
            do_action( 'dokan_analytics_content_area_header' );
        ?>

        <?php

            /**
             *  dokan_analytics_content_inside_before hook
             *
             *  @hooked show_seller_enable_message
             *
             *  @since 2.4
             */
            do_action( 'dokan_analytics_content_inside_before' );
        ?>


        <article class="dokan-reports-area">

            <?php

                /**
                 *  dokan_analytics_content hook
                 *
                 *  @since 2.4
                 */
                do_action( 'dokan_analytics_content' );
            ?>
        </article>

        <?php

            /**
             *  dokan_analytics_content_inside_after hook
             *
             *  @since 2.4
             */
            do_action( 'dokan_analytics_content_inside_after' );
        ?>

    </div> <!-- #primary .content-area -->

    <?php

        /**
         *  dokan_dashboard_content_after hook
         *  dokan_analytics_content_after hook
         *
         *  @since 2.4
         */
        do_action( 'dokan_dashboard_content_after' );
        do_action( 'dokan_analytics_content_after' );

    ?>

</div><!-- .dokan-dashboard-wrap -->

<?php do_action( 'dokan_dashboard_wrap_end' ); ?>