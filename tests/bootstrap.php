<?php
/**
 * Startpunkt für PHPUnit.
 *
 * @package A11yContentCheck
 */

declare( strict_types = 1 );

require_once __DIR__ . '/../vendor/autoload.php';

if ( ! function_exists( '__' ) ) {
	/**
	 * Ersatz für die Übersetzungsfunktion von WordPress.
	 *
	 * Die Prüfungen brauchen von WordPress nichts weiter als diese eine
	 * Funktion. Im Test genügt eine Fassung, die den Text unverändert
	 * zurückgibt - deshalb braucht es hier keine WordPress-Installation
	 * und keine Datenbank.
	 *
	 * @param string $text   Der zu übersetzende Text.
	 * @param string $domain Die Textdomain, hier ohne Bedeutung.
	 */
	function __( string $text, string $domain = 'default' ): string {
		return $text;
	}
}