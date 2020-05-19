<?php

add_action( 'plugins_loaded', 'WPAPIYoast_init' );

/**
 * Plugin Name: Yoast to REST API
 * Description: Adds Yoast fields to page and post metadata to WP REST API responses
 * Author: Niels Garve, Pablo Postigo, Tedy Warsitha, Charlie Francis
 * Author URI: https://github.com/niels-garve
 * Version: 1.4.1
 * Plugin URI: https://github.com/niels-garve/yoast-to-rest-api
 */
class Yoast_To_REST_API {

	protected $keys = array(
		'yoast_wpseo_focuskw',
		'yoast_wpseo_title',
		'yoast_wpseo_metadesc',
		'yoast_wpseo_linkdex',
		'yoast_wpseo_metakeywords',
		'yoast_wpseo_meta-robots-noindex',
		'yoast_wpseo_meta-robots-nofollow',
		'yoast_wpseo_meta-robots-adv',
		'yoast_wpseo_canonical',
		'yoast_wpseo_redirect',
		'yoast_wpseo_opengraph-title',
		'yoast_wpseo_opengraph-description',
		'yoast_wpseo_opengraph-image',
		'yoast_wpseo_twitter-title',
		'yoast_wpseo_twitter-description',
		'yoast_wpseo_twitter-image'
	);

	function __construct() {
		add_action( 'rest_api_init', array( $this, 'add_yoast_data' ) );
	}

	function add_yoast_data() {
		// Posts
		register_rest_field( 'post',
			'yoast_meta',
			array(
				'get_callback'    => array( $this, 'wp_api_encode_yoast' ),
				'update_callback' => null,
				'schema'          => null,
			)
		);

		// Pages
		register_rest_field( 'page',
			'yoast_meta',
			array(
				'get_callback'    => array( $this, 'wp_api_encode_yoast' ),
				'update_callback' => null,
				'schema'          => null,
			)
		);

		// Public custom post types
		$types = get_post_types( array(
			'public'   => true,
			'_builtin' => false
		) );

		foreach ( $types as $key => $type ) {
			register_rest_field( $type,
				'yoast_meta',
				array(
					'get_callback'    => array( $this, 'wp_api_encode_yoast' ),
					'update_callback' => null,
					'schema'          => null,
				)
			);
		}
	}

	function wp_api_encode_yoast( $post, $field_name, $request ) {
        $yoast_meta = [];
	    foreach($this->keys as $key) {
            $yoast_meta[$key] = get_post_meta($post['id'], '_' . $key, true);
        }
		return $yoast_meta;
	}

	private function wp_api_encode_taxonomy($termId) {
		$yoast_meta = array(
			'yoast_wpseo_title'    => get_the_title($termId),
			'yoast_wpseo_metadesc' => term_description($termId),
		);

		return (array) $yoast_meta;
	}

	function wp_api_encode_yoast_category( $category ) {
        return $this->wp_api_encode_taxonomy($category['id']);
	}

	function wp_api_encode_yoast_tag( $tag ) {
        return $this->wp_api_encode_taxonomy($tag['id']);
	}
}

function WPAPIYoast_init() {
	if ( class_exists( 'WPSEO_Frontend' ) ) {
		$yoast_To_REST_API = new Yoast_To_REST_API();
	} else {
		add_action( 'admin_notices', 'wpseo_not_loaded' );
	}
}

function wpseo_not_loaded() {
	printf(
		'<div class="error"><p>%s</p></div>',
		__( '<b>Yoast to REST API</b> plugin not working because <b>Yoast SEO</b> plugin is not active.' )
	);
}
