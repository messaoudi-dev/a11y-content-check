<?php
/**
 * Tests für die Prüfung auf nichtssagende Linktexte.
 *
 * @package A11yContentCheck
 */

declare( strict_types = 1 );

namespace A11yContentCheck\Tests;

use A11yContentCheck\Checks\LinkTextCheck;
use A11yContentCheck\Finding;
use A11yContentCheck\Html;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass( LinkTextCheck::class )]
final class LinkTextCheckTest extends TestCase {

	public function test_meldet_hier_klicken(): void {
		$findings = $this->check( '<a href="/a">hier klicken</a>' );

		self::assertCount( 1, $findings );
		self::assertSame( 'link-text', $findings[0]->check_id );
		self::assertSame( Finding::SEVERITY_WARNING, $findings[0]->severity );
	}

	public function test_meldet_sprechenden_linktext_nicht(): void {
		self::assertSame( array(), $this->check( '<a href="/a">Unsere Preise</a>' ) );
	}

	public function test_ignoriert_gross_und_kleinschreibung(): void {
		self::assertCount( 1, $this->check( '<a href="/a">Weiterlesen</a>' ) );
	}

	public function test_ignoriert_leerzeichen_und_satzzeichen(): void {
		self::assertCount( 1, $this->check( '<a href="/a">  Link . </a>' ) );
	}

	public function test_prueft_links_ohne_href_nicht(): void {
		self::assertSame( array(), $this->check( '<a id="anker">mehr</a>' ) );
	}

	public function test_leerer_linktext_wird_nicht_gemeldet(): void {
		self::assertSame( array(), $this->check( '<a href="/a"><img src="x.jpg" alt="Start"></a>' ) );
	}

	public function test_eigene_wortliste_ersetzt_die_standardliste(): void {
		$html  = '<a href="/a">Bitte hier</a><a href="/b">mehr</a>';
		$check = new LinkTextCheck( array( 'bitte hier' ) );

		$findings = $check->run( Html::to_xpath( $html ) );

		self::assertCount( 1, $findings );
		self::assertStringContainsString( 'Bitte hier', $findings[0]->snippet );
	}

	/**
	 * Lässt die Prüfung mit der Standardliste über ein HTML-Fragment laufen.
	 *
	 * @param string $html Das Fragment.
	 * @return Finding[]
	 */
	private function check( string $html ): array {
		return ( new LinkTextCheck() )->run( Html::to_xpath( $html ) );
	}
}