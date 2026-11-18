<?php
/**
 * Zentrale Klasse des Plugins.
 *
 * @package A11yContentCheck
 */

declare( strict_types = 1 );

namespace A11yContentCheck;

use A11yContentCheck\Admin\AdminPage;

/**
 * Hängt die Bestandteile des Plugins in WordPress ein.
 */
final class Plugin {

	/**
	 * Registriert alle Hooks. Einziger Einstiegspunkt des Plugins.
	 */
	public function register(): void {
		add_action( 'init', array( $this, 'load_textdomain' ) );

		if ( is_admin() ) {
			( new AdminPage() )->register();
		}
	}

	/**
	 * Lädt die Übersetzungsdateien aus dem Ordner /languages.
	 */
	public function load_textdomain(): void {
		load_plugin_textdomain(
			'a11y-content-check',
			false,
			dirname( plugin_basename( PLUGIN_FILE ) ) . '/languages'
		);
	}
}