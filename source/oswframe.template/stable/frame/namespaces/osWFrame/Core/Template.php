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

class Template {

	use BaseStaticTrait;
	use BaseTemplateTrait;

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
	 * @var array
	 */
	private array $textarea_matches=[];

	/**
	 * @var int
	 */
	private int $textarea_counter=0;

	/**
	 * @var array
	 */
	private array $vars=[];

	/**
	 * @var array
	 */
	private array $conf=[];

	/**
	 * @var array
	 */
	private array $loader=[];

	/**
	 * @var array
	 */
	private array $tags=[];

	/**
	 * @var array
	 */
	private array $codes=[];

	/**
	 * @var array
	 */
	private array $forms=[];

	/**
	 * Template constructor.
	 */
	public function __construct() {
		$this->setConf('gzipcompression', Settings::getBoolVar('template_gzipcompression'));
		$this->setConf('gzipcompression_level', Settings::getIntVar('template_gzipcompression_level'));
		$this->setConf('stripoutput', Settings::getBoolVar('template_stripoutput'));
	}

	public function getOutput(string $file='content', string $module='project', string $dir='modules') {
		$content=$this->fetchFileIfExists($file, $module, $dir);
		if (($this->getConf('gzipcompression')===true)&&(!headers_sent())&&(!connection_aborted())&&(ob_get_length()==0)) {
			ini_set('zlib.output_compression_level', $this->getConf('gzipcompression_level'));
			ob_start('ob_gzhandler');
		}

		// strip content
		if ($this->getConf('stripoutput')===true) {
			$content=$this->strip($content);
		}
		// # highlight words in body
		// osW_Template::getInstance()->setHighlightColors(array('#FFFF66', '#A0FFFF', '#99FF99', '#FF9999', '#FF66FF', '#880000', '#00AA00', '#886800'));
		// if ((osW_Session::getInstance()->isSpider()!==true)&&(osW_vOut('frame_highlight_words'))) {
		// $contents=osW_Template::getInstance()->highlightWords($contents, $words);
		// }
		return $content;
	}

	/**
	 * @param array $matches
	 * @return string
	 */
	private function callback_marktextarea(array $matches):string {
		$this->textarea_matches[]=$matches[0];

		return '<<<OSW_STRIP_REPLACE_TEXTAREA_MARKER_'.$this->textarea_counter++.'>>>';
	}

	/**
	 * @param string $c
	 * @return string
	 */
	public function strip(string $c):string {
		if (Settings::getBoolVar('template_textarea_used')===true) {
			$c=preg_replace_callback('/<textarea [^>]*>.*<\/textarea>/Uis', [$this, 'callback_marktextarea'], $c);
		}
		$c=HTML::stripContent($c);
		if (Settings::getBoolVar('template_textarea_used')===true) {
			foreach (array_keys($this->textarea_matches) as $key) {
				$c=preg_replace('/<<<OSW_STRIP_REPLACE_TEXTAREA_MARKER_'.$key.'>>>/', $this->textarea_matches[$key], $c);
			}
		}

		return $c;
	}

	public function setVar(string $name, &$value, $ref=true) {
		if ($ref===true) {
			return $this->setVarAsRef($name, $value);
		}

		return $this->setVarAsCopy($name, $value);
	}

	public function setVarAsRef(string $name, &$value) {
		$this->vars[$name]=&$value;

		return true;
	}

	public function setVarAsCopy(string $name, $value) {
		$this->vars[$name]=$value;

		return true;
	}

	public function getVar(string $name) {
		if ((strlen($name)>0)&&(isset($this->vars[$name]))) {
			return $this->vars[$name];
		}

		return null;
	}

	public function setConf(string $name, $value) {
		$this->conf[$name]=$value;

		return true;
	}

	public function getConf(string $name) {
		if ((strlen($name)>0)&&(isset($this->conf[$name]))) {
			return $this->conf[$name];
		}

		return null;
	}

	private function getModuleByShort($module='project') {
		if ($module=='project') {
			return Settings::getStringVar('project_default_module');
		} elseif ($module=='default') {
			return Settings::getStringVar('frame_default_module');
		} elseif ($module=='current') {
			return Settings::getStringVar('frame_current_module');
		} else {
			return $module;
		}
	}

	public function setVarFromFile(string $name, string $file='content', string $module='project', string $dir='modules') {
		$module=$this->getModuleByShort($module);

		return $this->setVarAsCopy($name, $this->fetchFileIfExists($file, $module, $dir));
	}

	public function isfetchFile($file='content', $module='project', $dir='modules') {
		$module=$this->getModuleByShort($module);
		if (file_exists(Settings::getStringVar('settings_abspath').$dir.'/'.$module.'/tpl/'.$file.'.tpl.php')===true) {
			return true;
		}

		return false;
	}

	public function fetchFileIfExists($file='content', $module='project', $dir='modules') {
		$module=$this->getModuleByShort($module);
		if ($this->isfetchFile($file, $module, $dir)===true) {
			return $this->fetchFile($file, $module, $dir);
		}

		return '';
	}

	public function fetchFile($file='content', $module='project', $dir='modules') {
		$module=$this->getModuleByShort($module);

		return $this->fetch(Settings::getStringVar('settings_abspath').$dir.'/'.$module.'/tpl/'.$file.'.tpl.php');
	}

	/**
	 *
	 * @param string $file
	 * @return string
	 */
	public function fetch(string $file):string {
		extract($this->vars);
		ob_start();
		require $file;
		$contents=ob_get_contents();
		ob_end_clean();

		return $contents;
	}

	/**
	 *
	 * @param string $module
	 * @param string $parameters
	 * @param bool $replace_amp
	 * @return string
	 */
	public function buildhrefLink(string $module='', string $parameters='', bool $replace_amp=true):string {
		if (($module=='')||($module=='default')) {
			$module=Settings::getStringVar('project_default_module');
		}
		if ($module=='current') {
			$module=Settings::getStringVar('frame_current_module');
		}
		if ($replace_amp===true) {
			return str_replace('&', '&amp;', Navigation::buildUrl($module, $parameters));
		} else {
			return Navigation::buildUrl($module, $parameters);
		}
	}

	/**
	 * Fügt einen Tag hinzu.
	 * (<script src="" ...></script>)
	 *
	 * @param string $tag
	 * @param array $attributes
	 * @param string $pos
	 * @param bool $void
	 * @return bool
	 */
	public function addTag(string $tag, array $attributes, string $pos='head', bool $void=false):bool {
		if (!isset($this->tags[$pos])) {
			$this->tags[$pos]=[];
		}
		$this->tags[$pos][md5(serialize(['tag'=>$tag, 'attributes'=>$attributes, 'void'=>$void, 'string'=>false]))]=['tag'=>$tag, 'attributes'=>$attributes, 'void'=>$void, 'string'=>false];

		return true;
	}

	/**
	 * Fügt eine Void-Tag hinzu.
	 * (<img ... />)
	 *
	 * @param string $tag
	 * @param array $attributes
	 * @param string $pos
	 * @return bool
	 */
	public function addVoidTag(string $tag, array $attributes, string $pos='head'):bool {
		return $this->addTag($tag, $attributes, $pos, true);
	}

	/**
	 * Fügt einen String-Tag hinzu.
	 * <title>...</title>
	 *
	 * @param string $tag
	 * @param string $attributes
	 * @param string $pos
	 * @return bool
	 */
	public function addStringTag(string $tag, string $attributes, string $pos='head'):bool {
		if (!isset($this->tags[$pos])) {
			$this->tags[$pos]=[];
		}
		$this->tags[$pos][md5(serialize(['tag'=>$tag, 'attributes'=>$attributes, 'void'=>false, 'string'=>true]))]=['tag'=>$tag, 'attributes'=>$attributes, 'void'=>false, 'string'=>true];

		return true;
	}

	/**
	 * Gibt die Tags aus.
	 *
	 * @param string $pos
	 * @return string
	 */
	private function outputTags(string $pos):string {
		if (!isset($this->tags[$pos])) {
			return '';
		}
		$content='';
		foreach ($this->tags[$pos] as $tag) {
			$content.=$this->buildTag($tag)."\n";
		}

		return $content;
	}

	/**
	 * Baut einen Tag zusammen.
	 *
	 * @param array $tag
	 * @return string
	 */
	private function buildTag(array $tag):string {
		if ($tag['string']===true) {
			return '<'.$tag['tag'].'>'.$tag['attributes'].'</'.$tag['tag'].'>';
		} else {
			if ($tag['void']===true) {
				return '<'.$tag['tag'].' '.$this->buildAttributes($tag['attributes']).' />';
			} else {
				return '<'.$tag['tag'].' '.$this->buildAttributes($tag['attributes']).'>'.'</'.$tag['tag'].'>';
			}
		}

		return '';
	}

	/**
	 *
	 * @param string $tag
	 * @param array $attributes
	 * @param string $contnt
	 * @param string $pos
	 * @return bool
	 */
	public function addCode(string $tag, array $attributes, string $content, string $pos='head'):bool {
		if (!isset($this->codes[$pos])) {
			$this->codes[$pos]=[];
		}
		$this->codes[$pos][md5(serialize(['tag'=>$tag, 'content'=>$content, 'attributes'=>$attributes]))]=['tag'=>$tag, 'content'=>$content, 'attributes'=>$attributes];

		return true;
	}

	/**
	 *
	 * @param string $pos
	 * @return string
	 */
	private function outputCodes(string $pos):string {
		if (!isset($this->codes[$pos])) {
			return '';
		}
		$content='';
		foreach ($this->codes[$pos] as $code) {
			$content.='<'.$code['tag'].' '.$this->buildAttributes($code['attributes']).'>'.$code['content'].'</'.$code['tag'].'>'."\n";
		}

		return $content;
	}

	/**
	 *
	 * @param array $attributes
	 * @return string
	 */
	private function buildAttributes(array $attributes):string {
		$result=[];
		foreach ($attributes as $key=>$value) {
			$result[]=$key.'="'.$value.'"';
		}

		return implode(' ', $result);
	}

	/**
	 *
	 * @return string
	 */
	public function getHead():string {
		$this->getJSFiles('head');
		$this->getCSSFiles('head');
		$this->getJSCodes('head');
		$this->getCSSCodes('head');
		$content='';
		$content.=$this->outputTags('head');
		$content.=$this->outputCodes('head');

		return $content;
	}

	/**
	 *
	 * @return string
	 */
	public function getBody():string {
		$this->getJSFiles('body');
		$this->getCSSFiles('body');
		$this->getJSCodes('body');
		$this->getCSSCodes('body');
		$content='';
		$content.=$this->outputTags('body');
		$content.=$this->outputCodes('body');

		return $content;
	}

	/**
	 *
	 * @return string
	 */
	public function getFooter():string {
		$this->getJSFiles('footer');
		$this->getCSSFiles('footer');
		$this->getJSCodes('footer');
		$this->getCSSCodes('footer');
		$content='';
		$content.=$this->outputTags('footer');
		$content.=$this->outputCodes('footer');

		return $content;
	}

	/**
	 *
	 * @param string $pos
	 * @param string $type
	 * @return array
	 */
	private function getTemplateFiles(string $pos='', string $type=''):array {
		if ((isset($this->template_files[$pos]))&&(isset($this->template_files[$pos][$type]))) {
			return $this->template_files[$pos][$type];
		}

		return [];
	}

	/**
	 *
	 * @param string $pos
	 * @param string $type
	 * @return array
	 */
	private function getTemplateCodes(string $pos='', string $type=''):array {
		if ((isset($this->template_codes[$pos]))&&(isset($this->template_codes[$pos][$type]))) {
			return $this->template_codes[$pos][$type];
		}

		return [];
	}

	/**
	 *
	 * @return bool
	 */
	private function getJSFiles(string $pos):bool {
		if (Settings::getBoolVar('smartoptimizer_combine_files')===true) {
			$str=implode(',', $this->getTemplateFiles($pos, 'js'));
			$file=md5($str).'.js';
			SmartOptimizer::writeCacheFile($file, $str);
			if (Settings::getStringVar('template_versionnumber')=='') {
				$this->addTag('script', ['src'=>'static/'.Settings::getStringVar('settings_scriptoptimizer').'/'.$file]);
			} elseif (Settings::getStringVar('template_versionnumber')=='cachetime') {
				$this->addTag('script', ['src'=>'static/'.Settings::getStringVar('settings_scriptoptimizer').'/'.$file.'?v='.Filesystem::getFileModTime(Cache::getDirName('smartoptimizer').$file, false)]);
			} else {
				$this->addTag('script', ['src'=>'static/'.Settings::getStringVar('settings_scriptoptimizer').'/'.$file.'?v='.Settings::getStringVar('template_versionnumber')]);
			}
		} else {
			if ($this->getTemplateFiles($pos, 'js')!=[]) {
				foreach ($this->getTemplateFiles($pos, 'js') as $file) {
					if (strstr($file, '?')) {
						$c='&';
					} else {
						$c='?';
					}
					if (Settings::getBoolVar('smartoptimizer_stripoutput')===true) {
						if (Settings::getStringVar('template_versionnumber')=='') {
							$this->addTag('script', ['src'=>'static/'.Settings::getStringVar('settings_scriptoptimizer').'/'.$file]);
						} elseif (Settings::getStringVar('template_versionnumber')=='cachetime') {
							$this->addTag('script', ['src'=>'static/'.Settings::getStringVar('settings_scriptoptimizer').'/'.$file.$c.'v='.Filesystem::getFileModTime(Settings::getStringVar('settings_abspath').$file, false)]);
						} else {
							$this->addTag('script', ['src'=>'static/'.Settings::getStringVar('settings_scriptoptimizer').'/'.$file.$c.'v='.Settings::getStringVar('template_versionnumber')]);
						}
					} else {
						if (Settings::getStringVar('template_versionnumber')=='') {
							$this->addTag('script', ['src'=>$file]);
						} elseif (Settings::getStringVar('template_versionnumber')=='cachetime') {
							$this->addTag('script', ['src'=>$file.$c.'v='.Filesystem::getFileModTime(Settings::getStringVar('settings_abspath').$file, false)]);
						} else {
							$this->addTag('script', ['src'=>$file.$c.'v='.Settings::getStringVar('template_versionnumber')]);
						}
					}
				}
			}
		}

		return true;
	}

	/**
	 *
	 * @return bool
	 */
	private function getCSSFiles(string $pos) {
		if (Settings::getBoolVar('smartoptimizer_combine_files')===true) {
			$str=implode(',', $this->getTemplateFiles($pos, 'css'));
			$file=md5($str).'.css';
			SmartOptimizer::writeCacheFile($file, $str);
			if (Settings::getStringVar('template_versionnumber')=='') {
				$this->addVoidTag('link', ['rel'=>'stylesheet', 'type'=>'text/css', 'href'=>'static/'.Settings::getStringVar('settings_styleoptimizer').'/'.$file]);
			} elseif (Settings::getStringVar('template_versionnumber')=='cachetime') {
				$this->addVoidTag('link', ['rel'=>'stylesheet', 'type'=>'text/css', 'href'=>'static/'.Settings::getStringVar('settings_styleoptimizer').'/'.$file.'?v='.Filesystem::getFileModTime(Cache::getDirName('smartoptimizer').$file, false)]);
			} else {
				$this->addVoidTag('link', ['rel'=>'stylesheet', 'type'=>'text/css', 'href'=>'static/'.Settings::getStringVar('settings_styleoptimizer').'/'.$file.'?v='.Settings::getStringVar('template_versionnumber')]);
			}
		} else {
			if ($this->getTemplateFiles($pos, 'css')!=[]) {
				foreach ($this->getTemplateFiles($pos, 'css') as $file) {
					if (strstr($file, '?')) {
						$c='&';
					} else {
						$c='?';
					}
					if (Settings::getBoolVar('smartoptimizer_stripoutput')===true) {
						if (Settings::getStringVar('template_versionnumber')=='') {
							$this->addVoidTag('link', ['rel'=>'stylesheet', 'type'=>'text/css', 'href'=>'static/'.Settings::getStringVar('settings_styleoptimizer').'/'.$file]);
						} elseif (Settings::getStringVar('template_versionnumber')=='cachetime') {
							$this->addVoidTag('link', ['rel'=>'stylesheet', 'type'=>'text/css', 'href'=>'static/'.Settings::getStringVar('settings_styleoptimizer').'/'.$file.$c.'v='.Filesystem::getFileModTime(Settings::getStringVar('settings_abspath').$file, false)]);
						} else {
							$this->addVoidTag('link', ['rel'=>'stylesheet', 'type'=>'text/css', 'href'=>'static/'.Settings::getStringVar('settings_styleoptimizer').'/'.$file.$c.'v='.Settings::getStringVar('template_versionnumber')]);
						}
					} else {
						if (Settings::getStringVar('template_versionnumber')=='') {
							$this->addVoidTag('link', ['rel'=>'stylesheet', 'type'=>'text/css', 'href'=>$file]);
						} elseif (Settings::getStringVar('template_versionnumber')=='cachetime') {
							$this->addVoidTag('link', ['rel'=>'stylesheet', 'type'=>'text/css', 'href'=>$file.$c.'v='.Filesystem::getFileModTime(Settings::getStringVar('settings_abspath').$file, false)]);
						} else {
							$this->addVoidTag('link', ['rel'=>'stylesheet', 'type'=>'text/css', 'href'=>$file.$c.'v='.Settings::getStringVar('template_versionnumber')]);
						}
					}
				}
			}
		}

		return true;
	}

	/**
	 *
	 * @return bool
	 */
	private function getJSCodes(string $pos):bool {
		$codes=[];
		if ($this->getTemplateCodes($pos, 'js')!=[]) {
			foreach ($this->getTemplateCodes($pos, 'js') as $code) {
				$codes[]=$code;
			}
		}
		if ($codes!=[]) {
			if (Settings::getBoolVar('smartoptimizer_stripoutput')===true) {
				$this->addCode('script', ['type'=>'text/javascript'], "\n".implode("\n\n", $codes)."\n");
			} else {
				$this->addCode('script', ['type'=>'text/javascript'], "\n".implode("\n\n", $codes)."\n");
			}
		}

		return true;
	}

	/**
	 *
	 * @return bool
	 */
	private function getCSSCodes(string $pos):bool {
		$codes=[];
		if ($this->getTemplateCodes($pos, 'css')!=[]) {
			foreach ($this->getTemplateCodes($pos, 'css') as $code) {
				$codes[]=$code;
			}
		}
		if ($codes!=[]) {
			if (Settings::getBoolVar('smartoptimizer_stripoutput')===true) {
				$this->addCode('style', ['type'=>'text/css', 'title'=>'text/css'], "\n".implode("\n\n", $codes)."\n");
			} else {
				$this->addCode('style', ['type'=>'text/css', 'title'=>'text/css'], "\n".implode("\n\n", $codes)."\n");
			}
		}

		return true;
	}

	/**
	 *
	 * @param string $alias
	 * @param string $namespace
	 * @return bool
	 */
	public function setForm(string $alias='default', string $namespace=''):bool {
		if ($namespace=='') {
			$namespace='osWFrame\Core\Form';
		}
		$this->forms[$alias]=new $namespace();

		return true;
	}

	/**
	 *
	 * @param string $alias
	 * @param object $object
	 * @return bool
	 */
	public function addForm(string $alias='default', object $object):bool {
		$this->forms[$alias]=$object;

		return true;
	}

	/**
	 *
	 * @param string $alias
	 * @return object
	 */
	public function Form(string $alias='default'):object {
		if (!isset($this->forms[$alias])) {
			$this->setForm($alias);
		}

		return $this->forms[$alias];
	}

	/**
	 * @param string $filename
	 * @param array $options
	 * @return string
	 */
	public function getOptimizedImage(string $filename, array $options=[]):string {
		if (!isset($options['module'])) {
			$options['module']='';
		}
		if (!isset($options['path'])) {
			if (($options['module']=='')||($options['module']=='default')) {
				$options['module']=Settings::getStringVar('project_default_module');
			}
			if ($options['module']=='current') {
				$options['module']=Settings::getStringVar('frame_current_module');
			}

			$options['path']='modules'.DIRECTORY_SEPARATOR.$options['module'].DIRECTORY_SEPARATOR.'img'.DIRECTORY_SEPARATOR;
		}

		if (isset($options['subdir'])) {
			$options['path'].=$options['subdir'].DIRECTORY_SEPARATOR;
		}

		$rel_file=$options['path'].$filename;
		$abs_file=\osWFrame\Core\Settings::getStringVar('settings_abspath').$rel_file;
		if (!file_exists($abs_file)) {
			MessageStack::addMessage(self::getNameAsString(), 'error', ['time'=>time(), 'line'=>__LINE__, 'function'=>__FUNCTION__, 'error'=>'File not found ('.$rel_file.')']);

			return '';
		}

		$osW_ImageOptimizer=new ImageOptimizer();
		$osW_ImageOptimizer->setOptionsByArray($options);

		$path_filename=pathinfo($abs_file, PATHINFO_FILENAME);
		$path_extension=pathinfo($abs_file, PATHINFO_EXTENSION);

		if (!isset($options['alt'])) {
			$options['alt']=$path_filename;
		}

		if (!isset($options['title'])) {
			$options['title']='';
		}

		if (!isset($options['parameter'])) {
			$options['parameter']='';
		}

		if (\osWFrame\Core\Settings::getBoolVar('imageoptimizer_protect_files')===true) {
			$osW_ImageOptimizer->setPS($rel_file);
		}

		$new_filename=$path_filename.'.'.$osW_ImageOptimizer->getOptionsAsString().'.'.$path_extension;

		$out='';
		$out.='<img '.$options['parameter'].' src="static/'.Settings::getStringVar('settings_imageoptimizer').'/'.$options['path'].$new_filename;
		/* ToDo: height/width ermitteln und angeben */
		$out.='" alt="'.HTML::outputString($options['alt']).'" title="'.HTML::outputString($options['title']).'" />';

		return $out;
	}

}

?>