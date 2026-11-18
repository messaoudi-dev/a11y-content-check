<?php
/**
 * Holt die Inhalte aus WordPress und lässt die Prüfungen darüber laufen.
 *
 * @package A11yContentCheck
 */

declare( strict_types = 1 );

namespace A11yContentCheck;

use WP_Query;

/**
 * Die einzige Klasse des Plugins, die WordPress kennt.
 *
 * Sie lädt die veröffentlichten Inhalte, macht aus dem HTML ein
 * durchsuchbares Dokument und reicht es an die Prüfungen weiter.
 */
final class Scanner {

	/**
	 * Die Prüfungen, die auf jedes Dokument angewendet werden.
	 *
	 * @var Check[]
	 */
	private readonly array $checks;

	/**
	 * @param Check ...$checks Beliebig viele Prüfungen.
	 */
	public function __construct( Check ...$checks ) {
		$this->checks = $checks;
	}

	/**
	 * Prüft alle veröffentlichten Seiten und Beiträge.
	 *
	 * Der Rückgabewert enthält jeden geprüften Beitrag, auch die ohne
	 * Befunde - dann mit einem leeren Array.
	 *
	 * @return array<int, Finding[]> Beitrags-ID => Befunde.
	 */
	public function scan(): array {
		// posts_per_page = -1 lädt alles auf einmal. Für eine Website mit
		// einigen hundert Seiten ist das vertretbar, für sehr große nicht.
		// Eine Stapelverarbeitung wäre der nächste Ausbauschritt.
		$query = new WP_Query(
			array(
				'post_type'      => array( 'post', 'page' ),
				'post_status'    => 'publish',
				'posts_per_page' => -1,
				'orderby'        => 'title',
				'order'          => 'ASC',
				'no_found_rows'  => true,
			)
		);

		$results = array();

		while ( $query->have_posts() ) {
			$query->the_post();

			$post_id = (int) get_the_ID();
			$html    = (string) apply_filters( 'the_content', get_the_content() );

			$results[ $post_id ] = $this->check_html( $html );
		}

		wp_reset_postdata();

		return $results;
	}

	/**
	 * Lässt alle Prüfungen über ein Stück HTML laufen.
	 *
	 * @param string $html Das fertig gerenderte HTML eines Beitrags.
	 * @return Finding[]
	 */
	private function check_html( string $html ): array {
		if ( '' === trim( $html ) ) {
			return array();
		}

		$xpath    = Html::to_xpath( $html );
		$findings = array();

		foreach ( $this->checks as $check ) {
			$findings = array_merge( $findings, $check->run( $xpath ) );
		}

		return $findings;
	}
}