<?php
/**
 * Die Admin-Seite unter "Werkzeuge".
 *
 * @package A11yContentCheck
 */

declare( strict_types = 1 );

namespace A11yContentCheck\Admin;

/**
 * Registriert und zeichnet die Seite "Barrierefreiheit" im Backend.
 */
final class AdminPage {

	/**
	 * Eindeutiger Name der Seite in der URL.
	 */
	public const MENU_SLUG = 'a11y-content-check';

	/**
	 * Name der Nonce-Aktion für das Formular.
	 */
	private const NONCE_ACTION = 'a11y_content_check_scan';

	/**
	 * Fähigkeit, die ein Benutzer braucht, um die Seite zu sehen.
	 */
	private const CAPABILITY = 'edit_posts';

	/**
	 * Hängt die Seite in das Backend-Menü ein.
	 */
	public function register(): void {
		add_action( 'admin_menu', array( $this, 'add_page' ) );
	}

	/**
	 * Legt den Menüpunkt unter "Werkzeuge" an.
	 */
	public function add_page(): void {
		add_management_page(
			__( 'Barrierefreiheit prüfen', 'a11y-content-check' ),
			__( 'Barrierefreiheit', 'a11y-content-check' ),
			self::CAPABILITY,
			self::MENU_SLUG,
			array( $this, 'render' )
		);
	}

	/**
	 * Zeichnet die Seite.
	 */
	public function render(): void {
		if ( ! current_user_can( self::CAPABILITY ) ) {
			wp_die( esc_html__( 'Du hast keine Berechtigung für diese Seite.', 'a11y-content-check' ) );
		}

		$scan_requested = isset( $_POST['a11y_scan'] );

		if ( $scan_requested ) {
			check_admin_referer( self::NONCE_ACTION );
		}
		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'Barrierefreiheit prüfen', 'a11y-content-check' ); ?></h1>

			<p>
				<?php esc_html_e( 'Durchsucht alle veröffentlichten Seiten und Beiträge auf vier häufige Barrierefreiheitsfehler im HTML.', 'a11y-content-check' ); ?>
			</p>

			<form method="post">
				<?php wp_nonce_field( self::NONCE_ACTION ); ?>
				<?php submit_button( __( 'Prüfung starten', 'a11y-content-check' ), 'primary', 'a11y_scan' ); ?>
			</form>

			<?php if ( $scan_requested ) : ?>
				<div class="notice notice-info">
					<p><?php esc_html_e( 'Formular angekommen, Nonce geprüft. Der Scanner folgt in Schritt 3.', 'a11y-content-check' ); ?></p>
				</div>
			<?php endif; ?>
		</div>
		<?php
	}
}