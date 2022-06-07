<?php
/**
 * Dokan analytics Content Template
 *
 * @since 2.4
 *
 * @package dokan
 */
?>
<div class="dokan-report-wrap">
    <ul class="dokan_tabs">
    <?php
    foreach ( $tabs['tabs'] as $key => $value ) {

        $class = ( $current == $key ) ? ' class="active"' : '';
        printf( '<li%s><a href="%s">%s</a></li>', $class, add_query_arg( array( 'tab' => $key ), $link ), $value['title'] );
    }
    ?>
    </ul>

    <?php if ( isset( $tabs['tabs'][$current] ) ) { ?>
        <div id="dokan_tabs_container">
            <div class="tab-pane active" id="home">
                <?php
                $func = $tabs['tabs'][$current]['function'];
                if ( $func && ( is_callable( $func ) ) ) {
                    call_user_func( $func );
                }
                ?>
            </div>
        </div>

    <?php } ?>
</div>
