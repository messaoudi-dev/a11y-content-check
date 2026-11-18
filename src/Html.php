<?php
/**
 * Hilfsklasse: HTML einlesen.
 *
 * @package A11yContentCheck
 */

declare( strict_types = 1 );

namespace A11yContentCheck;

use DOMDocument;
use DOMXPath;

/**
 * Macht aus einem HTML-Fragment ein durchsuchbares Dokument.
 *
 * Eigene Klasse, damit Scanner und Tests dieselbe Einleseroutine benutzen.
 * Würde der Test anders einlesen als das Plugin, prüfte er etwas anderes,
 * als später tatsächlich läuft.
 */
final class Html {

	/**
	 * Liest HTML ein und gibt ein XPath-Objekt darauf zurück.
	 *
	 * @param string $html Das HTML-Fragment.
	 */
	public static function to_xpath( string $html ): DOMXPath {
		$document = new DOMDocument();

		// loadHTML nimmt ohne Hinweis ISO-8859-1 an und zerstört damit
		// Umlaute. Die vorangestellte XML-Deklaration ist der übliche Weg,
		// ihm UTF-8 mitzuteilen.
		$previous = libxml_use_internal_errors( true );

		$document->loadHTML(
			'<?xml encoding="utf-8" ?>' . $html,
			LIBXML_NOERROR | LIBXML_NOWARNING
		);

		libxml_clear_errors();
		libxml_use_internal_errors( $previous );

		return new DOMXPath( $document );
	}
}