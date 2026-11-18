<?php
/**
 * Prüfung: Formularfelder ohne Beschriftung.
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
 * Findet Formularfelder, zu denen keine Beschriftung gehört.
 *
 * Ein Feld gilt als beschriftet, wenn eine der folgenden Angaben vorliegt:
 * ein label mit passendem for-Attribut, ein umschließendes label,
 * aria-label, aria-labelledby oder title. Ohne eine davon weiß ein
 * Screenreader-Nutzer nicht, was er eintragen soll.
 */
final class FormLabelCheck implements Check {

	use Snippet;

	/**
	 * Eingabetypen, die keine sichtbare Beschriftung brauchen.
	 *
	 * Knöpfe tragen ihren Namen im value-Attribut, hidden ist unsichtbar,
	 * und image wird über sein alt-Attribut benannt.
	 */
	private const TYPES_WITHOUT_LABEL = array(
		'hidden',
		'submit',
		'button',
		'reset',
		'image',
	);

	/**
	 * Attribute, die einem Feld allein schon einen Namen geben.
	 */
	private const NAMING_ATTRIBUTES = array(
		'aria-label',
		'aria-labelledby',
		'title',
	);

	/**
	 * Eindeutiger Bezeichner der Prüfung.
	 */
	public function id(): string {
		return 'form-label';
	}

	/**
	 * Name für die Ergebnistabelle.
	 */
	public function label(): string {
		return __( 'Formularfeld ohne Beschriftung', 'a11y-content-check' );
	}

	/**
	 * Sucht alle Formularfelder ohne Beschriftung.
	 *
	 * @param DOMXPath $xpath Das zu prüfende Dokument.
	 * @return Finding[]
	 */
	public function run( DOMXPath $xpath ): array {
		$nodes = $xpath->query( '//input|//select|//textarea' );

		if ( false === $nodes ) {
			return array();
		}

		$label_targets = $this->collect_label_targets( $xpath );
		$findings      = array();

		foreach ( $nodes as $node ) {
			if ( ! $node instanceof DOMElement ) {
				continue;
			}

			if ( $this->is_exempt( $node ) ) {
				continue;
			}

			if ( $this->has_name( $node, $xpath, $label_targets ) ) {
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

	/**
	 * Sammelt die for-Werte aller label-Elemente im Dokument.
	 *
	 * Einmal eingesammelt statt pro Feld neu gesucht: Das spart Abfragen und
	 * vermeidet, eine ID in einen XPath-Ausdruck einbauen zu müssen.
	 *
	 * @param DOMXPath $xpath Das Dokument.
	 * @return array<string, true> Die referenzierten IDs als Schlüssel.
	 */
	private function collect_label_targets( DOMXPath $xpath ): array {
		$labels = $xpath->query( '//label[@for]' );

		if ( false === $labels ) {
			return array();
		}

		$targets = array();

		foreach ( $labels as $label ) {
			if ( ! $label instanceof DOMElement ) {
				continue;
			}

			$for = trim( $label->getAttribute( 'for' ) );

			if ( '' !== $for ) {
				$targets[ $for ] = true;
			}
		}

		return $targets;
	}

	/**
	 * Braucht dieses Feld überhaupt eine Beschriftung?
	 *
	 * @param DOMElement $node Das Feld.
	 */
	private function is_exempt( DOMElement $node ): bool {
		if ( 'input' !== strtolower( $node->nodeName ) ) {
			return false;
		}

		$type = strtolower( trim( $node->getAttribute( 'type' ) ) );

		if ( '' === $type ) {
			$type = 'text';
		}

		return in_array( $type, self::TYPES_WITHOUT_LABEL, true );
	}

	/**
	 * Hat das Feld auf irgendeinem Weg einen Namen?
	 *
	 * @param DOMElement          $node          Das Feld.
	 * @param DOMXPath            $xpath         Das Dokument.
	 * @param array<string, true> $label_targets Die von labels referenzierten IDs.
	 */
	private function has_name( DOMElement $node, DOMXPath $xpath, array $label_targets ): bool {
		foreach ( self::NAMING_ATTRIBUTES as $attribute ) {
			if ( '' !== trim( $node->getAttribute( $attribute ) ) ) {
				return true;
			}
		}

		$id = trim( $node->getAttribute( 'id' ) );

		if ( '' !== $id && isset( $label_targets[ $id ] ) ) {
			return true;
		}

		$wrapping = $xpath->query( 'ancestor::label', $node );

		return false !== $wrapping && $wrapping->length > 0;
	}
}