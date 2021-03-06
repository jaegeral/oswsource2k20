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

class Filesystem {

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
	 * Filesystem constructor.
	 */
	private function __construct() {

	}

	/**
	 * Löscht eine Datei.
	 *
	 * @param string $file
	 * @return bool
	 */
	public static function unlink(string $file):bool {
		if (!file_exists($file)) {
			return true;
		}
		if (!is_file($file)) {
			return true;
		}
		if (@unlink($file)===true) {
			return true;
		}

		return false;
	}

	/**
	 * Prüft ob es sich um ein Verzeichnis handelt.
	 *
	 * @param string $filename
	 * @return bool
	 */
	public static function isDir(string $dirname):bool {
		self::clearStatcache();

		return is_dir($dirname);
	}

	/**
	 * Gibt den Verzeichnisnamen zurück.
	 *
	 * @param string $filename
	 * @return string
	 */
	public static function getDirName(string $filename):string {
		if (substr($filename, -1, 1)==DIRECTORY_SEPARATOR) {
			$filename.='.';
		}

		return dirname($filename).DIRECTORY_SEPARATOR;
	}

	/**
	 * Erstellt ein Verzeichnis.
	 *
	 * @param string $filename
	 * @param int $mod
	 * @return bool
	 */
	public static function makeDir(string $filename, int $mod=0):bool {
		$filename=self::getDirName($filename);
		if (self::isDir($filename)===true) {
			return true;
		}
		if ($mod==0) {
			$mod=Settings::getIntVar('settings_chmod_dir');
		}
		if (mkdir($filename, $mod, true)!==true) {
			return false;
		}
		self::changeFilemodeFromBase($filename, $mod);
		self::clearStatcache();

		return true;
	}

	/**
	 * @param string $dirname
	 * @return bool
	 */
	public static function protectDir(string $dirname):bool {
		if (substr($dirname, -1)!==DIRECTORY_SEPARATOR) {
			$dirname.=DIRECTORY_SEPARATOR;
		}
		self::makeDir($dirname);
		$file=$dirname.'.htaccess';
		if (self::existsFile($file)!==true) {
			file_put_contents($file, "order deny,allow\ndeny from all");
			self::changeFilemode($file);
		}

		return true;
	}

	/**
	 * Ändert die Zugriffsrechte der Datei.
	 *
	 * @param string $filename
	 * @param int $mod
	 * @return bool
	 */
	public static function changeFilemode(string $filename, int $mod=0):bool {
		if ($mod==0) {
			$mod=Settings::getIntVar('settings_chmod_dir');
		}

		return chmod($filename, $mod);
	}

	/**
	 * Ändert die Zugriffsrechte der Datei rekursiv.
	 *
	 * @param string $filename
	 * @param int $mod
	 * @return bool
	 */
	public static function changeFilemodeFromBase(string $filename, int $mod=0):bool {
		if ($mod==0) {
			$mod=Settings::getIntVar('settings_chmod_dir');
		}
		$list=explode(DIRECTORY_SEPARATOR, str_replace(Settings::getStringVar('settings_abspath'), '', $filename));
		$dir=Settings::getStringVar('settings_abspath');
		foreach ($list as $_dir) {
			if ($_dir!='') {
				$dir.=$_dir.DIRECTORY_SEPARATOR;
				self::changeFileMode($dir, $mod);
			}
		}

		return true;
	}

	/**
	 * Prüft ob es sich um eine Datei handelt.
	 *
	 * @param string $filename
	 * @return bool
	 */
	public static function isFile(string $filename):bool {
		self::clearStatcache();

		return is_file($filename);
	}

	/**
	 * Prüft ob die Datei existiert.
	 *
	 * @param string $filename
	 * @return bool
	 */
	public static function existsFile(string $filename):bool {
		self::clearStatcache();

		return file_exists($filename);
	}

	/**
	 * Scannt ein Verzeichnis.
	 *
	 * @param string $dirname
	 * @return array|null
	 */
	public static function scanDir(string $dirname):?array {
		$dirname=self::getDirName($dirname);

		return scandir($dirname);
	}

	/**
	 * Scannt ein Verzeichnis rekursiv und liefert die Liste als Array.
	 *
	 * @param string $dir
	 * @param bool $recursive
	 * @param int $deep
	 * @return array|null
	 */
	public static function scanDirToArray(string $dir, bool $recursive=false, int $deep=0):?array {
		return self::scanDirToArrayCore($dir, $recursive, $deep, 'fd');
	}

	/**
	 * Scannt ein Verzeichnis rekursiv und liefert die Liste der Verzeichnisse als Array.
	 *
	 * @param string $dir
	 * @param bool $recursive
	 * @param int $deep
	 * @return array|null
	 */
	public static function scanDirsToArray(string $dir, bool $recursive=false, int $deep=0):?array {
		return self::scanDirToArrayCore($dir, $recursive, $deep, 'd');
	}

	/**
	 * Scannt ein Verzeichnis rekursiv und liefert die Liste der Dateien als Array.
	 *
	 * @param string $dir
	 * @param bool $recursive
	 * @param int $deep
	 * @return array|null
	 */
	public static function scanFilesToArray(string $dir, bool $recursive=false, int $deep=0):?array {
		return self::scanDirToArrayCore($dir, $recursive, $deep, 'f');
	}

	/**
	 * Kürzt einen Array mit Verzeichnissen/Dateien um den angegebenen Pfad.
	 *
	 * @param string $dir
	 * @param array $list
	 * @return array
	 */
	public static function trimPathInArray(string $dir, array $list):array {
		$len=mb_strlen($dir);
		foreach ($list as $key=>$value) {
			$list[$key]=mb_substr($value, $len, -1);
		}

		return $list;
	}

	/**
	 * Engine zum Scannen von Verzeichnissen.
	 *
	 * @param string $dir
	 * @param bool $recursive
	 * @param int $deep
	 * @param string $mode
	 * @param int $current_level
	 * @param array $result
	 * @return array|null
	 */
	private static function scanDirToArrayCore(string $dir, bool $recursive=false, int $deep=0, string $mode='fd', int $current_level=0, $result=[]):?array {
		$dir=self::getDirName($dir);
		if (self::isDir($dir)!==true) {
			return null;
		}
		$list=self::scanDir($dir);
		if (!empty($list)) {
			foreach ($list as $f) {
				if (($f!='..')&&($f!='.')) {
					if (self::isDir($dir.$f)) {
						$current_level++;
						if (mb_strpos($mode, 'd')!==false) {
							$result[]=$dir.$f.DIRECTORY_SEPARATOR;
						}
						if (($recursive===true)&&(($deep==0)||($deep<$current_level))) {
							$result=self::scanDirToArrayCore($dir.$f.DIRECTORY_SEPARATOR, $recursive, $deep, $mode, $current_level, $result);
						}
					} else {
						if (mb_strpos($mode, 'f')!==false) {
							$result[]=$dir.$f;
						}
					}
				}
			}
		}

		return $result;
	}

	/**
	 * Kopiert eine Datei.
	 *
	 * @param string $source
	 * @param string $dest
	 * @return bool
	 */
	public static function copyFile(string $source, string $dest):bool {
		return copy($source, $dest);
	}

	/**
	 * Löscht den Status Cache.
	 */
	public static function clearStatcache():void {
		clearstatcache();
	}

	/**
	 * Gibt den letzen Aktualisierungszeitpunkt alle Dateien der Liste zurück.
	 *
	 * @param array $files
	 * @param bool $check_configs
	 * @return int
	 */
	public static function getFilesModTime(array $files, bool $check_configs=false):int {
		$filesmtime=0;
		if ($check_configs===true) {
			foreach (Settings::getConfigFiles() as $file) {
				$filesmtime=max(filemtime($file), $filesmtime);
			}
		}
		foreach ($files as $file) {
			if (file_exists($file)) {
				$filesmtime=max(filemtime($file), $filesmtime);
			}
		}

		return $filesmtime;
	}

	/**
	 * Gibt den Aktualisierungszeitpunkt einer Datei zurück.
	 *
	 * @param string $file
	 * @param bool $check_configs
	 * @return int
	 */
	public static function getFileModTime(string $file, bool $check_configs=false):int {
		return self::getFilesModTime([$file], $check_configs);
	}

}

?>