<?php

/**
 * This file is part of the osWFrame package
 *
 * @author Juergen Schwind
 * @copyright Copyright (c) JBS New Media GmbH - Juergen Schwind (https://jbs-newmedia.com)
 * @package osWFrame
 * @link https://oswframe.com
 * @license https://www.gnu.org/licenses/gpl-3.0.html GNU General Public License 3
 */

namespace osWFrame\Core;

class Cookie {

	use BaseStaticTrait;

	/**
	 * Major-Version der Klasse.
	 */
	private const CLASS_MAJOR_VERSION=1;

	/**
	 * Minor-Version der Klasse.
	 */
	private const CLASS_MINOR_VERSION=0;

	/**
	 * Release-Version der Klasse.
	 */
	private const CLASS_RELEASE_VERSION=0;

	/**
	 * Extra-Version der Klasse.
	 * Zum Beispiel alpha, beta, rc1, rc2 ...
	 */
	private const CLASS_EXTRA_VERSION='';

	/**
	 * Cookie constructor.
	 */
	private function __construct() {

	}

	/**
	 * @param string $name
	 * @param string|null $value
	 * @param int|null $expires
	 * @param string|null $path
	 * @param string|null $domain
	 * @param bool|null $secure
	 * @param bool|null $httponly
	 * @return bool
	 */
	public static function setCookie(string $name, string $value=null, int $expires=null, string $path=null, string $domain=null, bool $secure=null, bool $httponly=null):bool {
		return setcookie($name, $value, $expires, $path, $domain, $secure, $httponly);
	}

}

?>