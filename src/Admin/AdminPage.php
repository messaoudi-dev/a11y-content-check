<?php
/**
 * Die Admin-Seite unter "Werkzeuge".
 *
 * @package A11yContentCheck
 */

declare( strict_types = 1 );

namespace A11yContentCheck\Admin;

use A11yContentCheck\Checks\FormLabelCheck;
use A11yContentCheck\Checks\HeadingOrderCheck;
use A11yContentCheck\Checks\ImageAltCheck;
use A11yContentCheck\Checks\LinkTextCheck;
use A11yContentCheck\Finding;
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
				<div class="notice notice-info">
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

				<?php $this->render_results( $results ); ?>
			<?php endif; ?>
		</div>
		<?php
	}

	/**
	 * Zeichnet die Ergebnistabelle.
	 *
	 * @param array<int, Finding[]> $results Ergebnis des Scanners.
	 */
	private function render_results( array $results ): void {
		$with_findings = array_filter(
			$results,
			static fn( array $findings ): bool => array() !== $findings
		);

		if ( array() === $with_findings ) {
			printf(
				'<p>%s</p>',
				esc_html__( 'Keine Befunde. Alle geprüften Inhalte sind in Ordnung.', 'a11y-content-check' )
			);

			return;
		}
		?>
		<table class="wp-list-table widefat striped">
			<caption class="screen-reader-text">
				<?php esc_html_e( 'Gefundene Barrierefreiheitsfehler, nach Seite gruppiert', 'a11y-content-check' ); ?>
			</caption>
			<thead>
				<tr>
					<th scope="col"><?php esc_html_e( 'Seite', 'a11y-content-check' ); ?></th>
					<th scope="col"><?php esc_html_e( 'Art', 'a11y-content-check' ); ?></th>
					<th scope="col"><?php esc_html_e( 'Befund', 'a11y-content-check' ); ?></th>
					<th scope="col"><?php esc_html_e( 'Fundstelle', 'a11y-content-check' ); ?></th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ( $with_findings as $post_id => $findings ) : ?>
					<?php foreach ( array_values( $findings ) as $index => $finding ) : ?>
						<tr>
							<?php if ( 0 === $index ) : ?>
								<th scope="rowgroup" rowspan="<?php echo (int) count( $findings ); ?>">
									<?php $this->render_post_link( (int) $post_id ); ?>
								</th>
							<?php endif; ?>
							<td><?php echo esc_html( $this->severity_label( $finding->severity ) ); ?></td>
							<td><?php echo esc_html( $finding->message ); ?></td>
							<td><code><?php echo esc_html( $finding->snippet ); ?></code></td>
						</tr>
					<?php endforeach; ?>
				<?php endforeach; ?>
			</tbody>
		</table>
		<?php
	}

	/**
	 * Zeichnet den Titel eines Beitrags, wenn möglich als Link in den Editor.
	 *
	 * @param int $post_id Die Beitrags-ID.
	 */
	private function render_post_link( int $post_id ): void {
		$title = get_the_title( $post_id );
		$link  = get_edit_post_link( $post_id );

		if ( null === $link || '' === $link ) {
			echo esc_html( $title );

			return;
		}

		printf(
			'<a href="%s">%s</a>',
			esc_url( $link ),
			esc_html( $title )
		);
	}

	/**
	 * Übersetzt die Schwere in ein Wort für die Tabelle.
	 *
	 * @param string $severity Finding::SEVERITY_ERROR oder SEVERITY_WARNING.
	 */
	private function severity_label( string $severity ): string {
		return Finding::SEVERITY_WARNING === $severity
			? __( 'Warnung', 'a11y-content-check' )
			: __( 'Fehler', 'a11y-content-check' );
	}

	/**
	 * Baut den Scanner mit allen Prüfungen.
	 *
	 * Dies ist die einzige Stelle, an der eine neue Prüfung eingetragen wird.
	 */
	private function create_scanner(): Scanner {
		return new Scanner(
			new FormLabelCheck(),
			new HeadingOrderCheck(),
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
	 * @param array<int, Finding[]> $results Ergebnis des Scanners.
	 */
	private function count_findings( array $results ): int {
		$total = 0;

		foreach ( $results as $findings ) {
			$total += count( $findings );
		}

		return $total;
	}
}