<?php
/**
 * Plugin Name:       A11y Content Check
 * Plugin URI:        https://github.com/messaoudi-dev/a11y-content-check
 * Description:       Prüft veröffentlichte Seiten und Beiträge auf vier häufige Barrierefreiheitsfehler im HTML.
 * Version:           0.1.0
 * Requires at least: 6.0
 * Requires PHP:      8.1
 * Author:            Ayoub Messaoudi
 * Author URI:        https://github.com/messaoudi-dev
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       a11y-content-check
 * Domain Path:       /languages
 *
 * @package A11yContentCheck
 */

declare( strict_types = 1 );

namespace A11yContentCheck;

// Direkten Aufruf dieser Datei über den Browser verhindern.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

const VERSION     = '0.1.0';
const PLUGIN_FILE = __FILE__;

/**
 * Autoloader: bildet A11yContentCheck\Foo\Bar auf src/Foo/Bar.php ab.
 */
spl_autoload_register(
	static function ( string $class_name ): void {
		$prefix = __NAMESPACE__ . '\\';

		if ( ! str_starts_with( $class_name, $prefix ) ) {
			return;
		}

		$relative = substr( $class_name, strlen( $prefix ) );
		$path     = __DIR__ . '/src/' . str_replace( '\\', '/', $relative ) . '.php';

		if ( is_readable( $path ) ) {
			require_once $path;
		}
	}
);

( new Plugin() )->register();