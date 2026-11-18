<?php
/**
 * Die Admin-Seite unter "Werkzeuge".
 *
 * @package A11yContentCheck
 */

declare( strict_types = 1 );

namespace A11yContentCheck\Admin;

use A11yContentCheck\Checks\ImageAltCheck;
use A11yContentCheck\Checks\LinkTextCheck;
use A11yContentCheck\Scanner;

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

		$results = null;

		if ( isset( $_POST['a11y_scan'] ) ) {
			check_admin_referer( self::NONCE_ACTION );
			$results = $this->create_scanner()->scan();
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

			<?php if ( null !== $results ) : ?>
				<div class="notice notice-success">
					<p>
						<?php
						echo esc_html(
							sprintf(
								/* translators: 1: Anzahl geprüfter Inhalte. 2: Anzahl der Befunde. */
								__( '%1$d Seiten und Beiträge geprüft, %2$d Befunde.', 'a11y-content-check' ),
								count( $results ),
								$this->count_findings( $results )
							)
						);
						?>
					</p>
				</div>
			<?php endif; ?>
		</div>
		<?php
	}

	/**
	 * Baut den Scanner mit allen Prüfungen.
	 *
	 * Dies ist die einzige Stelle, an der eine neue Prüfung eingetragen wird.
	 */
	private function create_scanner(): Scanner {
		return new Scanner(
			new ImageAltCheck(),
			new LinkTextCheck(
				(array) apply_filters(
					'a11y_content_check_vague_link_texts',
					LinkTextCheck::DEFAULT_VAGUE_TEXTS
				)
			),
		);
	}

	/**
	 * Zählt alle Befunde über alle Beiträge hinweg.
	 *
	 * @param array<int, \A11yContentCheck\Finding[]> $results Ergebnis des Scanners.
	 */
	private function count_findings( array $results ): int {
		$total = 0;

		foreach ( $results as $findings ) {
			$total += count( $findings );
		}

		return $total;
	}
}