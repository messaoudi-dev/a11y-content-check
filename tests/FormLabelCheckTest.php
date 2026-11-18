<?php
/**
 * Tests für die Prüfung auf Formularfelder ohne Beschriftung.
 *
 * @package A11yContentCheck
 */

declare( strict_types = 1 );

namespace A11yContentCheck\Tests;

use A11yContentCheck\Checks\FormLabelCheck;
use A11yContentCheck\Finding;
use A11yContentCheck\Html;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass( FormLabelCheck::class )]
final class FormLabelCheckTest extends TestCase {

	public function test_meldet_feld_ohne_beschriftung(): void {
		$findings = $this->check( '<input type="text" name="telefon">' );

		self::assertCount( 1, $findings );
		self::assertSame( 'form-label', $findings[0]->check_id );
		self::assertSame( Finding::SEVERITY_ERROR, $findings[0]->severity );
	}

	public function test_label_mit_for_zaehlt_als_beschriftung(): void {
		$html = '<label for="mail">E-Mail</label><input type="email" id="mail">';

		self::assertSame( array(), $this->check( $html ) );
	}

	public function test_umschliessendes_label_zaehlt_als_beschriftung(): void {
		$html = '<label>Nachname <input type="text" name="nachname"></label>';

		self::assertSame( array(), $this->check( $html ) );
	}

	public function test_aria_label_zaehlt_als_beschriftung(): void {
		self::assertSame( array(), $this->check( '<input type="search" aria-label="Suche">' ) );
	}

	public function test_knoepfe_und_versteckte_felder_brauchen_kein_label(): void {
		$html = '<input type="hidden" name="token"><input type="submit" value="Absenden">';

		self::assertSame( array(), $this->check( $html ) );
	}

	public function test_meldet_auch_textarea_und_select(): void {
		$html = '<textarea name="nachricht"></textarea><select name="anrede"><option>Frau</option></select>';

		self::assertCount( 2, $this->check( $html ) );
	}

	public function test_label_fuer_ein_anderes_feld_hilft_nicht(): void {
		$html = '<label for="mail">E-Mail</label><input type="text" id="telefon">';

		self::assertCount( 1, $this->check( $html ) );
	}

	/**
	 * Lässt die Prüfung über ein HTML-Fragment laufen.
	 *
	 * @param string $html Das Fragment.
	 * @return Finding[]
	 */
	private function check( string $html ): array {
		return ( new FormLabelCheck() )->run( Html::to_xpath( $html ) );
	}
}