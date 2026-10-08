<?php
/**
 * Frequently asked questions as bordered accordion rows with Lenz's
 * chevron, plus optional FAQ structured data for search engines.
 *
 * Built on <details>: it opens without JavaScript, and the shared `name`
 * attribute keeps only one answer open in browsers that support it.
 *
 * @package LenzPlus
 */

namespace LenzPlus\Modules\Builder\Elementor\Widgets;

use Elementor\Controls_Manager;
use Elementor\Repeater;
use LenzPlus\Modules\Builder\Context;
use LenzPlus\Modules\Builder\Course_Data;

defined( 'ABSPATH' ) || exit;

/**
 * The «FAQ (Lenz+)» widget.
 */
final class Faq extends Section_Base {

	/** Elementor widget id. */
	public function get_name(): string {
		return 'lzp-faq';
	}

	/** Name in the Elementor panel. */
	public function get_title(): string {
		/* translators: %s: widget name. */
		return sprintf( __( '%s (Lenz+)', 'lenz-plus' ), __( 'FAQ', 'lenz-plus' ) );
	}

	/** Elementor panel icon. */
	public function get_icon(): string {
		return 'eicon-accordion';
	}

	/** Panel search terms. */
	public function get_keywords(): array {
		return array_merge( parent::get_keywords(), array( 'faq', 'accordion', 'questions', 'سوال', 'پرسش' ) );
	}

	/** Content (questions, behaviour, structured data) and style controls. */
	protected function register_controls(): void {
		$this->start_content_section( 'section_content', __( 'Questions', 'lenz-plus' ) );

		$this->add_control(
			'source',
			array(
				'label'   => __( 'Questions', 'lenz-plus' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'custom',
				'options' => array(
					'custom' => __( 'Typed here', 'lenz-plus' ),
					'course' => __( 'The course\'s questions', 'lenz-plus' ),
				),
			)
		);

		$repeater = new Repeater();
		$repeater->add_control(
			'question',
			array(
				'label'       => __( 'Question', 'lenz-plus' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => __( 'A common question?', 'lenz-plus' ),
				'label_block' => true,
			)
		);
		$repeater->add_control(
			'answer',
			array(
				'label'   => __( 'Answer', 'lenz-plus' ),
				'type'    => Controls_Manager::WYSIWYG,
				'default' => '<p>' . __( 'A short, clear answer.', 'lenz-plus' ) . '</p>',
			)
		);

		$this->add_control(
			'items',
			array(
				'label'       => __( 'Questions', 'lenz-plus' ),
				'type'        => Controls_Manager::REPEATER,
				'fields'      => $repeater->get_controls(),
				'default'     => array(
					array( 'question' => __( 'A common question?', 'lenz-plus' ) ),
					array( 'question' => __( 'A common question?', 'lenz-plus' ) ),
				),
				'title_field' => '{{{ question }}}',
				'condition'   => array( 'source' => 'custom' ),
			)
		);

		$this->add_control(
			'open_first',
			array(
				'label'        => __( 'First answer open', 'lenz-plus' ),
				'type'         => Controls_Manager::SWITCHER,
				'default'      => '',
				'return_value' => 'yes',
			)
		);

		$this->add_control(
			'one_open',
			array(
				'label'        => __( 'Close the others when one opens', 'lenz-plus' ),
				'type'         => Controls_Manager::SWITCHER,
				'default'      => 'yes',
				'return_value' => 'yes',
			)
		);

		$this->add_control(
			'schema',
			array(
				'label'        => __( 'FAQ data for Google', 'lenz-plus' ),
				'type'         => Controls_Manager::SWITCHER,
				'default'      => 'yes',
				'return_value' => 'yes',
				'description'  => __( 'Adds FAQPage structured data. Turn it off if an SEO plugin already adds it for this page.', 'lenz-plus' ),
			)
		);

		$this->add_responsive_control(
			'max_width',
			array(
				'label'      => __( 'Maximum width', 'lenz-plus' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px', '%' ),
				'range'      => array(
					'px' => array(
						'min' => 320,
						'max' => 1400,
					),
				),
				'selectors'  => array( '{{WRAPPER}} .lzp-faq' => 'max-width: {{SIZE}}{{UNIT}};' ),
			)
		);

		$this->end_controls_section();

		$this->start_style_section( 'section_style', __( 'Questions', 'lenz-plus' ) );
		$this->add_gap_control( '.lzp-faq' );
		$this->add_box_style( 'item', '.lzp-faq__item' );
		$this->add_text_style( 'question', '.lzp-faq__q', array( 'label' => __( 'Question', 'lenz-plus' ) ) );
		$this->add_text_style( 'answer', '.lzp-faq__a', array( 'label' => __( 'Answer', 'lenz-plus' ) ) );
		$this->end_controls_section();
	}

	/** The course source reads the viewed course. */
	protected function is_dynamic_content(): bool {
		return true;
	}

	/** Prints the accordion and its structured data. */
	protected function render(): void {
		$s = $this->get_settings_for_display();

		if ( 'course' === $s['source'] ) {
			$id         = Context::current_item( 'product' );
			$s['items'] = array();
			foreach ( $id && Course_Data::is_course( $id ) ? Course_Data::details( $id )['faq'] : array() as $pair ) {
				$s['items'][] = array(
					'question' => (string) $pair[0],
					'answer'   => esc_html( (string) $pair[1] ),
				);
			}
			if ( ! $s['items'] ) {
				$this->editor_hint( __( 'Nothing to show yet: fill in the questions in the course\'s details.', 'lenz-plus' ) );
			}
		}

		if ( empty( $s['items'] ) ) {
			return;
		}

		$group  = 'yes' === $s['one_open'] ? ' name="lzp-faq-' . esc_attr( $this->get_id() ) . '"' : '';
		$schema = array();

		echo '<div class="lzp-faq">';
		foreach ( $s['items'] as $index => $item ) {
			$answer = wp_kses_post( $this->parse_text_editor( (string) $item['answer'] ) );

			printf(
				'<details class="lzp-faq__item"%1$s%2$s><summary class="lzp-faq__q"><span class="lzp-faq__question">%3$s</span>%4$s</summary><div class="lzp-faq__a">%5$s</div></details>',
				$group, // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped above.
				0 === $index && 'yes' === $s['open_first'] ? ' open' : '',
				esc_html( (string) $item['question'] ),
				self::icon( 'chevron-down', 'lzp-faq__sign' ), // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- icon markup from the bundled library.
				$answer // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- kses above.
			);

			$schema[] = array(
				'@type'          => 'Question',
				'name'           => wp_strip_all_tags( (string) $item['question'] ),
				'acceptedAnswer' => array(
					'@type' => 'Answer',
					'text'  => wp_strip_all_tags( $answer ),
				),
			);
		}
		echo '</div>';

		if ( 'yes' === $s['schema'] && ! $this->in_editor() ) {
			printf(
				'<script type="application/ld+json">%s</script>',
				wp_json_encode(
					array(
						'@context'   => 'https://schema.org',
						'@type'      => 'FAQPage',
						'mainEntity' => $schema,
					),
					JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG
				)
			);
		}
	}
}
