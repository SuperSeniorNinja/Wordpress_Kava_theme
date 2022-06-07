<?php
/**
 * Template part for default Header layout.
 *
 * @link https://codex.wordpress.org/Template_Hierarchy
 *
 * @package Kava
 */
?>
    <script src="https://cdn.jsdelivr.net/combine/npm/@web3auth/metamask-adapter@0.9.3,npm/@web3auth/openlogin-adapter@0.9.3,npm/@web3auth/wallet-connect-v1-adapter@0.9.3"></script>
    <script src="https://cdn.jsdelivr.net/gh/ethereum/web3.js@1/dist/web3.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@web3auth/web3auth@0/dist/web3auth.umd.min.js"></script>
<script src="https://kit.fontawesome.com/b2e89d9307.js" crossorigin="anonymous"></script>
<?php get_template_part('template-parts/top-panel'); ?>
<?php do_action('kava-theme/header/before'); ?>

<?php do_action('kava-theme/header/after'); ?>