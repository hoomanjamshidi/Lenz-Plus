<?php
/**
 * Portfolio screen: Lenz's projects with their gallery and the plugin's
 * project details (type, summary, year, facts, steps, client's quote,
 * featured flag).
 *
 * The gallery is Lenz's own `_gallery` list of attachment IDs (photos and
 * videos); Lenz's external links (`_external_links`) are left untouched.
 * Project details go through Project_Meta_Box::clean(), the same cleaning as
 * the box on WordPress's editor.
 *
 * @package LenzPlus
 */

namespace LenzPlus\Modules\Panel\Screens;

use LenzPlus\Modules\Builder\Portfolio_Data;
use LenzPlus\Modules\Builder\Project_Meta_Box;
use LenzPlus\Modules\Panel\Ui;
use LenzPlus\Modules\Panel\Uploads;

defined( 'ABSPATH' ) || exit;

/**
 * Projects list and editor.
 */
final class Portfolio extends Content_Screen {

	/** Screen id. */
	public function id(): string {
		return 'portfolio';
	}

	/** Menu label. */
	public function title(): string {
		return __( 'Portfolio', 'lenz-plus' );
	}

	/** Menu icon. */
	public function icon(): string {
		return 'images';
	}

	/** Settings switch. */
	public function section(): string {
		return 'portfolio';
	}

	/** Lenz's project post type. */
	protected function post_type(): string {
		return Portfolio_Data::POST_TYPE;
	}

	/** Lenz's project categories. */
	protected function taxonomy(): string {
		return Portfolio_Data::TAXONOMY;
	}

	/** "New" label. */
	protected function new_label(): string {
		return __( 'New project', 'lenz-plus' );
	}

	/** Edit page title. */
	protected function edit_label(): string {
		return __( 'Edit project', 'lenz-plus' );
	}

	/** Empty list. */
	protected function empty_label(): string {
		return __( 'No projects yet. Add your first one with the + button.', 'lenz-plus' );
	}

	/** Main text label. */
	protected function content_label(): string {
		return __( 'About the project', 'lenz-plus' );
	}

	/**
	 * Gallery and project details.
	 *
	 * @param \WP_Post|null $post Project, null for a new one.
	 */
	protected function render_fields( ?\WP_Post $post ): void {
		$id      = $post ? (int) $post->ID : 0;
		$details = Portfolio_Data::details( $id );
		$gallery = $id ? array_map( 'intval', (array) get_post_meta( $id, '_gallery', true ) ) : array();

		$facts = array();
		foreach ( $details['facts'] as $fact ) {
			$facts[] = '' !== $fact[0] ? $fact[0] . ': ' . $fact[1] : $fact[1];
		}

		$kinds = array( '' => __( 'From the gallery', 'lenz-plus' ) ) + Project_Meta_Box::kind_labels();
		?>
		<section class="lzp-card">
			<h2 class="lzp-card__title"><?php esc_html_e( 'Gallery', 'lenz-plus' ); ?></h2>
			<?php
			Uploads::field(
				'gallery',
				__( 'Photos and videos', 'lenz-plus' ),
				$gallery,
				array(
					'multiple' => true,
					'accept'   => 'image/*,video/*',
					'help'     => __( 'Shown in this order on the project page. Use the arrows to move a photo.', 'lenz-plus' ),
				)
			);
			?>
		</section>
		<section class="lzp-card">
			<h2 class="lzp-card__title"><?php esc_html_e( 'Project details', 'lenz-plus' ); ?></h2>
			<?php Ui::select( 'lzp_project[kind]', __( 'Project type', 'lenz-plus' ), $kinds, $details['kind'], array( 'help' => __( 'Each type can have its own page design.', 'lenz-plus' ) ) ); ?>
			<?php Ui::textarea( 'lzp_project[summary]', __( 'Summary', 'lenz-plus' ), $details['summary'], array( 'rows' => 2 ) ); ?>
			<?php Ui::input( 'lzp_project[year]', __( 'Year', 'lenz-plus' ), $details['year'], array( 'inputmode' => 'numeric' ) ); ?>
			<?php Ui::textarea( 'lzp_project[facts]', __( 'Facts', 'lenz-plus' ), implode( "\n", $facts ), array( 'help' => __( 'One per line, as "Label: value" (e.g. Client: Nova).', 'lenz-plus' ) ) ); ?>
			<?php Ui::textarea( 'lzp_project[done]', __( 'What was done', 'lenz-plus' ), implode( "\n", $details['done'] ), array( 'help' => __( 'One step per line.', 'lenz-plus' ) ) ); ?>
			<details class="lzp-more"<?php echo '' !== $details['quote'] ? ' open' : ''; ?>>
				<summary><?php esc_html_e( 'Client\'s quote', 'lenz-plus' ); ?></summary>
				<?php Ui::textarea( 'lzp_project[quote]', __( 'Quote', 'lenz-plus' ), $details['quote'], array( 'rows' => 3 ) ); ?>
				<div class="lzp-fields-2">
					<?php Ui::input( 'lzp_project[quote_name]', __( 'Name', 'lenz-plus' ), $details['quote_name'] ); ?>
					<?php Ui::input( 'lzp_project[quote_role]', __( 'Role', 'lenz-plus' ), $details['quote_role'] ); ?>
				</div>
			</details>
		</section>
		<?php
	}

	/**
	 * The featured switch in the side column.
	 *
	 * @param \WP_Post|null $post Project, null for a new one.
	 */
	protected function render_side_fields( ?\WP_Post $post ): void {
		echo '<section class="lzp-card">';
		Ui::toggle( 'lzp_project_featured', __( 'Featured project', 'lenz-plus' ), $post && Portfolio_Data::is_featured( (int) $post->ID ), array( 'help' => __( 'Shown in "Featured projects".', 'lenz-plus' ) ) );
		echo '</section>';
	}

	/**
	 * Saves the gallery, the details and the featured flag.
	 *
	 * @param int $id Project ID.
	 */
	protected function save_fields( int $id ): void {
		update_post_meta( $id, '_gallery', Uploads::posted_ids( 'gallery', $id ) );
		update_post_meta( $id, Portfolio_Data::META, Project_Meta_Box::clean( self::posted_array( 'lzp_project' ) ) );

		if ( '1' === self::posted( 'lzp_project_featured' ) ) {
			update_post_meta( $id, Portfolio_Data::META_FEATURED, '1' );
		} else {
			delete_post_meta( $id, Portfolio_Data::META_FEATURED );
		}
	}

	/**
	 * Featured projects get a pill in the list.
	 *
	 * @param \WP_Post $post Project.
	 */
	protected function row( \WP_Post $post ): array {
		$row = parent::row( $post );
		if ( Portfolio_Data::is_featured( (int) $post->ID ) && '' === $row['pill'] ) {
			$row['pill'] = Ui::pill( 'new', __( 'Featured', 'lenz-plus' ) );
		}

		return $row;
	}
}
