<?php
/**
 * Prüfung: nichtssagende Linktexte.
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
 * Findet Links, deren Text ohne den umgebenden Satz nichts aussagt.
 *
 * Screenreader bieten an, alle Links einer Seite als Liste vorzulesen.
 * Steht dort zehnmal "mehr", ist die Liste wertlos. Der Linktext muss
 * allein verständlich sein - das ist WCAG 2.4.4.
 */
final class LinkTextCheck implements Check {

	use Snippet;

	/**
	 * Standardliste nichtssagender Linktexte, kleingeschrieben.
	 */
	public const DEFAULT_VAGUE_TEXTS = array(
		'hier',
		'hier klicken',
		'klick hier',
		'klicken sie hier',
		'mehr',
		'weiter',
		'weiterlesen',
		'link',
		'dokument',
		'here',
		'click here',
		'more',
		'read more',
	);

	/**
	 * Die verwendete Liste, bereits vereinheitlicht.
	 *
	 * @var string[]
	 */
	private readonly array $vague_texts;

	/**
	 * @param string[]|null $vague_texts Eigene Liste; null nimmt die Standardliste.
	 */
	public function __construct( ?array $vague_texts = null ) {
		$this->vague_texts = array_map(
			fn( string $text ): string => $this->normalize( $text ),
			$vague_texts ?? self::DEFAULT_VAGUE_TEXTS
		);
	}

	/**
	 * Eindeutiger Bezeichner der Prüfung.
	 */
	public function id(): string {
		return 'link-text';
	}

	/**
	 * Name für die Ergebnistabelle.
	 */
	public function label(): string {
		return __( 'Nichtssagender Linktext', 'a11y-content-check' );
	}

	/**
	 * Sucht alle Links, deren Text auf der Liste steht.
	 *
	 * @param DOMXPath $xpath Das zu prüfende Dokument.
	 * @return Finding[]
	 */
	public function run( DOMXPath $xpath ): array {
		$nodes = $xpath->query( '//a[@href]' );

		if ( false === $nodes ) {
			return array();
		}

		$findings = array();

		foreach ( $nodes as $node ) {
			if ( ! $node instanceof DOMElement ) {
				continue;
			}

			$text = $this->normalize( $node->textContent );

			if ( '' === $text ) {
				continue;
			}

			if ( ! in_array( $text, $this->vague_texts, true ) ) {
				continue;
			}

			$findings[] = new Finding(
				$this->id(),
				$this->label(),
				$this->snippet( $node ),
				Finding::SEVERITY_WARNING
			);
		}

		return $findings;
	}

	/**
	 * Vereinheitlicht einen Linktext für den Vergleich: Mehrfache Leerzeichen
	 * zu einem, abschließende Satzzeichen weg, alles klein.
	 *
	 * @param string $text Der rohe Text.
	 */
	private function normalize( string $text ): string {
		$text = preg_replace( '/\s+/u', ' ', $text ) ?? $text;
		$text = preg_replace( '/[\s.!?:;…»«]+$/u', '', $text ) ?? $text;

		return mb_strtolower( trim( $text ) );
	}
}