<?php
/**
 * Prüfung: Reihenfolge der Überschriften.
 *
 * @package A11yContentCheck
 */

declare( strict_types = 1 );

namespace A11yContentCheck\Checks;

use A11yContentCheck\Check;
use A11yContentCheck\Finding;
use DOMElement;
use DOMXPath;

/**
 * Findet Sprünge in der Überschriften-Hierarchie und mehrfache h1.
 *
 * Überschriften sind für Screenreader-Nutzer das Inhaltsverzeichnis der
 * Seite: Sie springen von Überschrift zu Überschrift und erschließen sich
 * daraus den Aufbau. Folgt auf eine h2 direkt eine h4, fehlt eine Ebene -
 * die Gliederung stimmt nicht mehr.
 *
 * Eine fehlende h1 wird bewusst NICHT gemeldet: Geprüft wird nur der
 * Inhalt eines Beitrags, während die h1 meist vom Theme aus dem Titel
 * erzeugt wird und hier gar nicht sichtbar ist.
 */
final class HeadingOrderCheck implements Check {

	use Snippet;

	/**
	 * Eindeutiger Bezeichner der Prüfung.
	 */
	public function id(): string {
		return 'heading-order';
	}

	/**
	 * Name für die Ergebnistabelle.
	 */
	public function label(): string {
		return __( 'Überschriften-Hierarchie', 'a11y-content-check' );
	}

	/**
	 * Geht die Überschriften in Dokumentreihenfolge durch.
	 *
	 * @param DOMXPath $xpath Das zu prüfende Dokument.
	 * @return Finding[]
	 */
	public function run( DOMXPath $xpath ): array {
		$nodes = $xpath->query( '//h1|//h2|//h3|//h4|//h5|//h6' );

		if ( false === $nodes ) {
			return array();
		}

		$findings = array();
		$previous = null;
		$h1_count = 0;

		foreach ( $nodes as $node ) {
			if ( ! $node instanceof DOMElement ) {
				continue;
			}

			$level = (int) substr( strtolower( $node->nodeName ), 1 );

			if ( 1 === $level ) {
				++$h1_count;

				if ( $h1_count > 1 ) {
					$findings[] = new Finding(
						$this->id(),
						__( 'Mehr als eine h1 im Inhalt', 'a11y-content-check' ),
						$this->snippet( $node ),
						Finding::SEVERITY_WARNING
					);
				}
			}

			if ( null !== $previous && $level > $previous + 1 ) {
				$findings[] = new Finding(
					$this->id(),
					sprintf(
						/* translators: 1: vorheriges Level, etwa h2. 2: gefundenes Level, etwa h4. */
						__( 'Überschriftensprung von %1$s auf %2$s', 'a11y-content-check' ),
						'h' . $previous,
						'h' . $level
					),
					$this->snippet( $node ),
					Finding::SEVERITY_WARNING
				);
			}

			$previous = $level;
		}

		return $findings;
	}
}