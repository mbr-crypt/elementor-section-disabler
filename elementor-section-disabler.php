<?php
/**
 * Plugin Name: INTERCAT – Elementor Section Disabler
 * Description: Adds a "Hide on live site" switch to containers, sections, columns and widgets in Elementor under the "Advanced" tab. The element stays visible in the editor (faded grey with diagonal stripes) but is not rendered at all on the live frontend, so it is missing from the HTML source as well.
 * Version:     1.1.0
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

		/** Elementor element types that get the switch and the should_render filter. */
		const ELEMENT_TYPES = array( 'widget', 'section', 'column', 'container' );

		public function __construct() {
			// Inject the control into each element type (always under the "Advanced" tab).
			add_action( 'elementor/element/container/section_layout/after_section_end', array( $this, 'register_controls' ), 10, 2 );
			add_action( 'elementor/element/section/section_advanced/after_section_end', array( $this, 'register_controls' ), 10, 2 );
			add_action( 'elementor/element/column/section_advanced/after_section_end', array( $this, 'register_controls' ), 10, 2 );
			add_action( 'elementor/element/common/_section_style/after_section_end', array( $this, 'register_controls' ), 10, 2 );

			// Primary mechanism: do not render flagged elements at all (no markup in the page source).
			foreach ( self::ELEMENT_TYPES as $type ) {
				add_filter( "elementor/frontend/{$type}/should_render", array( $this, 'filter_should_render' ), 10, 2 );
			}

			// Safety net: if an element type ever skips the filter above, it is at least hidden visually.
			add_action( 'elementor/frontend/before_render', array( $this, 'maybe_hide_element' ) );
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
					'description'  => esc_html__( 'The element stays visible in the editor but is not rendered on the live site at all (it is not part of the page source).', 'intercat' ),
				)
			);

			$element->end_controls_section();
		}

		/**
		 * Is the given element flagged as disabled on the live site?
		 *
		 * @param \Elementor\Element_Base $element
		 * @return bool
		 */
		private function is_flagged( $element ) {
			return 'yes' === $element->get_settings( self::CONTROL_ID );
		}

		/**
		 * Are we inside the Elementor editor (where disabled elements must stay visible)?
		 *
		 * @return bool
		 */
		private function in_editor() {
			return \Elementor\Plugin::$instance->editor->is_edit_mode();
		}

		/**
		 * Skip rendering of flagged elements on the frontend (never inside the editor).
		 * Because nothing is rendered, the content is neither in the HTML nor in the DOM.
		 *
		 * @param bool                    $should_render
		 * @param \Elementor\Element_Base $element
		 * @return bool
		 */
		public function filter_should_render( $should_render, $element ) {
			if ( ! $should_render ) {
				return false;
			}

			if ( ! $this->is_flagged( $element ) ) {
				return true;
			}

			// Keep the element visible and editable inside the Elementor editor.
			if ( $this->in_editor() ) {
				return true;
			}

			return false;
		}

		/**
		 * Fallback: mark the wrapper so CSS can hide it, in case the element was rendered anyway.
		 *
		 * @param \Elementor\Element_Base $element
		 */
		public function maybe_hide_element( $element ) {
			if ( ! $this->is_flagged( $element ) ) {
				return;
			}

			if ( $this->in_editor() ) {
				return;
			}

			$element->add_render_attribute( '_wrapper', 'class', 'esd-hidden' );
		}

		/**
		 * Frontend rule (fallback only): hide flagged elements.
		 */
		public function frontend_styles() {
			wp_register_style( 'esd-styles', false );
			wp_enqueue_style( 'esd-styles' );
			wp_add_inline_style( 'esd-styles', '.esd-hidden{display:none !important;}' );
		}

		/**
		 * Editor canvas: fade disabled elements to grey and lay diagonal stripes over them.
		 *
		 * The fade is done with backdrop-filter on an overlay, so Elementor's own editing handles
		 * (which sit above the overlay) keep their normal colours. Browsers without backdrop-filter
		 * support fall back to a plain grayscale + opacity on the element.
		 */
		public function editor_styles() {
			$css = '
				.elementor-element.esd-disabled--yes {
					position: relative;
					outline: 2px dashed #8a8f98 !important;
					outline-offset: -2px;
				}
				.elementor-element.esd-disabled--yes::after {
					content: "";
					position: absolute;
					top: 0;
					right: 0;
					bottom: 0;
					left: 0;
					z-index: 1000;
					pointer-events: none;
					background: repeating-linear-gradient(
						135deg,
						rgba(70, 74, 82, .30) 0,
						rgba(70, 74, 82, .30) 5px,
						rgba(255, 255, 255, .28) 5px,
						rgba(255, 255, 255, .28) 12px
					);
					-webkit-backdrop-filter: grayscale(1) contrast(.55) brightness(1.2);
					backdrop-filter: grayscale(1) contrast(.55) brightness(1.2);
				}
				.elementor-element.esd-disabled--yes::before {
					content: "Hidden on live site";
					display: block;
					position: absolute;
					top: 0;
					left: 0;
					right: auto;
					bottom: auto;
					width: auto;
					height: auto;
					margin: 0;
					transform: none;
					opacity: 1;
					z-index: 1001;
					background: #3c434a;
					color: #fff;
					font: 600 11px/1 -apple-system, "Segoe UI", sans-serif;
					letter-spacing: .02em;
					padding: 4px 8px;
					pointer-events: none;
					border-bottom-right-radius: 4px;
				}
				/* Nested flagged elements inside an already disabled parent: no double decoration. */
				.elementor-element.esd-disabled--yes .elementor-element.esd-disabled--yes {
					outline: none !important;
				}
				.elementor-element.esd-disabled--yes .elementor-element.esd-disabled--yes::before,
				.elementor-element.esd-disabled--yes .elementor-element.esd-disabled--yes::after {
					display: none;
				}
				@supports not ((backdrop-filter: blur(1px)) or (-webkit-backdrop-filter: blur(1px))) {
					.elementor-element.esd-disabled--yes {
						filter: grayscale(1);
						opacity: .5;
					}
				}
			';

			wp_register_style( 'esd-editor-styles', false );
			wp_enqueue_style( 'esd-editor-styles' );
			wp_add_inline_style( 'esd-editor-styles', $css );
		}
	}

	new Elementor_Section_Disabler();
}
