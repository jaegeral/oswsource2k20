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

trait BaseStaticTrait {

	/**
	 * Gibt die Version als String zurück.
	 *
	 * @return string
	 */
	public static function getVersion():string {
		return self::CLASS_MAJOR_VERSION.'.'.self::CLASS_MINOR_VERSION.'.'.self::CLASS_RELEASE_VERSION.self::CLASS_EXTRA_VERSION;
	}

	/**
	 * Gibt die Version als Integer zurück.
	 *
	 * @return int
	 */
	public static function getVersionId():int {
		return self::CLASS_MAJOR_VERSION.sprintf('%02d', self::CLASS_MINOR_VERSION).sprintf('%02d', self::CLASS_RELEASE_VERSION);
	}

	/**
	 * Liefert den Klassennamen ohne Namespace.
	 *
	 * @return string
	 */
	public static function getClassName():string {
		return str_replace(__NAMESPACE__.'\\', '', __CLASS__);
	}

	/**
	 * Liefert den Klassennamen mit Namespace als String mit _.
	 *
	 * @return string
	 */
	public static function getNameAsString():string {
		return str_replace('\\', '_', __CLASS__);
	}

}

?>