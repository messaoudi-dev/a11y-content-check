<?php
/**
 * Hilfsfunktion für alle Prüfungen: die Fundstelle als kurzes HTML.
 *
 * @package A11yContentCheck
 */

declare( strict_types = 1 );

namespace A11yContentCheck\Checks;

use DOMNode;

/**
 * Erzeugt aus einem gefundenen Knoten ein kurzes, lesbares HTML-Schnipsel.
 */
trait Snippet {

	/**
	 * Gibt den Knoten als HTML zurück, auf 120 Zeichen gekürzt.
	 *
	 * @param DOMNode $node Der gefundene Knoten.
	 */
	private function snippet( DOMNode $node ): string {
		$html = $node->ownerDocument?->saveHTML( $node );

		if ( ! is_string( $html ) || '' === trim( $html ) ) {
			return '';
		}

		return mb_strimwidth( trim( $html ), 0, 120, '…' );
	}
}