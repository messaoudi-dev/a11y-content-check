<?php
/**
 * Tests für die Prüfung auf fehlende Alternativtexte.
 *
 * @package A11yContentCheck
 */

declare( strict_types = 1 );

namespace A11yContentCheck\Tests;

use A11yContentCheck\Checks\ImageAltCheck;
use A11yContentCheck\Finding;
use A11yContentCheck\Html;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass( ImageAltCheck::class )]
final class ImageAltCheckTest extends TestCase {

	public function test_meldet_bild_ohne_alt_attribut(): void {
		$findings = $this->check( '<img src="bild.jpg">' );

		self::assertCount( 1, $findings );
		self::assertSame( 'image-alt', $findings[0]->check_id );
		self::assertSame( Finding::SEVERITY_ERROR, $findings[0]->severity );
	}

	public function test_meldet_leeres_alt_nicht(): void {
		self::assertSame( array(), $this->check( '<img src="bild.jpg" alt="">' ) );
	}

	public function test_meldet_gefuelltes_alt_nicht(): void {
		self::assertSame( array(), $this->check( '<img src="bild.jpg" alt="Ein Hund im Garten">' ) );
	}

	public function test_meldet_jedes_betroffene_bild_einzeln(): void {
		$html = '<img src="a.jpg"><img src="b.jpg" alt="B"><img src="c.jpg">';

		self::assertCount( 2, $this->check( $html ) );
	}

	public function test_schnipsel_nennt_die_fundstelle(): void {
		$findings = $this->check( '<img src="bild.jpg">' );

		self::assertStringContainsString( 'bild.jpg', $findings[0]->snippet );
	}

	public function test_dokument_ohne_bilder_liefert_nichts(): void {
		self::assertSame( array(), $this->check( '<p>Nur Text, kein Bild.</p>' ) );
	}

	/**
	 * Lässt die Prüfung über ein HTML-Fragment laufen.
	 *
	 * @param string $html Das Fragment.
	 * @return Finding[]
	 */
	private function check( string $html ): array {
		return ( new ImageAltCheck() )->run( Html::to_xpath( $html ) );
	}
}