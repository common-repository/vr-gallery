<?php

/*
 * Plugin Name: VR Gallery
 * Description: Create more immersive WordPress image galleries by turning any images into virtual reality shots.
 * Version: 1.0.4
 * Author: MotoPress
 * Author URI: https://motopress.com/
 * License: GPLv2 or later
 * Text Domain: vr-gallery
 * Domain Path: /languages
 */

class VR_Gallery {
	/**
	 * $shortcode_tag
	 * holds the name of the shortcode tag
	 * @var string
	 */
	public $shortcode_tag = 'vrg_panel';
	public $preffix = 'vrg_';
	
	/**
	 * __construct
	 * class constructor will set the needed filter and action hooks
	 *
	 * @param array $args
	 */
	function __construct( $args = array() ) {
		//add shortcode
		add_shortcode( $this->shortcode_tag, array( $this, 'shortcode_handler' ) );

		if ( is_admin() ) {
			add_action( 'admin_head', array( $this, 'admin_head' ) );
			add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );
		}
		add_action( 'wp_enqueue_scripts', array( $this, 'frontend_enqueue_scripts' ) );
		
	}

	/**
	 * shortcode_handler
	 *
	 * @param  array $atts shortcode attributes
	 * @param  string $content shortcode content
	 *
	 * @return string
	 */
	// Add Shortcode
	function shortcode_handler( $atts, $content = null ) {
		// Attributes
		
		extract( shortcode_atts(
				array(
					'image_ids'			=> '',
					'scene_width'		=> '100%',
					'scene_height'		=> '400px',
					'sky_color'			=> '#111122',
					'ground_texture'	=> ''
				), $atts )
		);
		
		
			return $this->do_mobile_vr_scene($image_ids, $ground_texture, $scene_width, $scene_height, $sky_color);
	}
	
	function do_mobile_vr_scene($image_ids, $ground_texture, $scene_width, $scene_height, $sky_color) {

		$classes = 'vrg-main-scene';

		$ids_arr = explode(',', $image_ids);
		$dg_per_img = (-1) * 360 / count($ids_arr);
		if (!empty($ground_texture)) {
			$img_info = wp_get_attachment_image_src($ground_texture, 'full');
			$ground = $img_info[0];
		} else {
			$ground = plugins_url( 'js/img/texture.jpg', __FILE__ );
		}

		$out = '<a-scene class="' . $classes . '" embedded style="width: ' . $scene_width . '; height: ' . $scene_height . '" height="'. $scene_height .'">
				<a-assets>';

		$out .= '<audio id="click-sound" crossorigin="anonymous" src="' . plugins_url( 'audio/click.ogg', __FILE__ ) . '"></audio>'
				. '<img id="groundTexture" src="' . $ground . '">';

		$templates = '';
		foreach ($ids_arr as $k => $id) {
			$img_src_thumb = wp_get_attachment_image_src($id, 'medium');
			$img_src = wp_get_attachment_image_src($id, 'full');
			
			$out .= '<img class="attachment-thumbnail size-thumbnail" '
					. 'id="thumb-img_' . $id . '" crossorigin="anonymous" '
					. 'src="' . $img_src_thumb[0] . '" position>';
			
			$out .= '<img id="img_' . $id . '" crossorigin="anonymous" '
					. 'src="' . $img_src[0] . '" position>';
			
			$rotate = $dg_per_img * $k + (-90);
			$rotate_big = $dg_per_img * $k + (-90);

			$templates .= '<a-entity template="src: #link" '
					. 'data-thumb="#thumb-img_' . $id . '" '
					. 'data-src="#img_' . $id . '" '
					. 'data-w="' . $img_src[1] . '" '
					. 'data-h="' . $img_src[2] . '" '
					. 'data-r="' . $rotate_big . '"  '
					. 'rotation="-4 ' . $rotate . ' 0" ></a-entity>';
		}

		$out .='<!-- Image link template to be reused. -->
					<script id="link" type="text/html">
						<a-entity class="link"
							geometry="primitive: plane; width: 4; height: 4" 
							material="shader: flat; src: ${thumb}"
							event-set__1="_event: mousedown; scale: 1 1 1"
							event-set__2="_event: mouseup; scale: 1.2 1.2 1"
							event-set__3="_event: mouseenter; scale: 1.2 1.2 1"
							event-set__4="_event: mouseleave; scale: 1 1 1"
							set-image="on: click; target: #image-360; src: ${src}; w: ${w}; h: ${h}; r: ${r}"
							sound="on: click; src: #click-sound">
						</a-entity>
					</script>';
		$out .= '</a-assets>';
		$first_img = wp_get_attachment_image_src($ids_arr[0], 'full');
		$w = $img_src[1] / 50;
		$h = $img_src[2] / 50;

		$out .= '<a-entity position="0 8 0" rotation="0 90 0" layout="type: circle; margin: 1.5; radius: 20;">'
				. '<a-entity id="image-360" '
				. 'src="#img_' . $ids_arr[0] . '" '
				. 'material="src: ' . $first_img[0] . '" '
				. 'position="0 0 0" '
				. 'geometry="primitive: plane; width: ' . $w . '; height: ' . $h . ';" '
				. 'rotation="20 -90 0" '
				. '></a-entity></a-entity>';

		$out .='<a-sky color="' . $sky_color . '"></a-sky>'
				. '<a-plane src="#groundTexture" '
				. 'geometry="primitive: circle; radius: 22;" '
				. 'rotation="-90 0 0" height="40" width="40"></a-plane>';

		$out .='<!-- Image links. -->
				<a-entity id="links" layout="type: circle; margin: 1.5; radius: 18;" position="0 1.5 0" rotation="0 90 0" >';
		
		$out .= $templates;

		$out .= '</a-entity>';
		
		$out .= '<!-- Camera + cursor. -->
				<a-entity id="camera" camera look-controls position="0 8 0" rotation="0 0 0">
					<a-cursor id="cursor"
						animation__click="property: scale; startEvents: click; from: 0.1 0.1 0.1; to: 1 1 1; dur: 150"
						animation__fusing="property: fusing; startEvents: fusing; from: 1 1 1; to: 0.1 0.1 0.1; dur: 1500"
						event-set__1="_event: mouseenter; color: springgreen"
						event-set__2="_event: mouseleave; color: black"
						fuse="true"
						raycaster="objects: .link"></a-cursor>
					</a-entity>
				</a-scene>';

		return $out;
	}
	
	/**
	 * admin_head
	 * calls your functions into the correct filters
	 * @return void
	 */
	function admin_head() {
		// check user permissions
		if ( ! current_user_can( 'edit_posts' ) && ! current_user_can( 'edit_pages' ) ) {
			return;
		}

		// check if WYSIWYG is enabled
		if ( 'true' == get_user_option( 'rich_editing' ) ) {
			add_filter( 'mce_external_plugins', array( $this, 'mce_external_plugins' ) );
			add_filter( 'mce_buttons', array( $this, 'mce_buttons' ) );
		}
	}

	/**
	 * mce_external_plugins
	 * Adds our tinymce plugin
	 *
	 * @param  array $plugin_array
	 *
	 * @return array
	 */
	function mce_external_plugins( $plugin_array ) {
		$plugin_array[ $this->shortcode_tag ] = plugins_url( 'js/mce-button.js', __FILE__ );

		return $plugin_array;
	}

	/**
	 * mce_buttons
	 * Adds our tinymce button
	 *
	 * @param  array $buttons
	 *
	 * @return array
	 */
	function mce_buttons( $buttons ) {
		array_push( $buttons, $this->shortcode_tag );

		return $buttons;
	}

	/**
	 * admin_enqueue_scripts
	 * Used to enqueue custom styles
	 * @return void
	 */
	function admin_enqueue_scripts() {
		wp_enqueue_style( 'vrg_panel_shortcode', plugins_url( 'stylesheets/mce-button.css', __FILE__ ) );
		wp_enqueue_media();
		
	}

	function frontend_enqueue_scripts() {
		wp_enqueue_script('aframe', plugins_url( 'js/aframe-master.js', __FILE__ ), array('jquery') );
		
		wp_enqueue_script('animation', plugins_url( 'js/components/animation.js', __FILE__ ), array('jquery', 'aframe') );
		wp_enqueue_script('event-set', plugins_url( 'js/vrg_event_set_component.js', __FILE__ ), array('jquery', 'aframe') );
		wp_enqueue_script('layout', plugins_url( 'js/components/layout.js', __FILE__ ), array('jquery', 'aframe') );
		wp_enqueue_script('template', plugins_url( 'js/components/template.js', __FILE__ ), array('jquery', 'aframe') );
		wp_enqueue_script('image-set', plugins_url( 'js/vrg_image_set_component.js', __FILE__ ), array('jquery', 'aframe') );
		
		wp_enqueue_style( 'vrg-style', plugins_url( 'stylesheets/vrg.css', __FILE__ ) );
		
		
		wp_enqueue_script( 'vrg-init', plugins_url( 'js/gallery-init.js', __FILE__ ), array('jquery'));
		wp_localize_script( 'vrg-init', 'vrg', array(
			'ajaxurl'             => esc_url( admin_url( 'admin-ajax.php' ) ),
		) );
	}
}

new VR_Gallery();
