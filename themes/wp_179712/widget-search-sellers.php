<?php

namespace Solid_Dropdown;

use Elementor\Repeater;
use Elementor\Widget_Base;

if (!defined('ABSPATH')) exit; // Exit if accessed directly


class SearchSellers_Widget extends Widget_Base
{

    public function __construct($data = [], $args = null) {
        parent::__construct($data, $args);

        wp_register_script( 'script-handle', get_stylesheet_directory_uri() . '/assets/react/index.js', [ 'wp-element', 'elementor-frontend' ], time(), true);
    }

    public function get_script_depends() {
        return [ 'script-handle' ];
    }

    public static $slug = 'elementor-search-sellers';

    public function get_name() {
        return self::$slug;
    }

    public function get_title() {
        return __('Search Sellers', self::$slug);
    }

    public function get_icon() {
        return 'fa fa-user';
    }

    public function get_categories() {
        return ['general'];
    }

    protected function render() {
        echo '<div id="react-app"></div>';
    }
}