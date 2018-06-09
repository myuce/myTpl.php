<?php
/********************************************************************
*	myTpl - php template engine (https://github.com/myuce/myTpl)	*
*	author: Mehmet Yüce (https://github.com/myuce)					*
********************************************************************/
namespace myuce;
class myTpl {
	function __construct($tplDir,$cacheDir=false) {
		if(empty($tplDir)) die('the template directory has not been set');
		$this->tplDir = $tplDir;
		if(!empty($cacheDir)) {
			$this->cache = true;
			$this->cacheDir = $cacheDir;
		}
		$this->tplVars = [];
	}

	public function set($name,$value) {
		$result = &$this->tplVars;
		$names = explode('.',$name);
		foreach($names as $name) {
			$result = &$result[$name];
		}
		$result = $value;
	}

	private function get($var,$source=null) {
		$source = isset($source) ? $source : $this->tplVars;
		$tokens = preg_split('/(\.|\:)/',$var,-1,PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);
		
		if(count($tokens) == 1) {
			return $source[$var];
		}

		$result = $source;
		for($i = 0; $i < count($tokens); ++$i) {
			if(isset($tokens[$i-1]) && $tokens[$i-1] == ':') { // if it's an object
				$result = $result->{$tokens[$i]};
			} else { // if it's an array
				$result = $result[$tokens[$i]];
			}
			++$i;
		}
		return $result;
	}

	private function compile($tplString) {
		$result = str_replace('<?','<?php echo \'<?\'; ?>',$tplString); // for xml files
		$result = "<?php if(!class_exists('myuce\\myTpl')) { die('access forbidden'); } ?>".$result;

		// template variables
		$i[] = '#{\%(.*?)}#si';
		$o[] = '<?php echo $this->get(\'$1\'); ?>';

		// loop associative arrays
		$i[] = '#<!-- loop (.*?) as (.*?) -->#si';
		$o[] = '<?php if(!empty($this->get(\'$1\'))) { '
			.'foreach($this->get(\'$1\') as $array_temp_$2) { '
			.'$this->set(\'$2\',$array_temp_$2); ?>';
		$i[] = '#<!-- empty -->#';
		$o[] = '<?php }} else {{ ?>';
		$i[] = '#<!-- endloop -->#si';
		$o[] = '<?php }} ?>';

		// conditions
		$result = preg_replace_callback('#<!-- (if|elseif) (.*?) -->#',
			function($condition) {
				$condition[1] = $condition[1] == 'elseif' ? '} elseif': 'if';
				$condition[2] = preg_replace('#\$([\S]+)#si','$this->get(\'$1\',$GLOBALS)',$condition[2]);
				$condition[2] = preg_replace('#\%([\S]+)#si','$this->get(\'$1\',$this->tplVars)',$condition[2]);
				return "<?php {$condition[1]}($condition[2]) { ?>";
			},
			$result);


		$i[] = '#<!-- else -->#si';
		$o[] = '<?php } else { ?>';

		$i[] = '#<!-- endif -->#si';
		$o[] = '<?php } ?>';

		// shorthand ifs. only cheks if a variable is true
		$result = preg_replace_callback('#<!-- begin (.*?) -->#',
			function($condition) {
				$condition = preg_replace('#\$([\S]+)#si','$this->get(\'$1\',$GLOBALS)',$condition[1]);
				$condition = preg_replace('#\%([\S]+)#si','$this->get(\'$1\',$this->tplVars)',$condition);
				return "<?php if(!empty($condition) && $condition == true) { ?>";
			},
			$result);

		$i[] = '#<!-- end -->#si';
		$o[] = '<?php } ?>';

		// load another template
		$i[] = '#<!-- (include|require) (.*?) -->#si';
		$o[] = '<?php $this->load(\'$2\'); ?>';

		$result = preg_replace($i,$o,$result);
		return $result;
	}
	
	public function load($tplFile) {
		$tplPath = "{$this->tplDir}/$tplFile.html";
		if($this->cache) {
			$cacheFile = $this->cacheDir.'/'.str_replace('/','|',$tplFile).'!'.filemtime($tplPath).'.php';
			if(!file_exists($cacheFile)) {
				array_map('unlink',glob("$this->cacheDir/".str_replace('/','|',basename($tplFile)).'*.php'));
				$compiled = $this->compile(file_get_contents($tplPath));
				file_put_contents($cacheFile,$compiled);
			}
			include($cacheFile);
		} else {
			$tpl = file_get_contents($tplPath);
			$tpl = $this->compile($tpl);
			eval(" ?>$tpl<?php ");
		}
	}
}
