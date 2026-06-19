<?php
/**
 * Plugin Name: INTERCAT – Elementor Section Disabler
 * Description: Adds a "Hide on live site" switch to containers, sections, columns and widgets in Elementor under the "Advanced" tab. The element stays visible in the editor but is hidden with display:none on the live frontend.
 * Version:     1.0.0
 * Author:      MBR-CRYPT from INTERCAT.it
 * Author URI:  https://github.com/mbr-crypt/elementor-section-disabler
 * Text Domain: github
 */
 
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Prevent direct access.
}
 
if ( ! class_exists( 'Elementor_Section_Disabler' ) ) {
 
	final class Elementor_Section_Disabler {
 
		/** Key of the switcher control. */
		const CONTROL_ID = 'esd_disable';
 
		public function __construct() {
			// Inject the control into each element type (always under the "Advanced" tab).
			add_action( 'elementor/element/container/section_layout/after_section_end', array( $this, 'register_controls' ), 10, 2 );
			add_action( 'elementor/element/section/section_advanced/after_section_end', array( $this, 'register_controls' ), 10, 2 );
			add_action( 'elementor/element/column/section_advanced/after_section_end', array( $this, 'register_controls' ), 10, 2 );
			add_action( 'elementor/element/common/_section_style/after_section_end', array( $this, 'register_controls' ), 10, 2 );
 
			// Decide on render whether to hide the element.
			add_action( 'elementor/frontend/before_render', array( $this, 'maybe_hide_element' ) );
 
			// CSS: hide flagged elements on the real frontend.
			add_action( 'wp_enqueue_scripts', array( $this, 'frontend_styles' ) );
 
			// CSS: visually mark disabled elements inside the editor canvas.
			add_action( 'elementor/preview/enqueue_styles', array( $this, 'editor_styles' ) );
		}
 
		/**
		 * Register the switcher control.
		 *
		 * @param \Elementor\Controls_Stack $element
		 * @param array                     $args
		 */
		public function register_controls( $element, $args ) {
			$element->start_controls_section(
				'esd_disable_section',
				array(
					'label' => esc_html__( 'Disable Section', 'intercat' ),
					'tab'   => \Elementor\Controls_Manager::TAB_ADVANCED,
				)
			);
 
			$element->add_control(
				self::CONTROL_ID,
				array(
					'label'        => esc_html__( 'Hide on live site', 'intercat' ),
					'type'         => \Elementor\Controls_Manager::SWITCHER,
					'label_on'     => esc_html__( 'Yes', 'intercat' ),
					'label_off'    => esc_html__( 'No', 'intercat' ),
					'return_value' => 'yes',
					'default'      => '',
					'prefix_class' => 'esd-disabled--',
					'description'  => esc_html__( 'The element stays visible in the editor but is hidden with display:none on the live site.', 'intercat' ),
				)
			);
 
			$element->end_controls_section();
		}
 
		/**
		 * Hide the element on the frontend (never inside the editor).
		 *
		 * @param \Elementor\Element_Base $element
		 */
		public function maybe_hide_element( $element ) {
			if ( 'yes' !== $element->get_settings_for_display( self::CONTROL_ID ) ) {
				return;
			}
 
			// Keep the element visible inside the Elementor editor.
			if ( \Elementor\Plugin::$instance->editor->is_edit_mode() ) {
				return;
			}
 
			// Hide only on the real frontend.
			$element->add_render_attribute( '_wrapper', 'class', 'esd-hidden' );
		}
 
		/**
		 * Frontend rule: hide flagged elements.
		 */
		public function frontend_styles() {
			wp_register_style( 'esd-styles', false );
			wp_enqueue_style( 'esd-styles' );
			wp_add_inline_style( 'esd-styles', '.esd-hidden{display:none !important;}' );
		}
 
		/**
		 * Editor canvas: highlight disabled elements so they stay recognizable.
		 */
		public function editor_styles() {
			$css = '
				.esd-disabled--yes {
					outline: 2px dashed #d63638 !important;
					outline-offset: -2px;
					opacity: .55;
					position: relative;
				}
				.esd-disabled--yes::before {
					content: "Hidden on live site";
					position: absolute;
					top: 0;
					left: 0;
					z-index: 9999;
					background: #d63638;
					color: #fff;
					font-size: 11px;
					line-height: 1;
					padding: 4px 6px;
					font-family: -apple-system, "Segoe UI", sans-serif;
					pointer-events: none;
					border-bottom-right-radius: 4px;
				}
			';
 
			wp_register_style( 'esd-editor-styles', false );
			wp_enqueue_style( 'esd-editor-styles' );
			wp_add_inline_style( 'esd-editor-styles', $css );
		}
	}
 
	new Elementor_Section_Disabler();
}