<?php
/**
 * Configurable form fields shared by the request form and the project
 * booking form: the field types, how each answer is cleaned and checked,
 * and the choices of select and choice fields.
 *
 * Fields are configured in the widget (Elementor repeater rows with `type`,
 * `label`, `required`, `options`) and posted by position (`f[0]`, `f[1]`…);
 * the handlers read the configuration from the saved widget, never from the
 * request.
 *
 * @package LenzPlus
 */

namespace LenzPlus\Modules\Builder;

use LenzPlus\Core\Persian;

defined( 'ABSPATH' ) || exit;

/**
 * Field types, answer cleaning and validation.
 */
final class Form_Fields {

	/**
	 * Every field type: short text, contact details, a number, a date, a
	 * dropdown, buttons to pick one of a few choices, and a long text.
	 */
	public const TYPES = array( 'name', 'phone', 'email', 'text', 'number', 'date', 'select', 'choice', 'textarea' );

	/** Longest value kept per field type. */
	private const MAX_LENGTH = array(
		'name'     => 100,
		'number'   => 20,
		'textarea' => 5000,
	);

	private const MAX_DEFAULT = 200;

	/**
	 * Choices of a select or choice field (one per line).
	 *
	 * @param string $options Options setting.
	 * @return string[]
	 */
	public static function option_list( string $options ): array {
		return array_values( array_filter( array_map( 'trim', preg_split( '/\R/u', $options ) ), 'strlen' ) );
	}

	/**
	 * A field's type, `text` when it is not one of the allowed types.
	 *
	 * @param array    $field   Field settings.
	 * @param string[] $allowed Types the form offers.
	 */
	public static function type_of( array $field, array $allowed ): string {
		return in_array( $field['type'] ?? '', $allowed, true ) ? (string) $field['type'] : 'text';
	}

	/**
	 * Each configured field with the visitor's sanitized answer.
	 *
	 * @param array    $posted  Raw posted values, by position.
	 * @param array    $fields  Field settings.
	 * @param string[] $allowed Types the form offers.
	 * @param int      $offset  Position of the first field (forms with several field groups number them on).
	 * @return array<int, array{type:string, label:string, required:bool, value:string}>
	 */
	public static function clean( array $posted, array $fields, array $allowed, int $offset = 0 ): array {
		$answers = array();

		foreach ( array_values( $fields ) as $index => $field ) {
			$position = $offset + $index;
			$type     = self::type_of( $field, $allowed );
			$raw      = isset( $posted[ $position ] ) && is_string( $posted[ $position ] ) ? $posted[ $position ] : '';

			$answers[ $position ] = array(
				'type'     => $type,
				'label'    => (string) ( $field['label'] ?? '' ),
				'required' => 'yes' === ( $field['required'] ?? '' ),
				'value'    => mb_substr( self::sanitize( $type, $raw ), 0, self::MAX_LENGTH[ $type ] ?? self::MAX_DEFAULT ),
			);
		}

		return $answers;
	}

	/**
	 * One answer, cleaned for its field type.
	 *
	 * @param string $type Field type.
	 * @param string $raw  Posted value.
	 */
	public static function sanitize( string $type, string $raw ): string {
		switch ( $type ) {
			case 'email':
				return sanitize_email( $raw );
			case 'phone':
				// Visitors type numbers with Persian digits; keep digits, + and the usual separators.
				return trim( (string) preg_replace( '/[^\d+()\s-]/', '', Persian::latin_digits( sanitize_text_field( $raw ) ) ) );
			case 'number':
				return (string) preg_replace( '/\D/', '', Persian::latin_digits( sanitize_text_field( $raw ) ) );
			case 'textarea':
				return trim( sanitize_textarea_field( $raw ) );
			default:
				// A chosen option is kept as sent, not matched against the list: a cached page can
				// show options edited since, and a valid choice would be rejected.
				return trim( sanitize_text_field( $raw ) );
		}
	}

	/**
	 * Positions of the answers that need fixing.
	 *
	 * @param array $answers Cleaned answers.
	 * @return int[]
	 */
	public static function invalid( array $answers ): array {
		$invalid = array();

		foreach ( $answers as $position => $answer ) {
			$value = $answer['value'];

			if ( '' === $value ) {
				if ( $answer['required'] ) {
					$invalid[] = $position;
				}
				continue;
			}

			if ( ( 'email' === $answer['type'] && ! is_email( $value ) )
				|| ( 'phone' === $answer['type'] && strlen( (string) preg_replace( '/\D/', '', $value ) ) < 7 ) ) {
				$invalid[] = $position;
			}
		}

		return $invalid;
	}

	/**
	 * The answered fields as [label, value] pairs, the way requests and
	 * bookings are stored, emailed and exported.
	 *
	 * @param array $answers Cleaned answers.
	 * @return array<int, array{0:string, 1:string}>
	 */
	public static function pairs( array $answers ): array {
		$pairs = array();
		foreach ( $answers as $answer ) {
			if ( '' !== $answer['value'] ) {
				$pairs[] = array( $answer['label'], $answer['value'] );
			}
		}

		return $pairs;
	}

	/**
	 * The first answer of a type ('' when none), e.g. the visitor's name or email.
	 *
	 * @param array  $answers Cleaned answers.
	 * @param string $type    Field type.
	 */
	public static function first_of( array $answers, string $type ): string {
		foreach ( $answers as $answer ) {
			if ( $type === $answer['type'] && '' !== $answer['value'] ) {
				return $answer['value'];
			}
		}

		return '';
	}

	/**
	 * Stored [label, value] pairs as "label: value" lines.
	 *
	 * @param mixed $pairs Meta value.
	 * @return string[]
	 */
	public static function lines( $pairs ): array {
		$lines = array();
		foreach ( is_array( $pairs ) ? $pairs : array() as $pair ) {
			if ( is_array( $pair ) && 2 === count( $pair ) ) {
				$lines[] = $pair[0] . ': ' . $pair[1];
			}
		}

		return $lines;
	}
}
