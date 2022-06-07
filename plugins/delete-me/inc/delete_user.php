<?php
// File called by class?
if ( isset( $this ) == false || get_class( $this ) != 'plugin_delete_me' ) exit;

// Does user have the capability?
if ( current_user_can( $this->info['cap'] ) == false || ( is_multisite() && is_super_admin() ) ) return; // stop executing file

// Does the trigger value match the currently logged in user ID?
if ( empty( $this->user_ID ) || $this->GET[$this->info['trigger']] != $this->user_ID ) return; // stop executing file

// Nonce
if ( isset( $this->GET[$this->info['nonce']] ) == false || wp_verify_nonce( $this->GET[$this->info['nonce']], $this->info['nonce'] ) == false ) return; // stop executing file

// User - Used to confirm password and for data in email notification
$user = get_user_by( 'id', $this->user_ID );

// Confirm Password Required?
$your_profile_confirmation_page = ( is_admin() && basename( $_SERVER['SCRIPT_NAME'] ) == 'options.php' && $this->GET['page'] == ( $this->info['slug_prefix'] . '_confirmation' ) ) ? true : false;
if ( ( $your_profile_confirmation_page && $this->option['settings']['your_profile_confirm_password_required'] ) || ( !$your_profile_confirmation_page && $this->option['settings']['shortcode_form_enabled'] ) ) {
	
	$this->POST = $this->striptrim_deep( $_POST );
	$password_entered = '';
	$password_entered = isset( $this->POST[$this->info['trigger'] . '_your_profile_confirm_password'] ) ? $this->POST[$this->info['trigger'] . '_your_profile_confirm_password'] : $password_entered;
	$password_entered = isset( $this->POST[$this->info['trigger'] . '_shortcode_password'] ) ? $this->POST[$this->info['trigger'] . '_shortcode_password'] : $password_entered;
	if ( empty( $password_entered ) || empty( $user ) || wp_check_password( $password_entered, $user->data->user_pass ) !== true ) return; // stop executing file
	
}

// Include required WordPress function files
include_once( ABSPATH . WPINC . '/post.php' ); // wp_delete_post
include_once( ABSPATH . 'wp-admin/includes/bookmark.php' ); // wp_delete_link
include_once( ABSPATH . 'wp-admin/includes/comment.php' ); // wp_delete_comment
include_once( ABSPATH . 'wp-admin/includes/user.php' ); // wp_delete_user, get_blogs_of_user

if ( is_multisite() ) {
	
	include_once( ABSPATH . WPINC . '/ms-functions.php' ); // remove_user_from_blog
	include_once( ABSPATH . 'wp-admin/includes/ms.php' ); // wpmu_delete_user
	
}

// Number of Sites to which the user is registered
$num_blogs_of_user = is_multisite() ? count( get_blogs_of_user( $this->user_ID ) ) : 1;

// "Delete From Network" setting is checked or user belongs to only one blog
$delete_from_network = ( is_multisite() && ( $this->option['settings']['ms_delete_from_network'] == true || $num_blogs_of_user == 1 ) ) ? true : false;

// Posts
//->>> Start: WordPress wp_delete_user Post types to delete
$post_types_to_delete = array();

foreach ( get_post_types( array(), 'objects' ) as $post_type ) {
	
	if ( $post_type->delete_with_user ) {
		
		$post_types_to_delete[] = $post_type->name;
		
	} elseif ( null === $post_type->delete_with_user && post_type_supports( $post_type->name, 'author' ) ) {
		
		$post_types_to_delete[] = $post_type->name;
		
	}
	
}

$post_types_to_delete = apply_filters( 'post_types_to_delete_with_user', $post_types_to_delete, $this->user_ID );
//<<<- End: WordPress wp_delete_user Post types to delete

$posts_list = array();
$posts = $this->wpdb->get_results( "SELECT `ID`, `post_title`, `post_type` FROM " . $this->wpdb->posts . " WHERE `post_author`='" . $this->user_ID . "' AND `post_type` IN ('" . implode( "', '", $post_types_to_delete ) . "')", ARRAY_A );
foreach ( $posts as $post ) $posts_list[] = wp_specialchars_decode( $post['post_title'], ENT_QUOTES ) . "\n" . ucwords( $post['post_type'] ) . ' ' . get_permalink( $post['ID'] );

// Links
$links_list = array();
$links = $this->wpdb->get_results( "SELECT `link_id`, `link_url`, `link_name` FROM " . $this->wpdb->links . " WHERE `link_owner`='" . $this->user_ID . "'", ARRAY_A );
foreach ( $links as $link ) $links_list[] = wp_specialchars_decode( $link['link_name'], ENT_QUOTES ) . "\n" . $link['link_url'];

// Comments
$comments_list = array();

if ( $this->option['settings']['delete_comments'] == true ) :
	
	$comments = $this->wpdb->get_results( "SELECT `comment_ID` FROM " . $this->wpdb->comments . " WHERE `user_id`='" . $this->user_ID . "'", ARRAY_A );
	
	foreach ( $comments as $comment ) {
		
		$comments_list[] = $comment['comment_ID'];
		
		// Delete comments if option set
		wp_delete_comment( $comment['comment_ID'] );
		
	}
	
endif;

// E-mail notification
if ( $this->option['settings']['email_notification'] == true ) :
	
	$email = array();
	$email['to'] = get_option( 'admin_email' );
	$email['subject'] = sprintf( _x( '[%s] Deleted User Notification', '%s = site name', 'delete-me' ), wp_specialchars_decode( get_option( 'blogname' ), ENT_QUOTES ) );
	$email['message'] =
	sprintf( _x( 'Deleted user on your site %s', '%s = site name', 'delete-me' ), wp_specialchars_decode( get_option( 'blogname' ), ENT_QUOTES ) ) . ":" . "\n\n" .
	( ( $num_blogs_of_user > 1 && $delete_from_network ) ? ( sprintf( __( 'User deleted from %d total Network Sites.', 'delete-me' ), $num_blogs_of_user ) . "\n\n" ) : '' ) .
	__( 'Username', 'delete-me' ) . ': ' . $this->user_login . "\n\n" .
	__( 'E-mail', 'delete-me' ) . ': ' . $this->user_email . "\n\n" .
	__( 'Role', 'delete-me' ) . ': ' . implode( ',', $user->roles ) . "\n\n" .
	__( 'First Name', 'delete-me' ) . ': ' . ( empty( $user->first_name ) ? __( '(empty)', 'delete-me' ) : $user->first_name ) . "\n\n" .
	__( 'Last Name', 'delete-me' ) . ': ' . ( empty( $user->last_name ) ? __( '(empty)', 'delete-me' ) : $user->last_name ) . "\n\n" .
	sprintf( _x( 'Registered: %s', '%s = date user registered with the Network or Site', 'delete-me' ), $user->data->user_registered ) . "\n\n" .
	sprintf( _x( 'This user deleted themselves using the WordPress plugin %s', '%s = plugin name', 'delete-me' ), $this->info['name'] ) . "\n\n" .
	sprintf( __( '%d Post(s)', 'delete-me' ), count( $posts_list ) ) . "\n" .
	'----------------------------------------------------------------------' . "\n" .
	implode( "\n\n", $posts_list ) . "\n\n" .
	sprintf( __( '%d Link(s)', 'delete-me' ), count( $links_list ) ) . "\n" .
	'----------------------------------------------------------------------' . "\n" .
	implode( "\n\n", $links_list ) . "\n\n" .
	sprintf( __( '%d Comment(s)', 'delete-me' ), count( $comments_list ) );
	wp_mail( $email['to'], $email['subject'], $email['message'] );
	
endif;

// Delete user
if ( $delete_from_network ) {
	
	// Multisite: Deletes user's Posts and Links, then deletes from WP Users|Usermeta
	wpmu_delete_user( $this->user_ID );
	
} else {
	
	// Deletes user's Posts and Links
	// Multisite: Removes user from current blog
	// Not Multisite: Deletes user from WP Users|Usermeta	
	wp_delete_user( $this->user_ID );
	
}

// Logout
wp_logout();

// Redirect to same or landing URL
$shortcode_landing_url = isset( $this->GET[$this->info['trigger'] . '_landing_url'] ) ? $this->GET[$this->info['trigger'] . '_landing_url'] : $this->option['settings']['shortcode_landing_url'];
$same_url = remove_query_arg( array( $this->info['trigger'], $this->info['nonce'], $this->info['trigger'] . '_landing_url' ), $_SERVER['REQUEST_URI'] );
is_admin() ? wp_redirect( ( $this->option['settings']['your_profile_landing_url'] == '' ) ? $same_url : $this->option['settings']['your_profile_landing_url'] ) : wp_redirect( ( $shortcode_landing_url == '' ) ? $same_url : $shortcode_landing_url );

exit;
