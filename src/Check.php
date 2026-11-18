<?php
/**
 * Schnittstelle aller Prüfungen.
 *
 * @package A11yContentCheck
 */

declare( strict_types = 1 );

namespace A11yContentCheck;

use DOMXPath;

/**
 * Gemeinsame Schnittstelle aller Prüfungen.
 *
 * Eine Prüfung kennt WordPress nicht. Sie bekommt ein HTML-Dokument
 * als DOMXPath und gibt Befunde zurück - sonst nichts.
 */
interface Check {

	/**
	 * Kurzer, eindeutiger Bezeichner, zum Beispiel "image-alt".
	 */
	public function id(): string;

	/**
	 * Menschenlesbarer Name für die Ergebnistabelle.
	 */
	public function label(): string;

	/**
	 * Durchsucht das Dokument.
	 *
	 * @param DOMXPath $xpath Das zu prüfende Dokument.
	 * @return Finding[] Liste der Befunde, leer wenn nichts gefunden wurde.
	 */
	public function run( DOMXPath $xpath ): array;
}