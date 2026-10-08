<?php
/**
 * Base for the public form widgets (request form, sign-up). They add the
 * forms stylesheet and the script that sends them in the background; the
 * forms post normally without it.
 *
 * @package LenzPlus
 */

namespace LenzPlus\Modules\Builder\Elementor\Widgets;

use Elementor\Controls_Manager;
use LenzPlus\Modules\Builder\Assets;

defined( 'ABSPATH' ) || exit;

/**
 * Assets and shared markup of the form widgets.
 */
abstract class Form_Base extends Section_Base {

	/** Section and form stylesheets. */
	public function get_style_depends(): array {
		return array( Assets::HANDLE, Assets::SECTIONS_HANDLE, Assets::FORMS_HANDLE );
	}

	/** The background-sending script. */
	public function get_script_depends(): array {
		return array( Assets::FORMS_HANDLE );
	}

	/** The result of a plain post depends on the request: never cached by Elementor. */
	protected function is_dynamic_content(): bool {
		return true;
	}

	/** Panel search terms shared by the form widgets. */
	public function get_keywords(): array {
		return array_merge( parent::get_keywords(), array( 'form', 'فرم' ) );
	}

	/**
	 * "Message after sending" control.
	 *
	 * @param string $default_value Default message.
	 */
	protected function add_success_control( string $default_value ): void {
		$this->add_control(
			'success_text',
			array(
				'label'   => __( 'Message after sending', 'lenz-plus' ),
				'type'    => Controls_Manager::TEXTAREA,
				'rows'    => 2,
				'default' => $default_value,
			)
		);
	}
}
