<?php
/**
 * Account screen: the user's name, email and password, and, for those who
 * manage the site, its name and tagline. Logging out is here too.
 *
 * @package LenzPlus
 */

namespace LenzPlus\Modules\Panel\Screens;

use LenzPlus\Modules\Panel\Router;
use LenzPlus\Modules\Panel\Ui;

defined( 'ABSPATH' ) || exit;

/**
 * Profile and site settings.
 */
final class Account extends Screen {

	/** Screen id. */
	public function id(): string {
		return 'account';
	}

	/** Menu label. */
	public function title(): string {
		return __( 'My account', 'lenz-plus' );
	}

	/** Menu icon. */
	public function icon(): string {
		return 'user-circle';
	}

	/** Every logged-in panel user. */
	public function capability(): string {
		return 'read';
	}

	/** Prints the forms. */
	public function render(): void {
		$user = wp_get_current_user();
		?>
		<div class="lzp-edit__grid">
			<div class="lzp-edit__main">
				<section class="lzp-card">
					<h2 class="lzp-card__title"><?php esc_html_e( 'Profile', 'lenz-plus' ); ?></h2>
					<?php $this->form_open( 'profile' ); ?>
						<div class="lzp-fields-2">
							<?php Ui::input( 'first_name', __( 'First name', 'lenz-plus' ), (string) $user->first_name, array( 'autocomplete' => 'given-name' ) ); ?>
							<?php Ui::input( 'last_name', __( 'Last name', 'lenz-plus' ), (string) $user->last_name, array( 'autocomplete' => 'family-name' ) ); ?>
						</div>
						<?php Ui::input( 'display_name', __( 'Name shown on the site', 'lenz-plus' ), (string) $user->display_name, array( 'required' => true ) ); ?>
						<?php
						Ui::input(
							'email',
							__( 'Email', 'lenz-plus' ),
							(string) $user->user_email,
							array(
								'type'         => 'email',
								'dir'          => 'ltr',
								'required'     => true,
								'autocomplete' => 'email',
							)
						);
						?>
						<?php
						Ui::textarea(
							'description',
							__( 'About me', 'lenz-plus' ),
							(string) $user->description,
							array(
								'rows' => 3,
								'help' => __( 'Shown in the author box of your articles.', 'lenz-plus' ),
							)
						);
						?>
						<details class="lzp-more">
							<summary><?php esc_html_e( 'Change password', 'lenz-plus' ); ?></summary>
							<?php
							Ui::input(
								'pass1',
								__( 'New password', 'lenz-plus' ),
								'',
								array(
									'type'         => 'password',
									'dir'          => 'ltr',
									'autocomplete' => 'new-password',
								)
							);
							?>
							<?php
							Ui::input(
								'pass2',
								__( 'New password again', 'lenz-plus' ),
								'',
								array(
									'type'         => 'password',
									'dir'          => 'ltr',
									'autocomplete' => 'new-password',
								)
							);
							?>
						</details>
						<button type="submit" class="lzp-btn lzp-btn--primary"><?php esc_html_e( 'Save', 'lenz-plus' ); ?></button>
					</form>
				</section>
			</div>
			<aside class="lzp-edit__side">
				<?php if ( current_user_can( 'manage_options' ) ) : ?>
					<section class="lzp-card">
						<h2 class="lzp-card__title"><?php esc_html_e( 'Site', 'lenz-plus' ); ?></h2>
						<?php $this->form_open( 'site' ); ?>
							<?php Ui::input( 'blogname', __( 'Site name', 'lenz-plus' ), (string) get_option( 'blogname' ) ); ?>
							<?php Ui::input( 'blogdescription', __( 'Tagline', 'lenz-plus' ), (string) get_option( 'blogdescription' ) ); ?>
							<button type="submit" class="lzp-btn lzp-btn--primary"><?php esc_html_e( 'Save', 'lenz-plus' ); ?></button>
						</form>
					</section>
				<?php endif; ?>
				<section class="lzp-card">
					<div class="lzp-actions lzp-actions--stack">
						<a class="lzp-btn lzp-btn--ghost" href="<?php echo esc_url( home_url( '/' ) ); ?>" target="_blank" rel="noopener"><?php Ui::the_icon( 'globe' ); ?><span><?php esc_html_e( 'View the site', 'lenz-plus' ); ?></span></a>
						<?php if ( ! $this->module->access()->is_panel_only() ) : ?>
							<a class="lzp-btn lzp-btn--ghost" href="<?php echo esc_url( admin_url() ); ?>"><?php Ui::the_icon( 'settings' ); ?><span><?php esc_html_e( 'WordPress dashboard', 'lenz-plus' ); ?></span></a>
						<?php endif; ?>
						<a class="lzp-btn lzp-btn--danger" href="<?php echo esc_url( wp_logout_url( Router::url() ) ); ?>"><?php Ui::the_icon( 'logout' ); ?><span><?php esc_html_e( 'Log out', 'lenz-plus' ); ?></span></a>
					</div>
				</section>
			</aside>
		</div>
		<?php
	}

	/**
	 * Saves the profile or the site settings.
	 *
	 * @param string $action `profile` or `site`.
	 */
	public function handle( string $action ): string {
		if ( 'site' === $action ) {
			if ( ! current_user_can( 'manage_options' ) ) {
				return $this->done( 'denied' );
			}

			update_option( 'blogname', sanitize_text_field( self::posted( 'blogname' ) ) );
			update_option( 'blogdescription', sanitize_text_field( self::posted( 'blogdescription' ) ) );

			return $this->done( 'saved' );
		}

		if ( 'profile' !== $action ) {
			return $this->url();
		}

		return $this->done( $this->save_profile() );
	}

	/** Writes the profile; returns the message key. */
	private function save_profile(): string {
		$user  = wp_get_current_user();
		$email = sanitize_email( self::posted( 'email' ) );
		$pass1 = self::posted( 'pass1' );

		if ( ! is_email( $email ) ) {
			return 'email';
		}

		$owner = email_exists( $email );
		if ( $owner && (int) $owner !== (int) $user->ID ) {
			return 'email_taken';
		}

		$pass2 = self::posted( 'pass2' );
		if ( '' !== $pass1 && ! hash_equals( $pass1, $pass2 ) ) {
			return 'password';
		}

		$data = array(
			'ID'           => $user->ID,
			'first_name'   => sanitize_text_field( self::posted( 'first_name' ) ),
			'last_name'    => sanitize_text_field( self::posted( 'last_name' ) ),
			'display_name' => sanitize_text_field( self::posted( 'display_name' ) ),
			'user_email'   => $email,
			'description'  => sanitize_textarea_field( self::posted( 'description' ) ),
		);
		if ( '' !== $pass1 ) {
			$data['user_pass'] = $pass1;
		}

		$result = wp_update_user( $data );
		if ( is_wp_error( $result ) ) {
			return 'error';
		}

		if ( '' !== $pass1 ) {
			// A new password ends the session; log the user straight back in.
			wp_set_auth_cookie( $user->ID, true );
		}

		return 'saved';
	}
}
