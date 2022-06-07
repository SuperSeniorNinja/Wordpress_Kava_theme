<?php

add_filter('elementor/icons_manager/additional_tabs', 'register_elementor_additional_icons');

function register_elementor_additional_icons ( $tabs=[] ) {

     $tabs['flaticon'] = [
        'name' => 'Flaticon',
        'label' => __( 'Flaticon', 'wp_179712' ),
        'url' => get_stylesheet_directory_uri() . '/icons/flaticon/icons.css' ,
        'enqueue' => [ get_stylesheet_directory_uri() . '/icons/flaticon/font.css' ],
        'prefix' => 'flaticon-',
        'displayPrefix' => 'flaticon',
        'labelIcon' => 'eicon-favorite',
        'ver' => null,
        'fetchJson' => get_stylesheet_directory_uri() . '/icons/flaticon/icons.json' ,
        'native' => false,
    ];

     $tabs['continuous'] = [
        'name' => 'Сontinuous',
        'label' => __( 'Сontinuous', 'wp_179712' ),
        'url' => get_stylesheet_directory_uri() . '/icons/continuous/icons.css' ,
        'enqueue' => [ get_stylesheet_directory_uri() . '/icons/continuous/font.css' ],
        'prefix' => 'fl-continuous-',
        'displayPrefix' => 'fl-continuous',
        'labelIcon' => 'eicon-favorite',
        'ver' => null,
        'fetchJson' => get_stylesheet_directory_uri() . '/icons/continuous/icons.json' ,
        'native' => false,
    ];

    $tabs['fl-great-icon-set'] = [
        'name' => 'Great',
        'label' => __( 'Great', 'wp_179712' ),
        'url' => get_stylesheet_directory_uri() . '/icons/fl-great/icons.css' ,
        'enqueue' => [ get_stylesheet_directory_uri() . '/icons/fl-great/font.css' ],
        'prefix' => 'fl-great-icon-set-',
        'displayPrefix' => 'fl-great-icon-set-ico',
        'labelIcon' => 'eicon-favorite',
        'ver' => null,
        'fetchJson' => get_stylesheet_directory_uri() . '/icons/fl-great/fl-great-icon.json' ,
        'native' => false,
	];

	$tabs['material-design-icons'] = [
        'name' => 'Material Design Icons',

        'label' => __( 'Material Design Icons', 'wp_179712' ),
        
        'url' => get_stylesheet_directory_uri() . '/icons/mdi/icons.css' ,

        'enqueue' => [ get_stylesheet_directory_uri() . '/icons/mdi/font.css' ],

        'prefix' => 'mdi-',

        'displayPrefix' => 'mdi',

        'labelIcon' => 'eicon-favorite',

        'ver' => null,

        'fetchJson' => get_stylesheet_directory_uri() . '/icons/mdi/icons.json',

        'native' => false,
    ];

    $tabs['fl-budicons-free'] = [
        'name' => 'FL Budicons Free',

        'label' => __( 'FL Budicons Free', 'wp_179712' ),
        
        'url' => get_stylesheet_directory_uri() . '/icons/fl-budicons-free/icons.css' ,

        'enqueue' => [ get_stylesheet_directory_uri() . '/icons/fl-budicons-free/font.css' ],

        'prefix' => 'fl-budicons-free-',

        'displayPrefix' => 'fl-budicons-free-ico',

        'labelIcon' => 'eicon-favorite',

        'ver' => null,

        'fetchJson' => get_stylesheet_directory_uri() . '/icons/fl-budicons-free/icons.json',

        'native' => false,
    ];

     $tabs['fl-bigmug-line'] = [
     	
        'name' => 'Fl Bigmug Line',

        'label' => __( 'Fl Bigmug Line', 'wp_179712' ),
        
        'url' => get_stylesheet_directory_uri() . '/icons/fl-bigmug-line/icons.css' ,

        'enqueue' => [ get_stylesheet_directory_uri() . '/icons/fl-bigmug-line/font.css' ],

        'prefix' => 'fl-bigmug-line-',

        'displayPrefix' => 'fl-bigmug-line-ico',

        'labelIcon' => 'eicon-favorite',

        'ver' => null,

        'fetchJson' => get_stylesheet_directory_uri() . '/icons/fl-bigmug-line/icons.json',

        'native' => false,
    ];

    return $tabs;
}
add_action('wp_enqueue_scripts', 'fonts_styles', 0);
function fonts_styles() {
    wp_enqueue_style( 'all-fonts', get_stylesheet_directory_uri() .'/assets/css/fonts.css' );
}

function kava_get_page_preloader () {
    $page_preloader  = kava_theme()->customizer->get_value( 'page_preloader' );

    if ( $page_preloader ) {

        printf(
            '<div class="preloader">
                <div class="imgpreloader">
                    <img src="'
            );
            echo get_theme_mod('img-upload');
        printf(
                '" alt="headphone">
                </div>
            </div>'
            );
    }
}