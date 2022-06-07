<?php

namespace Solid_Dropdown;

use Elementor\Repeater;
use Elementor\Widget_Base;

if (!defined('ABSPATH')) exit; // Exit if accessed directly


class UserAvatar_Widget extends Widget_Base
{

    public static $slug = 'elementor-user-avatar';

    public function get_name() {
        return self::$slug;
    }

    public function get_title() {
        return __('User Avatar', self::$slug);
    }

    public function get_icon() {
        return 'fa fa-user';
    }

    public function get_categories() {
        return ['general'];
    }

    protected function render() {
        $user_id = get_current_user_id();
        um_fetch_user($user_id);

        $avatar_uri = um_get_avatar_uri(um_profile('profile_photo'), "original");

        if ($user_id > 0) {
            echo '<img style="height: 80px; border-radius: 50%" src="' . $avatar_uri . '">';
        } else {
            echo '<a href="http://odyssea.io/login/"
                class="elementor-button-link elementor-button elementor-size-sm"
                role="button"
                style="font-family: Mulish, Sans-serif;
                        font-size: 13px;
                        font-weight: 600;
                        text-transform: uppercase;
                        line-height: 1.5em;
                        letter-spacing: 1px;
                        background-color: #0C5ADB;"
                >
				<span class="elementor-button-content-wrapper">
				    <span class="elementor-button-text">Login</span>
		        </span>
			 </a>';
        }
    }
}