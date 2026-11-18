<?php
/**
 * Prüfung: Bilder ohne Alternativtext.
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
 * Findet Bilder, denen das alt-Attribut vollständig fehlt.
 *
 * Ein leeres alt="" wird nicht gemeldet: Es ist die korrekte Angabe für ein
 * rein dekoratives Bild und sagt dem Screenreader, dass er es überspringen
 * soll. Fehlt das Attribut dagegen ganz, liest er häufig den Dateinamen vor.
 */
final class ImageAltCheck implements Check {

	use Snippet;

	/**
	 * Eindeutiger Bezeichner der Prüfung.
	 */
	public function id(): string {
		return 'image-alt';
	}

	/**
	 * Name für die Ergebnistabelle.
	 */
	public function label(): string {
		return __( 'Bild ohne Alternativtext', 'a11y-content-check' );
	}

	/**
	 * Sucht alle img-Elemente ohne alt-Attribut.
	 *
	 * @param DOMXPath $xpath Das zu prüfende Dokument.
	 * @return Finding[]
	 */
	public function run( DOMXPath $xpath ): array {
		$nodes = $xpath->query( '//img[not(@alt)]' );

		if ( false === $nodes ) {
			return array();
		}

		$findings = array();

		foreach ( $nodes as $node ) {
			if ( ! $node instanceof DOMElement ) {
				continue;
			}

			$findings[] = new Finding(
				$this->id(),
				$this->label(),
				$this->snippet( $node ),
				Finding::SEVERITY_ERROR
			);
		}

		return $findings;
	}
}