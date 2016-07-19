<?php
/*
  Plugin Name: Gravity Forms: Toolbar Edit Links
  Plugin URI: https://github.com/gndev/gravity-forms-toolbar-edit-links/
  Version: 1.0.0
  Author: Vladimir Anokhin
  Author URI: http://gndev.info/
  Description: Adds "Edit GForm" link to Admin Bar on pages with Gravity Forms shortcode
  Text Domain: gravity-forms-toolbar-edit-links
  Domain Path: /languages
  License: GPL v3
 */


if ( !class_exists( 'GF_Toolbar_Edit_Links' ) ) {

	/**
	 * Plugin Class
	 */
	final class GF_Toolbar_Edit_Links {

		/**
		 * Form IDs
		 *
		 * @since  1.0.0
		 * @var mixed
		 * @access public
		 */
		public static $form_IDs;


		/**
		 * Empty constructor
		 *
		 * @since  1.0.0
		 * @return  void
		 */
		public function __construct() {}


		/**
		 * Init plugin
		 *
		 * @since  1.0.0
		 * @return void
		 */
		public static function init() {

			add_filter( 'gform_shortcode_form', array( __CLASS__, 'gform_shortcode_form' ), 10, 3  );
			add_action( 'admin_bar_menu',       array( __CLASS__, 'admin_bar_menu' ),       79.797 );
			add_action( 'wp_head',              array( __CLASS__, 'print_styles' ),         10     );
		}


		/**
		 * Hook to [gravityform] shortcode to collect Form IDs
		 *
		 * @since  1.0.0
		 * @param string  $shortcode_string Shortcode string
		 * @param mixed   $attributes       Shortcode attributes
		 * @param string  $content          Shortcode content
		 * @return string                   Unmodified shortcode string
		 */
		public static function gform_shortcode_form( $shortcode_string, $attributes, $content ) {

			// Check capability
			if ( !GFAPI::current_user_can_any( 'gravityforms_edit_forms' ) ) {
				return $shortcode_string;
			}

			// Check that Form ID is set properly
			if ( !isset( $attributes['id'] ) || !is_numeric( $attributes['id'] ) ) {
				return $shortcode_string;
			}

			// Add Form ID to global variable
			self::$form_IDs[] = $attributes['id'];

			// Return unmodified string
			return $shortcode_string;
		}


		/**
		 * Add new Toolbar nodes
		 *
		 * @since  1.0.0
		 * @param mixed   $toolbar WP_Admin_bar instance
		 * @return void
		 */
		public static function admin_bar_menu( $toolbar ) {

			// Check there is at least one form on the page
			if ( empty( self::$form_IDs ) ) {

				return;
			}

			// Add single node
			if ( count( self::$form_IDs ) === 1 ) {

				$form_ID = self::$form_IDs[0];

				$args = array(
					'id'     => 'gravity-forms-toolbar-edit-links-item-' . $form_ID,
					'href'   => admin_url( 'admin.php?page=gf_edit_forms&id=' . $form_ID ),
					'title'  => __( 'Edit GForm', 'gravity-forms-toolbar-edit-links' ),
					'meta'   => array(
						'class' => 'gravity-forms-toolbar-edit-links-item-top'
					),
				);

				// Add Toolbar node
				$toolbar->add_node( $args );
			}

			// Add multiple nodes (with parent)
			elseif ( count( self::$form_IDs ) > 1 ) {

				// Parent node args
				$args = array(
					'title' => __( 'Edit GForms', 'gravity-forms-toolbar-edit-links' ),
					'id'    => 'gravity-forms-toolbar-edit-links-group',
					'meta'   => array(
						'class' => 'gravity-forms-toolbar-edit-links-item-top'
					),
				);

				// Add parent node
				$toolbar->add_node( $args );

				// Loop through Form IDs
				foreach ( self::$form_IDs as $form_ID ) {

					// Get form data
					$form = GFAPI::get_form( $form_ID );

					// Child node args
					$args = array(
						'id'     => 'gravity-forms-toolbar-edit-links-item-' . $form_ID,
						'parent' => 'gravity-forms-toolbar-edit-links-group',
						'href'   => admin_url( 'admin.php?page=gf_edit_forms&id=' . $form_ID ),
						'title'  => $form['title'],
						'meta'   => array(
							'class' => 'gravity-forms-toolbar-edit-links-item-sub'
						),
					);

					// Add child node
					$toolbar->add_node( $args );
				}
			}
		}


		/**
		 * Print some CSS to add an icon to the Toolbar node
		 *
		 * @since  1.0.0
		 * @return void
		 */
		public static function print_styles() {

			// Check this is front and Toolbar is showing
			if ( !is_admin() && is_admin_bar_showing() ) {
				echo "\n<style>.gravity-forms-toolbar-edit-links-item-top>.ab-item:before{content:'\\f464';top:2px}</style>\n";
			}
		}


	} // END GF_Toolbar_Edit_Links class

	// Init plugin
	add_action( 'plugins_loaded', array( 'GF_Toolbar_Edit_Links', 'init' ) );

} // END if class_exists check


// Strings for PO Edit
if ( false ) {
	__( 'Adds "Edit GForm" link to Admin Bar on pages with Gravity Forms shortcode', 'gravity-forms-toolbar-edit-links' );
}
