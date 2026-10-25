<?php
/**
 * Base for the public form widgets (request form, sign-up, project
 * booking). They add the forms stylesheet and the script that sends them in
 * the background (and steps through the booking form); the forms post
 * normally without it.
 *
 * @package LenzPlus
 */

namespace LenzPlus\Modules\Builder\Elementor\Widgets;

use Elementor\Controls_Manager;
use Elementor\Repeater;
use LenzPlus\Modules\Builder\Assets;
use LenzPlus\Modules\Builder\Form_Fields;

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

	/**
	 * Controls of a configurable field row (type, label, choices, required,
	 * full width), for a REPEATER control's `fields`.
	 *
	 * @param string[] $types      Types the form offers.
	 * @param string   $label_hint Description under the label.
	 */
	protected static function field_repeater( array $types, string $label_hint ): array {
		$labels = array(
			'name'     => __( 'Name', 'lenz-plus' ),
			'phone'    => __( 'Mobile / phone', 'lenz-plus' ),
			'email'    => __( 'Email', 'lenz-plus' ),
			'text'     => __( 'Short text', 'lenz-plus' ),
			'number'   => __( 'Number', 'lenz-plus' ),
			'select'   => __( 'Choice', 'lenz-plus' ),
			'choice'   => __( 'Choice buttons', 'lenz-plus' ),
			'date'     => __( 'Date or time', 'lenz-plus' ),
			'textarea' => __( 'Long text', 'lenz-plus' ),
		);

		$repeater = new Repeater();
		$repeater->add_control(
			'type',
			array(
				'label'   => __( 'Type', 'lenz-plus' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'text',
				'options' => array_intersect_key( $labels, array_flip( $types ) ),
			)
		);
		$repeater->add_control(
			'label',
			array(
				'label'       => __( 'Label', 'lenz-plus' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => __( 'Field', 'lenz-plus' ),
				'label_block' => true,
				'description' => $label_hint,
			)
		);
		$repeater->add_control(
			'options',
			array(
				'label'     => __( 'Choices (one per line)', 'lenz-plus' ),
				'type'      => Controls_Manager::TEXTAREA,
				'rows'      => 5,
				'default'   => '',
				'condition' => array( 'type' => array( 'select', 'choice' ) ),
			)
		);
		$repeater->add_control(
			'required',
			array(
				'label'        => __( 'Required', 'lenz-plus' ),
				'type'         => Controls_Manager::SWITCHER,
				'default'      => '',
				'return_value' => 'yes',
			)
		);
		$repeater->add_control(
			'wide',
			array(
				'label'        => __( 'Full width', 'lenz-plus' ),
				'type'         => Controls_Manager::SWITCHER,
				'default'      => '',
				'return_value' => 'yes',
			)
		);

		return $repeater->get_controls();
	}

	/**
	 * "Email a copy" and "Send to" controls.
	 *
	 * @param array  $defaults Handler defaults (`notify`, `email_to`).
	 * @param string $kept     Where the posts are kept even when email fails.
	 */
	protected function add_sending_controls( array $defaults, string $kept ): void {
		$this->add_control(
			'notify',
			array(
				'label'        => __( 'Email a copy of each request', 'lenz-plus' ),
				'type'         => Controls_Manager::SWITCHER,
				'default'      => $defaults['notify'],
				'return_value' => 'yes',
				'description'  => $kept,
			)
		);

		$this->add_control(
			'email_to',
			array(
				'label'       => __( 'Send to', 'lenz-plus' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => $defaults['email_to'],
				'placeholder' => get_option( 'admin_email' ),
				'description' => __( 'One or more addresses, separated by commas. Empty: the site\'s admin email.', 'lenz-plus' ),
				'condition'   => array( 'notify' => 'yes' ),
			)
		);
	}

	/**
	 * One configurable field. Fields are posted by position (`f[0]`…), which
	 * is how the handlers read them. Labels are for screen readers only
	 * (the mockups show placeholders), or visible above the field.
	 *
	 * @param string   $form_id Form HTML id.
	 * @param int      $index   Position.
	 * @param array    $field   Field settings (`type`, `label`, `required`, `wide`, `options`).
	 * @param string[] $types   Types the form offers (Form_Fields::TYPES or fewer).
	 * @param bool     $visible Whether the label shows above the field.
	 */
	protected static function field_html( string $form_id, int $index, array $field, array $types, bool $visible = false ): string {
		$type     = Form_Fields::type_of( $field, $types );
		$label    = (string) ( $field['label'] ?? '' );
		$required = 'yes' === ( $field['required'] ?? '' );
		$field_id = $form_id . '-' . $index;
		$name     = 'f[' . $index . ']';
		$wide     = 'yes' === ( $field['wide'] ?? '' ) || in_array( $type, array( 'textarea', 'choice' ), true );
		$classes  = 'lzp-form__field' . ( $wide ? ' lzp-form__field--wide' : '' );
		$options  = Form_Fields::option_list( (string) ( $field['options'] ?? '' ) );

		// A few choices as buttons: native radios, so they work and are announced without JavaScript.
		if ( 'choice' === $type ) {
			$pills = '';
			foreach ( $options as $option ) {
				$pills .= sprintf(
					'<label class="lzp-form__pill"><input class="lzp-form__choice" type="radio" name="%1$s" value="%2$s" data-label="%3$s"%4$s><span>%5$s</span></label>',
					esc_attr( $name ),
					esc_attr( $option ),
					esc_attr( $label ),
					$required ? ' required' : '',
					esc_html( $option )
				);
			}

			return sprintf(
				'<fieldset class="%1$s lzp-form__choices"><legend class="lzp-form__label">%2$s%3$s</legend><span class="lzp-form__pills">%4$s</span></fieldset>',
				esc_attr( $classes ),
				esc_html( $label ),
				$required ? '' : ' <span class="lzp-form__optional">' . esc_html__( '(optional)', 'lenz-plus' ) . '</span>',
				$pills
			);
		}

		$attrs = sprintf(
			' class="lzp-form__input" id="%1$s" name="%2$s" data-label="%3$s"%4$s',
			esc_attr( $field_id ),
			esc_attr( $name ),
			esc_attr( $label ),
			$required ? ' required' : ''
		);
		$hint  = $visible ? '' : ' placeholder="' . esc_attr( $label ) . '"';

		switch ( $type ) {
			case 'select':
				$choices = '<option value="">' . esc_html( $visible ? __( 'Choose…', 'lenz-plus' ) : $label ) . '</option>';
				foreach ( $options as $option ) {
					$choices .= sprintf( '<option value="%1$s">%1$s</option>', esc_attr( $option ) );
				}
				$control = '<select' . $attrs . '>' . $choices . '</select>';
				break;

			case 'textarea':
				$control = '<textarea' . $attrs . ' rows="4" maxlength="5000"' . $hint . '></textarea>';
				break;

			default:
				$extra = array(
					'name'   => ' type="text" maxlength="100" autocomplete="name"',
					'phone'  => ' type="tel" inputmode="tel" maxlength="20" autocomplete="tel"',
					'email'  => ' type="email" inputmode="email" autocomplete="email"',
					'number' => ' type="text" inputmode="numeric" maxlength="20"',
				);
				// Numbers and addresses read left to right; with a placeholder inside the field it stays right-aligned instead.
				$ltr     = $visible && in_array( $type, array( 'phone', 'email' ), true ) ? ' dir="ltr"' : '';
				$control = '<input' . $attrs . ( $extra[ $type ] ?? ' type="text" maxlength="200"' ) . $ltr . $hint . '>';
		}

		$label_html = $visible
			? sprintf( '<label class="lzp-form__label" for="%1$s">%2$s%3$s</label>', esc_attr( $field_id ), esc_html( $label ), $required ? '' : ' <span class="lzp-form__optional">' . esc_html__( '(optional)', 'lenz-plus' ) . '</span>' )
			: sprintf( '<label class="screen-reader-text" for="%1$s">%2$s</label>', esc_attr( $field_id ), esc_html( $label ) );

		return '<p class="' . esc_attr( $classes ) . '">' . $label_html . $control . '</p>';
	}
}
