<?php
/**
 * Ein einzelner Befund.
 *
 * @package A11yContentCheck
 */

declare( strict_types = 1 );

namespace A11yContentCheck;

/**
 * Ein Befund: was gefunden wurde und an welcher Stelle.
 *
 * Unveränderlich: einmal erzeugt, ändert sich ein Befund nicht mehr.
 */
final class Finding {

	public const SEVERITY_ERROR   = 'error';
	public const SEVERITY_WARNING = 'warning';

	/**
	 * @param string $check_id Bezeichner der Prüfung, die den Befund erzeugt hat.
	 * @param string $message  Beschreibung für den Benutzer.
	 * @param string $snippet  Die gefundene Stelle im HTML, gekürzt.
	 * @param string $severity SEVERITY_ERROR oder SEVERITY_WARNING.
	 */
	public function __construct(
		public readonly string $check_id,
		public readonly string $message,
		public readonly string $snippet,
		public readonly string $severity = self::SEVERITY_ERROR
	) {
	}
}