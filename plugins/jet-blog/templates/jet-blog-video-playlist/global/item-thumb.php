<?php
/**
 * Playlist item title template
 */

$thumb = isset( $video_data['thumbnail_medium'] ) ? $video_data['thumbnail_medium'] : $video_data['thumbnail_default'];
$title = ! empty( $item['title'] ) ? $item['title'] : $video_data['title'];

$custom_thumb = $this->_get_custom_thumb( $item );

$thumb = ! empty( $custom_thumb ) ? $custom_thumb : $thumb;

?>

<div class="jet-blog-playlist__item-thumb">
	<img src="<?php echo $thumb; ?>" alt="<?php echo esc_attr( $title ); ?>" title="<?php echo esc_attr( $title ); ?>" class="jet-blog-playlist__item-thumb-img">
</div>