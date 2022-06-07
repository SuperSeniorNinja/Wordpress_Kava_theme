<?php
$user_id = get_current_user_id();
um_fetch_user($user_id);

if(um_profile('profile_photo')) {
    $avatar_uri = um_get_avatar_uri(um_profile('profile_photo'), "original");
} else {
    $avatar_uri = um_get_default_avatar_uri();
}

if ($user_id > 0) {
    ?>
    <div class='header-menu-item'>
        <img src='<?= $avatar_uri ?>' class='avatar-image'>
        <div class="dropdown-content">
            <a href="<?= get_site_url() ?>/dashboard">Dashboard</a>
            <a href="<?= get_site_url() ?>/account">Account</a>
            <a href="<?= get_site_url() ?>/account/password">Change password</a>
            <a href="<?= get_site_url() ?>/account/privacy">Privacy</a>
            <a href="<?= get_site_url() ?>/dashboard/withdraw">Wallet</a>
        </div>
    </div>
    <?php
} else {
    ?>
    <a href="<?= get_site_url() ?>/login/"
       class="elementor-button-link elementor-button elementor-size-sm login-button"
       role="button">
				<span class="elementor-button-content-wrapper">
				    <span class="elementor-button-text"><i class="fa-solid fa-arrow-right-to-bracket"></i></span>
		        </span>
    </a>
    <?php
}
?>