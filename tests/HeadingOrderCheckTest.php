<?php
/**
 * Tests für die Prüfung der Überschriften-Hierarchie.
 *
 * @package A11yContentCheck
 */

declare( strict_types = 1 );

namespace A11yContentCheck\Tests;

use A11yContentCheck\Checks\HeadingOrderCheck;
use A11yContentCheck\Finding;
use A11yContentCheck\Html;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass( HeadingOrderCheck::class )]
final class HeadingOrderCheckTest extends TestCase {

	public function test_meldet_uebersprungene_ebene(): void {
		$findings = $this->check( '<h1>A</h1><h2>B</h2><h4>C</h4>' );

		self::assertCount( 1, $findings );
		self::assertSame( 'heading-order', $findings[0]->check_id );
		self::assertSame( Finding::SEVERITY_WARNING, $findings[0]->severity );
	}

	public function test_meldung_nennt_beide_ebenen(): void {
		$findings = $this->check( '<h1>A</h1><h2>B</h2><h4>C</h4>' );

		self::assertStringContainsString( 'h2', $findings[0]->message );
		self::assertStringContainsString( 'h4', $findings[0]->message );
	}

	public function test_lueckenlose_hierarchie_ist_in_ordnung(): void {
		self::assertSame( array(), $this->check( '<h1>A</h1><h2>B</h2><h3>C</h3>' ) );
	}

	public function test_ruecksprung_nach_oben_ist_in_ordnung(): void {
		$html = '<h1>A</h1><h2>B</h2><h3>C</h3><h4>D</h4><h2>E</h2>';

		self::assertSame( array(), $this->check( $html ) );
	}

	public function test_beginn_mit_tiefer_ebene_wird_nicht_gemeldet(): void {
		self::assertSame( array(), $this->check( '<h3>A</h3><h4>B</h4>' ) );
	}

	public function test_meldet_zweite_h1(): void {
		$findings = $this->check( '<h1>A</h1><h2>B</h2><h1>C</h1>' );

		self::assertCount( 1, $findings );
		self::assertStringContainsString( 'h1', $findings[0]->message );
	}

	public function test_dokument_ohne_ueberschriften_liefert_nichts(): void {
		self::assertSame( array(), $this->check( '<p>Nur Text.</p>' ) );
	}

	/**
	 * Lässt die Prüfung über ein HTML-Fragment laufen.
	 *
	 * @param string $html Das Fragment.
	 * @return Finding[]
	 */
	private function check( string $html ): array {
		return ( new HeadingOrderCheck() )->run( Html::to_xpath( $html ) );
	}
}