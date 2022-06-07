<?php
/**
 * lass Jet Woo Product Gallery Base
 *
 * @package   JetWooProductGallery
 * @author    Crocoblock
 * @license   GPL-2.0+
 */

namespace Elementor;

use Elementor\Modules\DynamicTags\Module as TagsModule;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

abstract class Jet_Woo_Product_Gallery_Base extends Widget_Base {

	public $__context         = 'render';
	public $__processed_item  = false;
	public $__new_icon_prefix = 'selected_';

	public function get_jet_help_url() {
		return false;
	}

	public function get_help_url() {
		$url = $this->get_jet_help_url();
		if ( ! empty( $url ) ) {
			return add_query_arg(
				array(
					'utm_source'   => 'need-help',
					'utm_medium'   => $this->get_name(),
					'utm_campaign' => 'jetproductgallery',
				),
				esc_url( $url )
			);
		}
		return false;
	}

	protected function _register_controls() {

		$this->register_base_general_controls();

		$this->register_product_gallery_controls();

		$this->register_base_gallery_controls();

		$css_scheme = apply_filters(
			'jet-woo-product-gallery/base/css-scheme',
			array(
				'wrapper'                   => '.jet-woo-product-video',
				'overlay'                   => '.jet-woo-product-video__overlay',
				'video_popup_wrapper'       => '.jet-woo-product-video__popup-wrapper',
				'video_popup_overlay'       => '.jet-woo-product-video__popup-overlay',
				'video-popup-button'        => '.jet-woo-product-video__popup-button',
				'video-play-overlay'        => '.jet-woo-product-video__overlay',
				'video-play-button'         => '.jet-woo-product-video__play-button',
				'video-play-button-image'   => '.jet-woo-product-video__play-button-image',
				'popup-button'              => '.jet-woo-product-video__popup-button',
				'popup-button-icon'         => '.jet-woo-product-video__popup-button-icon',
				'popup-button-image'        => '.jet-woo-product-video__popup-button-image',
				'photoswipe-trigger'        => '.jet-woo-product-gallery .jet-woo-product-gallery__trigger:not( .jet-woo-product-gallery__image-link )',
				'photoswipe-bg'             => '.jet-woo-product-gallery-' . $this->get_id() . ' .pswp__bg',
				'photoswipe-controls'       => '.jet-woo-product-gallery-' . $this->get_id() . ' .pswp__button::before',
				'photoswipe-controls-hover' => '.jet-woo-product-gallery-' . $this->get_id() . ' .pswp__button:hover::before',
			)
		);

		$this->register_base_video_controls( $css_scheme );

		$this->register_base_photoswipe_trigger_controls_style( $css_scheme );

		$this->register_base_photoswipe_gallery_controls_style( $css_scheme );

		$this->register_base_video_popup_button_controls_style( $css_scheme );

		$this->register_base_video_play_button_controls_style( $css_scheme );

	}

	protected function register_base_general_controls() {

		$this->start_controls_section(
			'section_general_content',
			[
				'label'      => esc_html__( 'General', 'jet-woo-product-gallery' ),
				'tab'        => Controls_Manager::TAB_CONTENT,
				'show_label' => false,
			]
		);

		$this->add_control(
			'gallery_source',
			[
				'label'   => esc_html__( 'Source', 'jet-woo-product-gallery' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'manual',
				'options' => jet_woo_product_gallery_tools()->get_gallery_source_options(),
			]
		);

		$this->add_control(
			'product_id',
			[
				'label'     => esc_html__( 'Product id', 'jet-woo-product-gallery' ),
				'type'      => Controls_Manager::NUMBER,
				'condition' => [
					'gallery_source' => 'products',
				],
			]
		);

		$this->add_control(
			'disable_feature_image',
			[
				'label'        => esc_html__( 'Disable Feature Image', 'jet-woo-product-gallery' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_on'     => esc_html__( 'Yes', 'jet-woo-product-gallery' ),
				'label_off'    => esc_html__( 'No', 'jet-woo-product-gallery' ),
				'return_value' => 'yes',
				'default'      => '',
				'condition'    => [
					'gallery_source' => 'products',
				],
			]
		);

		$this->add_control(
			'enable_zoom',
			[
				'label'        => esc_html__( 'Enable Zoom', 'jet-woo-product-gallery' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_on'     => esc_html__( 'Yes', 'jet-woo-product-gallery' ),
				'label_off'    => esc_html__( 'No', 'jet-woo-product-gallery' ),
				'return_value' => 'yes',
				'default'      => '',
				'condition'    => [
					'gallery_source' => 'products',
				],
			]
		);

		$this->add_control(
			'zoom_magnify',
			[
				'label'     => esc_html__( 'Zoom Magnify', 'jet-woo-product-gallery' ),
				'type'      => Controls_Manager::NUMBER,
				'default'   => 1,
				'min'       => 1,
				'max'       => 2,
				'step'      => 0.1,
				'condition' => [
					'enable_zoom'    => 'yes',
					'gallery_source' => 'products',
				],
			]
		);

		$this->add_control(
			'gallery_key',
			[
				'label'     => esc_html__( 'Gallery Key', 'jet-woo-product-gallery' ),
				'type'      => Controls_Manager::TEXT,
				'dynamic'   => [
					'active' => true,
				],
				'condition' => [
					'gallery_source' => 'cpt',
				],
			]
		);

		$this->add_control(
			'enable_feature_image',
			[
				'label'        => esc_html__( 'Enable Feature Image', 'jet-woo-product-gallery' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_on'     => esc_html__( 'Yes', 'jet-woo-product-gallery' ),
				'label_off'    => esc_html__( 'No', 'jet-woo-product-gallery' ),
				'return_value' => 'yes',
				'default'      => '',
				'condition'    => [
					'gallery_source' => 'cpt',
				],
			]
		);

		$this->add_control(
			'gallery_images',
			[
				'label'      => esc_html__( 'Add Images', 'jet-woo-product-gallery' ),
				'type'       => Controls_Manager::GALLERY,
				'default'    => [],
				'show_label' => false,
				'dynamic'    => [
					'active' => true,
				],
				'condition'  => [
					'gallery_source' => 'manual',
				],
			]
		);

		$this->add_control(
			'enable_video',
			[
				'label'     => esc_html__( 'Enable Video', 'jet-woo-product-gallery' ),
				'type'      => Controls_Manager::SWITCHER,
				'default'   => '',
				'condition' => [
					'gallery_source!' => 'products',
				],
			]
		);

		$this->add_control(
			'video_type',
			[
				'label'     => esc_html__( 'Video Type', 'jet-elements' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => 'youtube',
				'options'   => [
					'youtube'     => esc_html__( 'YouTube', 'jet-elements' ),
					'vimeo'       => esc_html__( 'Vimeo', 'jet-elements' ),
					'self_hosted' => esc_html__( 'Self Hosted', 'jet-elements' ),
				],
				'condition' => [
					'gallery_source' => [ 'cpt', 'manual' ],
					'enable_video'   => 'yes',
				],
			]
		);

		$this->add_control(
			'youtube_url',
			[
				'label'       => esc_html__( 'YouTube URL', 'jet-elements' ),
				'label_block' => true,
				'type'        => Controls_Manager::TEXT,
				'placeholder' => esc_html__( 'Enter your URL', 'jet-elements' ),
				'default'     => 'https://www.youtube.com/watch?v=CJO0u_HrWE8',
				'condition'   => [
					'enable_video'   => 'yes',
					'gallery_source' => [ 'cpt', 'manual' ],
					'video_type'     => 'youtube',
				],
				'dynamic'     => [
					'active'     => true,
					'categories' => [
						TagsModule::POST_META_CATEGORY,
						TagsModule::URL_CATEGORY,
					],
				],
			]
		);

		$this->add_control(
			'vimeo_url',
			[
				'label'       => esc_html__( 'Vimeo URL', 'jet-elements' ),
				'label_block' => true,
				'type'        => Controls_Manager::TEXT,
				'placeholder' => esc_html__( 'Enter your URL', 'jet-elements' ),
				'default'     => 'https://vimeo.com/235215203',
				'condition'   => [
					'enable_video'   => 'yes',
					'gallery_source' => [ 'cpt', 'manual' ],
					'video_type'     => 'vimeo',
				],
				'dynamic'     => [
					'active'     => true,
					'categories' => [
						TagsModule::POST_META_CATEGORY,
						TagsModule::URL_CATEGORY,
					],
				],
			]
		);

		$this->add_control(
			'self_hosted_url',
			[
				'label'      => esc_html__( 'Self Hosted URL', 'jet-elements' ),
				'type'       => Controls_Manager::MEDIA,
				'media_type' => 'video',
				'condition'  => [
					'enable_video'   => 'yes',
					'gallery_source' => [ 'cpt', 'manual' ],
					'video_type'     => 'self_hosted',
				],
				'dynamic'    => [
					'active'     => true,
					'categories' => [
						TagsModule::POST_META_CATEGORY,
						TagsModule::MEDIA_CATEGORY,
					],
				],
			]
		);

		$this->add_control(
			'custom_placeholder',
			[
				'label'     => esc_html__( 'Placeholder', 'jet-elements' ),
				'type'      => Controls_Manager::MEDIA,
				'dynamic'   => [ 'active' => true ],
				'condition' => [
					'gallery_source' => [ 'cpt', 'manual' ],
					'enable_video'   => 'yes',
				],
			]
		);

		$this->add_control(
			'enable_gallery',
			[
				'label'     => __( 'Enable Gallery', 'jet-woo-product-gallery' ),
				'type'      => Controls_Manager::SWITCHER,
				'separator' => 'before',
			]
		);

		$this->add_control(
			'gallery_trigger_type',
			[
				'label'     => __( 'Gallery Trigger Type', 'jet-woo-product-gallery' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => 'button',
				'options'   => [
					'button' => __( 'Button', 'jet-woo-product-gallery' ),
					'image'  => __( 'Image', 'jet-woo-product-gallery' ),
				],
				'condition' => [
					'enable_gallery' => 'yes',
				],
			]
		);

		$this->end_controls_section();

	}

	protected function register_base_gallery_controls() {

		$this->start_controls_section(
			'section_gallery_style',
			array(
				'label'      => esc_html__( 'Gallery', 'jet-woo-product-gallery' ),
				'tab'        => Controls_Manager::TAB_CONTENT,
				'show_label' => false,
				'condition'  => array(
					'enable_gallery' => array( 'yes' ),
				),
			)
		);

		$this->__add_advanced_icon_control(
			'gallery_button_icon',
			array(
				'label'       => esc_html__( 'Button Icon', 'jet-woo-product-gallery' ),
				'type'        => Controls_Manager::ICON,
				'label_block' => true,
				'file'        => '',
				'default'     => 'fa fa-search',
				'fa5_default' => array(
					'value'   => 'fas fa-search',
					'library' => 'fa-solid',
				),
			)
		);

		$this->add_control(
			'gallery_show_caption',
			array(
				'label'        => esc_html__( 'Show Caption', 'jet-woo-product-gallery' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_on'     => esc_html__( 'Yes', 'jet-woo-product-gallery' ),
				'label_off'    => esc_html__( 'No', 'jet-woo-product-gallery' ),
				'return_value' => 'yes',
				'default'      => 'yes',
			)
		);

		$this->add_control(
			'gallery_controls_heading',
			array(
				'label'     => esc_html__( 'Controls', 'jet-woo-product-gallery' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			)
		);

		$this->add_control(
			'gallery_show_fullscreen',
			array(
				'label'        => esc_html__( 'Show Full Screen', 'jet-woo-product-gallery' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_on'     => esc_html__( 'Yes', 'jet-woo-product-gallery' ),
				'label_off'    => esc_html__( 'No', 'jet-woo-product-gallery' ),
				'return_value' => 'yes',
				'default'      => 'yes',
			)
		);

		$this->add_control(
			'gallery_show_zoom',
			array(
				'label'        => esc_html__( 'Show Zoom', 'jet-woo-product-gallery' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_on'     => esc_html__( 'Yes', 'jet-woo-product-gallery' ),
				'label_off'    => esc_html__( 'No', 'jet-woo-product-gallery' ),
				'return_value' => 'yes',
				'default'      => 'yes',
			)
		);

		$this->add_control(
			'gallery_show_share',
			array(
				'label'        => esc_html__( 'Show Share', 'jet-woo-product-gallery' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_on'     => esc_html__( 'Yes', 'jet-woo-product-gallery' ),
				'label_off'    => esc_html__( 'No', 'jet-woo-product-gallery' ),
				'return_value' => 'yes',
				'default'      => 'yes',
			)
		);

		$this->add_control(
			'gallery_show_counter',
			array(
				'label'        => esc_html__( 'Show Counter', 'jet-woo-product-gallery' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_on'     => esc_html__( 'Yes', 'jet-woo-product-gallery' ),
				'label_off'    => esc_html__( 'No', 'jet-woo-product-gallery' ),
				'return_value' => 'yes',
				'default'      => 'yes',
			)
		);

		$this->add_control(
			'gallery_show_arrows',
			array(
				'label'        => esc_html__( 'Show Arrows', 'jet-woo-product-gallery' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_on'     => esc_html__( 'Yes', 'jet-woo-product-gallery' ),
				'label_off'    => esc_html__( 'No', 'jet-woo-product-gallery' ),
				'return_value' => 'yes',
				'default'      => 'yes',
			)
		);

		$this->end_controls_section();

	}

	protected function register_base_video_controls( $css_scheme ) {

		$this->start_controls_section(
			'section_video',
			array(
				'label' => esc_html__( 'Video', 'jet-woo-product-gallery' ),
			)
		);

		$this->add_control(
			'video_display_in',
			array(
				'label'   => esc_html__( 'Display Video In', 'jet-woo-product-gallery' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'content',
				'options' => array(
					'content' => esc_html__( 'Content', 'jet-woo-product-gallery' ),
					'popup'   => esc_html__( 'Popup', 'jet-woo-product-gallery' ),
				),
			)
		);

		$this->add_control(
			'aspect_ratio',
			array(
				'label'       => esc_html__( 'Aspect Ratio', 'jet-woo-product-gallery' ),
				'description' => esc_html__( 'Worked just with youtube and vimeo video types', 'jet-woo-product-gallery' ),
				'type'        => Controls_Manager::SELECT,
				'default'     => '16-9',
				'options'     => array(
					'16-9' => '16:9',
					'21-9' => '21:9',
					'9-16' => '9:16',
					'4-3'  => '4:3',
					'2-3'  => '2:3',
					'3-2'  => '3:2',
					'1-1'  => '1:1',
				),
			)
		);

		$this->add_control(
			'first_place_video',
			array(
				'label'     => esc_html__( 'Display Video at First Place', 'jet-woo-product-gallery' ),
				'type'      => Controls_Manager::SWITCHER,
				'default'   => '',
				'condition' => array(
					'video_display_in' => 'content',
				),
			)
		);

		$this->add_control(
			'video_options_heading',
			array(
				'label'     => esc_html__( 'Options', 'jet-woo-product-gallery' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			)
		);

		$this->add_control(
			'autoplay',
			array(
				'label'   => esc_html__( 'Autoplay', 'jet-woo-product-gallery' ),
				'type'    => Controls_Manager::SWITCHER,
				'default' => '',
			)
		);

		$this->add_control(
			'loop',
			array(
				'label'   => esc_html__( 'Loop', 'jet-woo-product-gallery' ),
				'type'    => Controls_Manager::SWITCHER,
				'default' => '',
			)
		);

		$this->register_product_video_in_content_controls( $css_scheme );

		$this->register_product_video_in_popup_controls( $css_scheme );

		$this->end_controls_section();

	}

	protected function register_product_video_in_content_controls( $css_scheme ) {

		$this->add_control(
			'video_overlay_heading',
			array(
				'label'     => esc_html__( 'Overlay', 'jet-woo-product-gallery' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
				'condition' => array(
					'video_display_in' => 'content',
				),
			)
		);

		$this->add_control(
			'overlay_color',
			array(
				'label'     => esc_html__( 'Overlay Color', 'jet-woo-product-gallery' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} ' . $css_scheme['overlay'] . ':before' => 'background-color: {{VALUE}};',
				),
				'condition' => array(
					'video_display_in' => 'content',
				),
			)
		);

		$this->add_control(
			'video_play_button_heading',
			array(
				'label'     => esc_html__( 'Play Button', 'jet-woo-product-gallery' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
				'condition' => array(
					'video_display_in' => 'content',
				),
			)
		);

		$this->add_control(
			'show_play_button',
			array(
				'label'     => esc_html__( 'Show Play Button', 'jet-woo-product-gallery' ),
				'type'      => Controls_Manager::SWITCHER,
				'default'   => 'yes',
				'condition' => array(
					'video_display_in' => 'content',
				),
			)
		);

		$this->add_control(
			'play_button_type',
			array(
				'label'     => esc_html__( 'Play Button Type', 'jet-woo-product-gallery' ),
				'type'      => Controls_Manager::CHOOSE,
				'default'   => 'icon',
				'toggle'    => false,
				'options'   => array(
					'icon'  => array(
						'title' => esc_html__( 'Icon', 'jet-woo-product-gallery' ),
						'icon'  => 'fa fa-play',
					),
					'image' => array(
						'title' => esc_html__( 'Image', 'jet-woo-product-gallery' ),
						'icon'  => 'fa fa-picture-o',
					),
				),
				'condition' => array(
					'video_display_in' => 'content',
					'show_play_button' => 'yes',
				),
			)
		);

		$this->__add_advanced_icon_control(
			'play_button_icon',
			array(
				'label'       => esc_html__( 'Icon', 'jet-woo-product-gallery' ),
				'type'        => Controls_Manager::ICON,
				'label_block' => true,
				'file'        => '',
				'default'     => 'fa fa-play',
				'fa5_default' => array(
					'value'   => 'fas fa-play',
					'library' => 'fa-solid',
				),
				'condition'   => array(
					'video_display_in' => 'content',
					'show_play_button' => 'yes',
					'play_button_type' => 'icon',
				),
			)
		);

		$this->add_control(
			'play_button_image',
			array(
				'label'     => esc_html__( 'Image', 'jet-woo-product-gallery' ),
				'type'      => Controls_Manager::MEDIA,
				'condition' => array(
					'video_display_in' => 'content',
					'show_play_button' => 'yes',
					'play_button_type' => 'image',
				),
			)
		);

	}

	protected function register_product_video_in_popup_controls( $css_scheme ) {

		$this->add_control(
			'popup_video_overlay_heading',
			array(
				'label'     => esc_html__( 'Popup Overlay', 'jet-woo-product-gallery' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
				'condition' => array(
					'video_display_in' => 'popup',
				),
			)
		);

		$this->add_control(
			'popup_overlay_color',
			array(
				'label'     => esc_html__( 'Overlay Color', 'jet-woo-product-gallery' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} ' . $css_scheme['video_popup_overlay'] => 'background-color: {{VALUE}};',
				),
				'condition' => array(
					'video_display_in' => 'popup',
				),
			)
		);

		$this->add_control(
			'video_popup_button_heading',
			array(
				'label'     => esc_html__( 'Popup Button', 'jet-woo-product-gallery' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
				'condition' => array(
					'video_display_in' => 'popup',
				),
			)
		);

		$this->__add_advanced_icon_control(
			'popup-button-icon',
			array(
				'label'       => esc_html__( 'Icon', 'jet-woo-product-gallery' ),
				'type'        => Controls_Manager::ICON,
				'label_block' => true,
				'file'        => '',
				'default'     => 'fa fa-video',
				'fa5_default' => array(
					'value'   => 'fas fa-video',
					'library' => 'fa-solid',
				),
				'condition'   => array(
					'video_display_in' => 'popup',
				),
			)
		);

	}

	/**
	 * Style controls for base elements.
	 */
	protected function register_base_photoswipe_trigger_controls_style( $css_scheme ) {
		$this->start_controls_section(
			'section_photoswipe_trigger_style',
			array(
				'label'      => esc_html__( 'Photoswipe Trigger', 'jet-woo-product-gallery' ),
				'tab'        => Controls_Manager::TAB_STYLE,
				'show_label' => false,
				'condition'  => [
					'gallery_trigger_type' => 'button',
				],
			)
		);

		$this->add_control(
			'photoswipe_trigger_show_on_hover',
			array(
				'label'        => esc_html__( 'Show On Hover', 'jet-woo-product-gallery' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_on'     => esc_html__( 'Yes', 'jet-woo-product-gallery' ),
				'label_off'    => esc_html__( 'No', 'jet-woo-product-gallery' ),
				'return_value' => 'show-on-hover',
				'default'      => '',
				'prefix_class' => 'jet-woo-product-gallery__trigger--',
			)
		);

		$this->add_control(
			'photoswipe_trigger_position',
			array(
				'label'        => esc_html__( 'Position', 'jet-woo-product-gallery' ),
				'type'         => Controls_Manager::SELECT,
				'default'      => 'top-right',
				'options'      => array(
					'top-right'    => esc_html__( 'Top Right', 'jet-woo-product-gallery' ),
					'bottom-right' => esc_html__( 'Bottom Right', 'jet-woo-product-gallery' ),
					'bottom-left'  => esc_html__( 'Bottom Left', 'jet-woo-product-gallery' ),
					'top-left'     => esc_html__( 'Top Left', 'jet-woo-product-gallery' ),
					'center'       => esc_html__( 'Center Center', 'jet-woo-product-gallery' ),
				),
				'prefix_class' => 'jet-woo-product-gallery__trigger--',
			)
		);

		$this->add_responsive_control(
			'photoswipe_trigger_size',
			array(
				'label'      => esc_html__( 'Size', 'jet-woo-product-gallery' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array(
					'px',
				),
				'range'      => array(
					'px' => array(
						'min' => 20,
						'max' => 200,
					),
				),
				'default'    => array(
					'size' => 30,
					'unit' => 'px',
				),
				'selectors'  => array(
					'{{WRAPPER}} ' . $css_scheme['photoswipe-trigger'] => 'width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}};',
				),
			)
		);

		$this->add_responsive_control(
			'photoswipe_trigger_icon_size',
			array(
				'label'      => esc_html__( 'Icon Size', 'jet-woo-product-gallery' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array(
					'px',
				),
				'range'      => array(
					'px' => array(
						'min' => 0,
						'max' => 50,
					),
				),
				'default'    => array(
					'size' => 18,
					'unit' => 'px',
				),
				'selectors'  => array(
					'{{WRAPPER}} ' . $css_scheme['photoswipe-trigger'] . ' .jet-woo-product-gallery__trigger-icon' => 'font-size: {{SIZE}}{{UNIT}};',
				),
			)
		);

		$this->start_controls_tabs( 'photoswipe_trigger_style_tabs' );

		$this->start_controls_tab(
			'photoswipe_trigger_normal_styles',
			array(
				'label' => esc_html__( 'Normal', 'jet-woo-product-gallery' ),
			)
		);

		$this->add_control(
			'photoswipe_trigger_normal_color',
			array(
				'label'     => esc_html__( 'Color', 'jet-woo-product-gallery' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} ' . $css_scheme['photoswipe-trigger'] . ' .jet-woo-product-gallery__trigger-icon' => 'color: {{VALUE}}',
				),
			)
		);

		$this->add_control(
			'photoswipe_trigger_normal_background_color',
			array(
				'label'     => esc_html__( ' Background Color', 'jet-woo-product-gallery' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} ' . $css_scheme['photoswipe-trigger'] => 'background-color: {{VALUE}}',
				),
			)
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'photoswipe_trigger_hover_styles',
			array(
				'label' => esc_html__( 'Hover', 'jet-woo-product-gallery' ),
			)
		);

		$this->add_control(
			'photoswipe_trigger_hover_color',
			array(
				'label'     => esc_html__( 'Color', 'jet-woo-product-gallery' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} ' . $css_scheme['photoswipe-trigger'] . ':hover' . ' .jet-woo-product-gallery__trigger-icon' => 'color: {{VALUE}}',
				),
			)
		);

		$this->add_control(
			'photoswipe_trigger_hover_background_color',
			array(
				'label'     => esc_html__( 'Background Color', 'jet-woo-product-gallery' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} ' . $css_scheme['photoswipe-trigger'] . ':hover' => 'background-color: {{VALUE}}',
				),
			)
		);

		$this->add_control(
			'photoswipe_trigger_hover_border_color',
			array(
				'label'     => esc_html__( 'Border Color', 'jet-woo-product-gallery' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} ' . $css_scheme['photoswipe-trigger'] . ':hover' => 'border-color: {{VALUE}}',
				),
			)
		);

		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->add_group_control(
			Group_Control_Border::get_type(),
			array(
				'name'        => 'photoswipe_trigger_border',
				'label'       => esc_html__( 'Border', 'jet-woo-product-gallery' ),
				'placeholder' => '1px',
				'default'     => '1px',
				'selector'    => '{{WRAPPER}} ' . $css_scheme['photoswipe-trigger'],
			)
		);

		$this->add_control(
			'photoswipe_trigger_border_radius',
			array(
				'label'      => esc_html__( 'Border Radius', 'jet-woo-product-gallery' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%' ),
				'selectors'  => array(
					'{{WRAPPER}} ' . $css_scheme['photoswipe-trigger'] => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}}; overflow:hidden;',
				),
			)
		);

		$this->add_responsive_control(
			'photoswipe_trigger_margin',
			array(
				'label'      => esc_html__( 'Margin', 'jet-woo-product-gallery' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%' ),
				'selectors'  => array(
					'{{WRAPPER}} ' . $css_scheme['photoswipe-trigger'] => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->end_controls_section();
	}

	protected function register_base_photoswipe_gallery_controls_style( $css_scheme ) {

		$this->start_controls_section(
			'photoswipe_gallery_style',
			array(
				'label'      => esc_html__( 'Photoswipe Gallery', 'jet-woo-product-gallery' ),
				'tab'        => Controls_Manager::TAB_STYLE,
				'show_label' => false,
			)
		);

		$this->add_control(
			'photoswipe_gallery_background_color',
			array(
				'label'     => esc_html__( 'Background Color', 'jet-woo-product-gallery' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					$css_scheme['photoswipe-bg'] => 'background-color: {{VALUE}}',
				),
			)
		);

		$this->add_control(
			'photoswipe_gallery_controls_heading',
			array(
				'label' => esc_html__( 'Photoswipe Controls', 'jet-woo-product-gallery' ),
				'type'  => Controls_Manager::HEADING,
			)
		);

		$this->start_controls_tabs( 'photoswipe_gallery_controls_style_tabs' );

		$this->start_controls_tab(
			'photoswipe_gallery_controls_normal_styles',
			array(
				'label' => esc_html__( 'Normal', 'jet-woo-product-gallery' ),
			)
		);

		$this->add_control(
			'photoswipe_gallery_controls_normal_background_color',
			array(
				'label'     => esc_html__( ' Background Color', 'jet-woo-product-gallery' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					$css_scheme['photoswipe-controls'] => 'background-color: {{VALUE}} !important',
				),
			)
		);

		$this->add_control(
			'photoswipe_gallery_controls_normal_border_radius',
			array(
				'label'      => esc_html__( 'Border Radius', 'jet-woo-product-gallery' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%' ),
				'selectors'  => array(
					$css_scheme['photoswipe-controls'] => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}}; overflow:hidden;',
				),
			)
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'photoswipe_gallery_controls_hover_styles',
			array(
				'label' => esc_html__( 'Hover', 'jet-woo-product-gallery' ),
			)
		);

		$this->add_control(
			'photoswipe_gallery_controls_hover_background_color',
			array(
				'label'     => esc_html__( ' Background Color', 'jet-woo-product-gallery' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					$css_scheme['photoswipe-controls-hover'] => 'background-color: {{VALUE}} !important',
				),
			)
		);

		$this->add_control(
			'photoswipe_gallery_controls_hover_border_radius',
			array(
				'label'      => esc_html__( 'Border Radius', 'jet-woo-product-gallery' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%' ),
				'selectors'  => array(
					$css_scheme['photoswipe-controls-hover'] => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}}; overflow:hidden;',
				),
			)
		);

		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->end_controls_section();
	}

	protected function register_base_video_popup_button_controls_style( $css_scheme ) {
		$this->start_controls_section(
			'video_popup_button_style',
			array(
				'label'      => esc_html__( 'Video Popup Button', 'jet-woo-product-gallery' ),
				'tab'        => Controls_Manager::TAB_STYLE,
				'show_label' => false,
			)
		);

		$this->add_responsive_control(
			'video_popup_button_icon_size',
			array(
				'label'      => esc_html__( 'Icon Size', 'jet-woo-product-gallery' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array(
					'px',
				),
				'range'      => array(
					'px' => array(
						'min' => 0,
						'max' => 50,
					),
				),
				'default'    => array(
					'size' => 18,
					'unit' => 'px',
				),
				'selectors'  => array(
					'{{WRAPPER}} ' . $css_scheme['video-popup-button'] . ' .jet-woo-product-video__popup-button-icon' => 'font-size: {{SIZE}}{{UNIT}};',
				),
			)
		);

		$this->start_controls_tabs( 'video_popup_button_style_tabs' );

		$this->start_controls_tab(
			'video_popup_button_normal_styles',
			array(
				'label' => esc_html__( 'Normal', 'jet-woo-product-gallery' ),
			)
		);

		$this->add_control(
			'video_popup_button_normal_color',
			array(
				'label'     => esc_html__( 'Color', 'jet-woo-product-gallery' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} ' . $css_scheme['video-popup-button'] . ' .jet-woo-product-video__popup-button-icon' => 'color: {{VALUE}}',
				),
			)
		);

		$this->add_control(
			'video_popup_button_normal_background_color',
			array(
				'label'     => esc_html__( ' Background Color', 'jet-woo-product-gallery' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} ' . $css_scheme['video-popup-button'] => 'background-color: {{VALUE}}',
				),
			)
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'video_popup_button_hover_styles',
			array(
				'label' => esc_html__( 'Hover', 'jet-woo-product-gallery' ),
			)
		);

		$this->add_control(
			'video_popup_button_hover_color',
			array(
				'label'     => esc_html__( 'Color', 'jet-woo-product-gallery' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} ' . $css_scheme['video-popup-button'] . ':hover' . ' .jet-woo-product-video__popup-button-icon' => 'color: {{VALUE}}',
				),
			)
		);

		$this->add_control(
			'video_popup_button_hover_background_color',
			array(
				'label'     => esc_html__( 'Background Color', 'jet-woo-product-gallery' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} ' . $css_scheme['video-popup-button'] . ':hover' => 'background-color: {{VALUE}}',
				),
			)
		);

		$this->add_control(
			'video_popup_button_hover_border_color',
			array(
				'label'     => esc_html__( 'Border Color', 'jet-woo-product-gallery' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} ' . $css_scheme['video-popup-button'] . ':hover' => 'border-color: {{VALUE}}',
				),
			)
		);

		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->add_group_control(
			Group_Control_Border::get_type(),
			array(
				'name'        => 'video_popup_button_border',
				'label'       => esc_html__( 'Border', 'jet-woo-product-gallery' ),
				'placeholder' => '1px',
				'default'     => '1px',
				'selector'    => '{{WRAPPER}} ' . $css_scheme['video-popup-button'],
			)
		);

		$this->add_control(
			'video_popup_button_border_radius',
			array(
				'label'      => esc_html__( 'Border Radius', 'jet-woo-product-gallery' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%' ),
				'selectors'  => array(
					'{{WRAPPER}} ' . $css_scheme['video-popup-button'] => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}}; overflow:hidden;',
				),
			)
		);

		$this->add_responsive_control(
			'video_popup_button_padding',
			array(
				'label'      => esc_html__( 'Padding', 'jet-woo-product-gallery' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%' ),
				'selectors'  => array(
					'{{WRAPPER}} ' . $css_scheme['video-popup-button'] => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->add_responsive_control(
			'video_popup_button_margin',
			array(
				'label'      => esc_html__( 'Margin', 'jet-woo-product-gallery' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%' ),
				'selectors'  => array(
					'{{WRAPPER}} ' . $css_scheme['video-popup-button'] => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->add_responsive_control(
			'video_popup_button_alignment',
			array(
				'label'     => esc_html__( 'Alignment', 'jet-woo-product-gallery' ),
				'type'      => Controls_Manager::CHOOSE,
				'options'   => array(
					'left'   => array(
						'title' => esc_html__( 'Left', 'jet-woo-product-gallery' ),
						'icon'  => 'eicon-text-align-left',
					),
					'center' => array(
						'title' => esc_html__( 'Center', 'jet-woo-product-gallery' ),
						'icon'  => 'eicon-text-align-center',
					),
					'right'  => array(
						'title' => esc_html__( 'Right', 'jet-woo-product-gallery' ),
						'icon'  => 'eicon-text-align-right',
					),
				),
				'selectors' => array(
					'{{WRAPPER}} ' . $css_scheme['video_popup_wrapper'] => 'text-align: {{VALUE}};',
				),
				'classes'   => 'elementor-control-align',
			)
		);

		$this->end_controls_section();
	}

	protected function register_base_video_play_button_controls_style( $css_scheme ) {
		$this->start_controls_section(
			'section_video_play_button_style',
			array(
				'label' => esc_html__( 'Play Button', 'jet-woo-product-gallery' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_responsive_control(
			'video_play_button_size',
			array(
				'label'     => esc_html__( 'Icon/Image Size', 'jet-woo-product-gallery' ),
				'type'      => Controls_Manager::SLIDER,
				'range'     => array(
					'px' => array(
						'min' => 10,
						'max' => 300,
					),
				),
				'selectors' => array(
					'{{WRAPPER}} ' . $css_scheme['video-play-button']          => 'font-size: {{SIZE}}{{UNIT}};',
					'{{WRAPPER}} ' . $css_scheme['video-play-button'] . ' img' => 'width: {{SIZE}}{{UNIT}}; height: auto;',
				),
			)
		);

		$this->add_control(
			'video_play_button_image_border_radius',
			array(
				'label'      => esc_html__( 'Image Border Radius', 'jet-woo-product-gallery' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%' ),
				'selectors'  => array(
					'{{WRAPPER}} ' . $css_scheme['video-play-button-image'] => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
				'condition'  => array(
					'video_play_button_type' => 'image',
				),
			)
		);

		$this->start_controls_tabs( 'video_play_button_tabs' );

		$this->start_controls_tab(
			'video_play_button_normal_tab',
			array(
				'label' => esc_html__( 'Normal', 'jet-woo-product-gallery' ),
			)
		);

		$this->add_control(
			'video_play_button_color',
			array(
				'label'     => esc_html__( 'Color', 'jet-woo-product-gallery' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} ' . $css_scheme['video-play-button'] => 'color: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'video_play_button__background_color',
			array(
				'label'     => esc_html__( 'Background Color', 'jet-woo-product-gallery' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} ' . $css_scheme['video-play-button'] => 'background-color: {{VALUE}}',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			array(
				'name'     => 'video_play_button_box_shadow',
				'selector' => '{{WRAPPER}} ' . $css_scheme['video-play-button'],
			)
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'video_play_button_hover_tab',
			array(
				'label' => esc_html__( 'Hover', 'jet-woo-product-gallery' ),
			)
		);

		$this->add_control(
			'video_play_button_color_hover',
			array(
				'label'     => esc_html__( 'Color', 'jet-woo-product-gallery' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} ' . $css_scheme['video-play-overlay'] . ':hover ' . $css_scheme['video-play-button'] => 'color: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'video_play_button_hover_background_color',
			array(
				'label'     => esc_html__( 'Background Color', 'jet-woo-product-gallery' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} ' . $css_scheme['video-play-overlay'] . ':hover ' . $css_scheme['video-play-button'] => 'background-color: {{VALUE}}',
				),
			)
		);

		$this->add_control(
			'video_play_button_border_color_hover',
			array(
				'label'     => esc_html__( 'Border Color', 'jet-woo-product-gallery' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} ' . $css_scheme['video-play-overlay'] . ':hover ' . $css_scheme['video-play-button'] => 'border-color: {{VALUE}};',
				),
				'condition' => array(
					'video_play_button_border_border!' => '',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			array(
				'name'     => 'video_play_button_box_shadow_hover',
				'selector' => '{{WRAPPER}} ' . $css_scheme['video-play-overlay'] . ':hover ' . $css_scheme['video-play-button'],
			)
		);

		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->add_responsive_control(
			'video_play_button_padding',
			array(
				'label'      => esc_html__( 'Padding', 'jet-woo-product-gallery' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%' ),
				'separator'  => 'before',
				'selectors'  => array(
					'{{WRAPPER}} ' . $css_scheme['video-play-button'] => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->add_responsive_control(
			'video_play_button_margin',
			array(
				'label'      => esc_html__( 'Margin', 'jet-woo-product-gallery' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%' ),
				'selectors'  => array(
					'{{WRAPPER}} ' . $css_scheme['video-play-button'] => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			array(
				'name'     => 'video_play_button_border',
				'selector' => '{{WRAPPER}} ' . $css_scheme['video-play-button'],
			)
		);

		$this->add_control(
			'video_play_button_border_radius',
			array(
				'label'      => esc_html__( 'Border Radius', 'jet-woo-product-gallery' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%' ),
				'selectors'  => array(
					'{{WRAPPER}} ' . $css_scheme['video-play-button'] => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->end_controls_section();
	}

	/**
	 * Register widget style controls. Specific for each widget.
	 *
	 * @return void
	 */
	public function register_product_gallery_controls() {
	}

	/**
	 * Get globally affected template
	 *
	 * @param null $name
	 *
	 * @return bool|mixed|string
	 */
	public function __get_global_template( $name = null ) {

		$template = call_user_func( array( $this, sprintf( '__get_%s_template', $this->__context ) ) );

		if ( ! $template ) {
			$template = jet_woo_product_gallery()->get_template( $this->get_name() . '/global/' . $name . '.php' );
		}

		return $template;

	}

	/**
	 * Get front-end template
	 *
	 * @param null $name
	 *
	 * @return bool|string
	 */
	public function __get_render_template( $name = null ) {
		return jet_woo_product_gallery()->get_template( $this->get_name() . '/render/' . $name . '.php' );
	}

	/**
	 * Get editor template
	 *
	 * @param null $name
	 *
	 * @return bool|string
	 */
	public function __get_edit_template( $name = null ) {
		return jet_woo_product_gallery()->get_template( $this->get_name() . '/edit/' . $name . '.php' );
	}

	/**
	 * Get global looped template for settings
	 * Required only to process repeater settings.
	 *
	 * @param string $name    Base template name.
	 * @param string $setting Repeater setting that provide data for template.
	 *
	 * @return void
	 */
	public function __get_global_looped_template( $name = null, $setting = null ) {

		$templates = array(
			'start' => $this->__get_global_template( $name . '-loop-start' ),
			'loop'  => $this->__get_global_template( $name . '-loop-item' ),
			'end'   => $this->__get_global_template( $name . '-loop-end' ),
		);

		call_user_func(
			array( $this, sprintf( '__get_%s_looped_template', $this->__context ) ), $templates, $setting
		);

	}

	/**
	 * Get render mode looped template
	 *
	 * @param array $templates
	 * @param null  $setting
	 *
	 * @return void
	 */
	public function __get_render_looped_template( $templates = array(), $setting = null ) {

		$loop = $this->get_settings( $setting );

		if ( empty( $loop ) ) {
			return;
		}

		if ( ! empty( $templates['start'] ) ) {
			include $templates['start'];
		}

		foreach ( $loop as $item ) {

			$this->__processed_item = $item;
			if ( ! empty( $templates['start'] ) ) {
				include $templates['loop'];
			}
			$this->__processed_index++;
		}

		$this->__processed_item  = false;
		$this->__processed_index = 0;

		if ( ! empty( $templates['end'] ) ) {
			include $templates['end'];
		}

	}

	/**
	 * Get edit mode looped template
	 *
	 * @param array $templates
	 * @param null  $setting
	 *
	 * @return void
	 */
	public function __get_edit_looped_template( $templates = array(), $setting = null ) {
		?>
		<# if ( settings.<?php echo $setting; ?> ) { #>
		<?php
		if ( ! empty( $templates['start'] ) ) {
			include $templates['start'];
		}
		?>
		<# _.each( settings.<?php echo $setting; ?>, function( item ) { #>
		<?php
		if ( ! empty( $templates['loop'] ) ) {
			include $templates['loop'];
		}
		?>
		<# } ); #>
		<?php
		if ( ! empty( $templates['end'] ) ) {
			include $templates['end'];
		}
		?>
		<# } #>
		<?php
	}

	/**
	 * Get current looped item dependents from contexßt.
	 *
	 * @param array  $keys
	 * @param string $format
	 *
	 * @return mixed
	 */
	public function __loop_item( $keys = array(), $format = '%s' ) {
		return call_user_func( array( $this, sprintf( '__%s_loop_item', $this->__context ) ), $keys, $format );
	}

	/**
	 * Loop edit item
	 *
	 * @param array  $keys
	 * @param string $format
	 *
	 * @return false|string
	 */
	public function __edit_loop_item( $keys = array(), $format = '%s' ) {

		$settings = $keys[0];

		if ( isset( $keys[1] ) ) {
			$settings .= '.' . $keys[1];
		}

		ob_start();

		echo '<# if ( item.' . $settings . ' ) { #>';
		printf( $format, '{{{ item.' . $settings . ' }}}' );
		echo '<# } #>';

		return ob_get_clean();

	}

	/**
	 * Loop render item
	 *
	 * @param array  $keys
	 * @param string $format
	 *
	 * @return false|string
	 */
	public function __render_loop_item( $keys = array(), $format = '%s' ) {

		$item = $this->__processed_item;

		$key        = $keys[0];
		$nested_key = isset( $keys[1] ) ? $keys[1] : false;

		if ( empty( $item ) || ! isset( $item[ $key ] ) ) {
			return false;
		}

		if ( false === $nested_key || ! is_array( $item[ $key ] ) ) {
			$value = $item[ $key ];
		} else {
			$value = isset( $item[ $key ][ $nested_key ] ) ? $item[ $key ][ $nested_key ] : false;
		}

		if ( ! empty( $value ) ) {
			return sprintf( $format, $value );
		}

	}

	/**
	 * Include global template if any of passed settings is defined
	 *
	 * @param null  $name
	 * @param array $settings
	 *
	 * @return void
	 */
	public function __glob_inc_if( $name = null, $settings = array() ) {
		$template = $this->__get_global_template( $name );

		call_user_func( array( $this, sprintf( '__%s_inc_if', $this->__context ) ), $template, $settings );
	}

	/**
	 * Include render template if any of passed setting is not empty
	 *
	 * @param null  $file
	 * @param array $settings
	 *
	 * @return void
	 */
	public function __render_inc_if( $file = null, $settings = array() ) {
		foreach ( $settings as $setting ) {
			$val = $this->get_settings( $setting );

			if ( ! empty( $val ) ) {
				include $file;

				return;
			}
		}
	}

	/**
	 * Include edit template if any of passed setting is not empty
	 *
	 * @param null  $file
	 * @param array $settings
	 *
	 * @return void
	 */
	public function __edit_inc_if( $file = null, $settings = array() ) {

		$condition = null;
		$sep       = null;

		foreach ( $settings as $setting ) {
			$condition .= $sep . 'settings.' . $setting;
			$sep       = ' || ';
		} ?>

		<# if ( <?php echo $condition; ?> ) { #>
		<?php include $file; ?>
		<# } #>

		<?php
	}

	/**
	 * Open standard wrapper
	 *
	 * @return void
	 */
	public function __open_wrap() {

		$attributes_variation_images = $this->get_render_attribute_string( 'gallery_variation_images_data' );

		printf(
			'<div class="elementor-%s jet-woo-product-gallery" data-gallery-settings="%s" %s >',
			$this->get_name(),
			$this->__generate_gallery_setting_json(),
			$attributes_variation_images
		);

	}

	/**
	 * Returns rendered gallery attachments depending on gallery source.
	 */
	public function __get_rendered_gallery() {

		global $post;

		$settings            = $this->get_settings_for_display();
		$source              = $settings['gallery_source'];
		$post_id             = null;
		$attachment_ids      = [];
		$with_featured_image = false;


		switch ( $source ) {
			case 'products':
				if ( ! empty( $settings['product_id'] ) ) {
					$product = wc_get_product( $settings['product_id'] );
				} else {
					$product = wc_get_product();
				}

				if ( ! empty( $product ) ) {
					if ( 'variable' === $product->get_type() ) {
						$variation_images = $this->get_variation_images_data( $post, $product, $settings );

						$this->set_render_attribute(
							'gallery_variation_images_data',
							'data-variation-images',
							$variation_images
						);
					}

					$post_id             = $product->get_id();
					$attachment_ids      = $product->get_gallery_image_ids();
					$with_featured_image = ! filter_var( $settings['disable_feature_image'], FILTER_VALIDATE_BOOLEAN );
				}

				break;

			case 'cpt':
				$post_id             = $post->ID;
				$with_featured_image = filter_var( $settings['enable_feature_image'], FILTER_VALIDATE_BOOLEAN );


				if ( ! empty( $settings['gallery_key'] ) ) {
					$gallery_data = get_post_meta( $post->ID, $settings['gallery_key'], true );

					if ( is_array( $gallery_data ) ) {
						foreach ( $gallery_data as $data ) {
							$attachment_ids[] = $data['id'];
						}
					} else {
						if ( empty( $gallery_data ) ) {
							$gallery_data = $settings['gallery_key'];
						}

						$gallery_data = explode( ',', $gallery_data );

						foreach ( $gallery_data as $data ) {
							if ( is_numeric( $data ) ) {
								$attachment_ids[] = $data;
							} elseif ( filter_var( $data, FILTER_VALIDATE_URL ) !== false ) {
								$attachment_ids[] = attachment_url_to_postid( $data );
							}
						}
					}
				}

				break;

			case 'manual':
				$selected_images = $settings['gallery_images'];

				if ( ! empty( $selected_images ) ) {
					foreach ( $selected_images as $image ) {
						if ( $image ) {
							$attachment_ids[] = $image['id'];
						}
					}
				}

				break;

			default:
				break;
		}

		if ( $with_featured_image || $attachment_ids ) {
			$this->__context = 'render';

			$this->__open_wrap();
			include $this->__get_global_template( 'index' );
			$this->__close_wrap();
		} else {
			printf(
				'<div class="jet-woo-product-gallery__content">%s</div>',
				esc_html__( 'Gallery not found.', 'jet-woo-product-gallery' )
			);
		}

	}

	/**
	 * Generate variations images data
	 *
	 * @param $post
	 * @param $_product
	 * @param $settings
	 *
	 * @return string
	 */
	public function get_variation_images_data( $post, $_product, $settings ) {

		$variation_images = array();

		$variations = $_product->get_available_variations();
		foreach ( $variations as $variation ) {
			$variation_props = wc_get_product_attachment_props( $variation['image_id'], $post );

			// Thumbnail version.
			if ( isset( $settings['thumbs_image_size'] ) ) {
				$variation_src                  = wp_get_attachment_image_src( $variation['image_id'], $settings['thumbs_image_size'] );
				$variation_props['thumb_src']   = $variation_src[0];
				$variation_props['thumb_src_w'] = $variation_src[1];
				$variation_props['thumb_src_h'] = $variation_src[2];
			}

			// Image source.
			$variation_src                              = wp_get_attachment_image_src( $variation['image_id'], $settings['image_size'] );
			$variation_props['src']                     = $variation_src[0];
			$variation_props['src_w']                   = $variation_src[1];
			$variation_props['src_h']                   = $variation_src[2];
			$variation_props['srcset']                  = function_exists( 'wp_get_attachment_image_srcset' ) ? wp_get_attachment_image_srcset( $variation['image_id'], $settings['image_size'] ) : false;
			$variation_props['sizes']                   = function_exists( 'wp_get_attachment_image_sizes' ) ? wp_get_attachment_image_sizes( $variation['image_id'], $settings['image_size'] ) : false;
			$variation_images[ $variation['image_id'] ] = $variation_props;
		}

		$variation_images = json_encode( $variation_images );

		return $variation_images;

	}

	/**
	 * Close standard wrapper
	 *
	 * @return void
	 */
	public function __close_wrap() {
		echo '</div>';
	}

	/**
	 * Print HTML markup if passed setting not empty.
	 *
	 * @param null   $setting Passed setting.
	 * @param string $format  Required markup.
	 *
	 * @return string|void
	 */
	public function __html( $setting = null, $format = '%s' ) {
		call_user_func( array( $this, sprintf( '__%s_html', $this->__context ) ), $setting, $format );
	}

	/**
	 * Returns HTML markup if passed setting not empty.
	 *
	 * @param null   $setting Passed setting.
	 * @param string $format  Required markup.
	 *
	 * @return string|void
	 */
	public function __get_html( $setting = null, $format = '%s' ) {

		ob_start();
		$this->__html( $setting, $format );

		return ob_get_clean();

	}

	/**
	 * Print HTML template
	 *
	 * @param null   $setting
	 * @param string $format
	 *
	 * @return string
	 */
	public function __render_html( $setting = null, $format = '%s' ) {

		if ( is_array( $setting ) ) {
			$key     = $setting[1];
			$setting = $setting[0];
		}

		$val = $this->get_settings( $setting );

		if ( ! is_array( $val ) && '0' === $val ) {
			printf( $format, $val );
		}

		if ( is_array( $val ) && empty( $val[ $key ] ) ) {
			return '';
		}

		if ( ! is_array( $val ) && empty( $val ) ) {
			return '';
		}

		if ( is_array( $val ) ) {
			printf( $format, $val[ $key ] );
		} else {
			printf( $format, $val );
		}

	}

	/**
	 * Print underscore template
	 *
	 * @param null   $setting
	 * @param string $format
	 *
	 * @return void
	 */
	public function __edit_html( $setting = null, $format = '%s' ) {

		if ( is_array( $setting ) ) {
			$setting = $setting[0] . '.' . $setting[1];
		}

		echo '<# if ( settings.' . $setting . ' ) { #>';
		printf( $format, '{{{ settings.' . $setting . ' }}}' );
		echo '<# } #>';

	}

	/**
	 * Returns featured image placeholder depending on gallery source.
	 *
	 * @return string
	 */
	public function __get_featured_image_placeholder() {

		$settings = $this->get_settings();

		if ( 'products' === $settings['gallery_source'] ) {
			return wc_placeholder_img_src( 'large' );
		} else {
			return \Elementor\Utils::get_placeholder_image_src();
		}

	}

	/**
	 * Generate setting json
	 *
	 * @return string
	 */
	public function __generate_gallery_setting_json() {

		$module_settings = $this->get_settings();

		$settings = array(
			'enableGallery' => filter_var( $module_settings['enable_gallery'], FILTER_VALIDATE_BOOLEAN ),
			'enableZoom'    => filter_var( $module_settings['enable_zoom'], FILTER_VALIDATE_BOOLEAN ),
			'zoomMagnify'   => isset( $module_settings['zoom_magnify'] ) ? $module_settings['zoom_magnify'] : 1,
			'caption'       => filter_var( $module_settings['gallery_show_caption'], FILTER_VALIDATE_BOOLEAN ),
			'zoom'          => filter_var( $module_settings['gallery_show_zoom'], FILTER_VALIDATE_BOOLEAN ),
			'fullscreen'    => filter_var( $module_settings['gallery_show_fullscreen'], FILTER_VALIDATE_BOOLEAN ),
			'share'         => filter_var( $module_settings['gallery_show_share'], FILTER_VALIDATE_BOOLEAN ),
			'counter'       => filter_var( $module_settings['gallery_show_counter'], FILTER_VALIDATE_BOOLEAN ),
			'arrows'        => filter_var( $module_settings['gallery_show_arrows'], FILTER_VALIDATE_BOOLEAN ),
		);

		if ( $this->product_has_video() ) {
			$settings['hasVideo']      = true;
			$settings['videoType']     = jet_woo_gallery_video_integration()->get_video_type( $module_settings );
			$settings['videoIn']       = $module_settings['video_display_in'];
			$settings['videoAutoplay'] = filter_var( $module_settings['autoplay'], FILTER_VALIDATE_BOOLEAN );
			$settings['videoLoop']     = filter_var( $module_settings['loop'], FILTER_VALIDATE_BOOLEAN );
			$settings['videoFirst']    = 'content' === $module_settings['video_display_in'] ? filter_var( $module_settings['first_place_video'], FILTER_VALIDATE_BOOLEAN ) : false;
		}

		return htmlspecialchars( json_encode( $settings ) );

	}

	/**
	 * Check video existence
	 *
	 * @return bool
	 */
	public function product_has_video() {

		$video_url = $this->__get_video_url();

		if ( empty( $video_url ) ) {
			return false;
		}

		return true;

	}

	/**
	 * Return url on iframe video placeholder
	 *
	 * @param $type
	 * @param $url
	 *
	 * @return string
	 */
	public function __get_video_iframe_thumbnail_url( $type, $url ) {

		$oembed = _wp_oembed_get_object();
		$data   = $oembed->get_data( $url );

		$thumb_url = $data->thumbnail_url;

		if ( 'youtube' === $type ) {
			$thumb_url = str_replace( '/hqdefault.', '/maxresdefault.', $thumb_url );
		}

		return esc_url( $thumb_url );

	}

	/**
	 * Check if video has custom placeholder
	 *
	 * @return bool
	 */
	public function __video_has_custom_placeholder( $settings ) {
		return ! empty( jet_woo_gallery_video_integration()->get_video_custom_placeholder( $settings ) );
	}

	/**
	 * Return url for video thumbnail
	 *
	 * @return string
	 */
	public function __get_video_thumbnail_url() {

		$thumb_url  = '';
		$settings   = $this->get_settings_for_display();
		$video_url  = $this->__get_video_url();
		$video_type = jet_woo_gallery_video_integration()->get_video_type( $settings );

		if ( ! $this->product_has_video() ) {
			return '';
		}

		if ( $this->__video_has_custom_placeholder( $settings ) ) {
			$video_placeholder_id  = jet_woo_gallery_video_integration()->get_video_custom_placeholder( $settings );
			$video_placeholder_src = wp_get_attachment_image_src( $video_placeholder_id, 'full' );
			$thumb_url             = $video_placeholder_src[0];
		} elseif ( in_array( $video_type, array( 'youtube', 'vimeo' ) ) ) {
			$thumb_url = $this->__get_video_iframe_thumbnail_url( $video_type, $video_url );
		}

		if ( empty( $thumb_url ) ) {
			return '';
		}

		return esc_url( $thumb_url );

	}

	/**
	 * Return generated video html
	 *
	 * @return bool|string
	 */
	public function __get_video_html() {

		$settings   = $this->get_settings();
		$video_url  = $this->__get_video_url();
		$video_type = jet_woo_gallery_video_integration()->get_video_type( $settings );
		$video_html = '';

		if ( ! $this->product_has_video() ) {
			return '';
		}

		if ( 'self_hosted' === $video_type ) {
			$self_hosted_params = $this->__get_self_hosted_video_params();

			$this->add_render_attribute( 'video_player', 'class', 'jet-woo-product-video-player' );
			$this->add_render_attribute( 'video_player', 'class', 'jet-woo-product-video-mejs-player' );
			$this->add_render_attribute( 'video_player', 'src', $video_url );
			$this->add_render_attribute( 'video_player', $self_hosted_params );

			if ( filter_var( $settings['show_play_button'], FILTER_VALIDATE_BOOLEAN ) ) {
				$this->add_render_attribute( 'video_player', 'class', 'jet-woo-product-video-custom-play-button' );
			}

			$video_html = '<video ' . $this->get_render_attribute_string( 'video_player' ) . '></video>';
		} else {
			$embed_params  = $this->__get_embed_video_params();
			$embed_options = array(
				'lazy_load' => false,
			);

			$embed_attr = array(
				'class' => 'jet-woo-product-video-iframe',
				'allow' => 'autoplay;encrypted-media',
			);

			$video_html = Embed::get_embed_html( $video_url, $embed_params, $embed_options, $embed_attr );
		}

		return $video_html;

	}

	/**
	 * Return parameters for self hosted video
	 *
	 * @return array
	 */
	public function __get_self_hosted_video_params() {

		$settings = $this->get_settings_for_display();

		$params = array();

		if ( 'content' === $settings['video_display_in'] ) {
			if ( filter_var( $settings['autoplay'], FILTER_VALIDATE_BOOLEAN ) ) {
				$params['autoplay'] = '';
			}
		}

		if ( filter_var( $settings['loop'], FILTER_VALIDATE_BOOLEAN ) ) {
			$params['loop'] = '';
		}

		$params['style'] = 'max-width: 100%;';
		$controls        = array( 'playpause', 'progress', 'current', 'duration', 'volume', 'fullscreen' );

		if ( in_array( 'current', $controls ) ) {
			$controls[1] = 'current';
			$controls[2] = 'progress';
		}

		$params['data-controls'] = esc_attr( json_encode( $controls ) );

		return $params;

	}

	/**
	 * Return embedded video parameters
	 *
	 * @return array
	 */
	public function __get_embed_video_params() {

		$settings          = $this->get_settings();
		$params            = array();
		$params_dictionary = array();

		switch ( jet_woo_gallery_video_integration()->get_video_type( $settings ) ) {
			case 'youtube':
				$params_dictionary = array(
					'autoplay' => 'autoplay',
					'loop'     => 'loop',
				);


				if ( $settings['loop'] ) {
					$video_properties = Embed::get_video_properties( esc_url( $this->__get_video_url() ) );

					$params['playlist'] = $video_properties['video_id'];
				}

				break;
			case 'vimeo':
				$params_dictionary = array(
					'autoplay' => 'autoplay',
					'loop'     => 'loop',
				);

				if ( 'content' === $settings['video_display_in'] ) {
					$params_dictionary['autoplay'] = 'autoplay';
				} else {
					$params_dictionary['autoplay'] = false;
				}

				$params['autopause'] = '0';

				break;
		}

		foreach ( $params_dictionary as $setting_name => $param_name ) {

			$param_value = filter_var( $settings[ $setting_name ], FILTER_VALIDATE_BOOLEAN ) ? '1' : '0';

			$params[ $param_name ] = $param_value;

			if ( 'popup' === $settings['video_display_in'] ) {
				$params['autoplay'] = '0';
			}

		}

		return $params;

	}

	/**
	 * Return video url
	 *
	 * @return string
	 */
	public function __get_video_url() {

		$video_url = '';
		$settings  = $this->get_settings_for_display();

		switch ( jet_woo_gallery_video_integration()->get_video_type( $settings ) ) {
			case 'self_hosted':
				$video_id  = jet_woo_gallery_video_integration()->get_self_hosted_video_id( $settings );
				$video_url = wp_get_attachment_url( $video_id );
				break;
			case 'youtube':
				$video_url = jet_woo_gallery_video_integration()->get_youtube_video_url( $settings );
				break;
			case 'vimeo':
				$video_url = jet_woo_gallery_video_integration()->get_vimeo_video_url( $settings );
				break;
		}

		if ( ! $video_url ) {
			return '';
		}

		return esc_url( $video_url );

	}

	/**
	 * Add icon control
	 *
	 * @param string $id
	 * @param array  $args
	 * @param object $instance
	 */
	public function __add_advanced_icon_control( $id, array $args = array(), $instance = null ) {

		if ( defined( 'ELEMENTOR_VERSION' ) && version_compare( ELEMENTOR_VERSION, '2.6.0', '>=' ) ) {

			$_id = $id; // old control id
			$id  = $this->__new_icon_prefix . $id;

			$args['type']             = Controls_Manager::ICONS;
			$args['fa4compatibility'] = $_id;

			unset( $args['file'] );
			unset( $args['default'] );

			if ( isset( $args['fa5_default'] ) ) {
				$args['default'] = $args['fa5_default'];

				unset( $args['fa5_default'] );
			}
		} else {
			$args['type'] = Controls_Manager::ICON;
			unset( $args['fa5_default'] );
		}

		if ( null !== $instance ) {
			$instance->add_control( $id, $args );
		} else {
			$this->add_control( $id, $args );
		}

	}

	/**
	 * Prepare icon control ID for condition.
	 *
	 * @param string $id Old icon control ID.
	 *
	 * @return string
	 */
	public function __prepare_icon_id_for_condition( $id ) {

		if ( defined( 'ELEMENTOR_VERSION' ) && version_compare( ELEMENTOR_VERSION, '2.6.0', '>=' ) ) {
			return $this->__new_icon_prefix . $id . '[value]';
		}

		return $id;

	}

	/**
	 * Print HTML icon template
	 *
	 * @param array  $setting
	 * @param string $format
	 * @param string $icon_class
	 * @param bool   $echo
	 *
	 * @return void|string
	 */
	public function __render_icon( $setting = null, $format = '%s', $icon_class = '', $echo = true ) {

		if ( false === $this->__processed_item ) {
			$settings = $this->get_settings_for_display();
		} else {
			$settings = $this->__processed_item;
		}

		$new_setting = $this->__new_icon_prefix . $setting;
		$migrated    = isset( $settings['__fa4_migrated'][ $new_setting ] );
		$is_new      = empty( $settings[ $setting ] ) && class_exists( 'Elementor\Icons_Manager' ) && Icons_Manager::is_migration_allowed();
		$icon_html   = '';

		if ( $is_new || $migrated ) {
			$attr = array( 'aria-hidden' => 'true' );

			if ( ! empty( $icon_class ) ) {
				$attr['class'] = $icon_class;
			}

			if ( isset( $settings[ $new_setting ] ) ) {
				ob_start();
				Icons_Manager::render_icon( $settings[ $new_setting ], $attr );

				$icon_html = ob_get_clean();
			}
		} else if ( ! empty( $settings[ $setting ] ) ) {
			if ( empty( $icon_class ) ) {
				$icon_class = $settings[ $setting ];
			} else {
				$icon_class .= ' ' . $settings[ $setting ];
			}

			$icon_html = sprintf( '<i class="%s" aria-hidden="true"></i>', $icon_class );
		}

		if ( empty( $icon_html ) ) {
			return;
		}

		if ( ! $echo ) {
			return sprintf( $format, $icon_html );
		}

		printf( $format, $icon_html );

	}

}
