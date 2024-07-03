<?php

namespace JW3B\Data;
use JW3B\erday\Helpful_Files;

class FileWriting {
	public $dir_path;
	public $style;
	private $tabs_or_space;
	public $file_data;
	public $real_file;

	/**
	 * __construct() function
	 *
	 * @param string $dataname
	 * 		The directory where the files are saved. Could be called a database table in you think like that.
	 * @param string $style
	 * <?php
	 * 		'return' = create a file with <?php return [];
	 * 		'lines' = creare a php file with data on separate lines
	 * @param string $tabs_or_space
	 * 	the indentation, put spaces if you'd like space, or leave alone for tabs.
	 *
	 *	For the style::
	 * 'return' = create a file with <?php return [];
	 *  You can then call
	 * ````php
	 * <?php
	 * $FileWriting = new FileWriting('cache/file-data');
	 * $FileWriting->save('name/to-reference/later',['works' => ['with' => 'arrays']]);
	 * $var = $FileWriting->get_file('name/to-reference/later');
	 * echo $var['works']['with'];
	 * ````
	 *  and you can use $var['key'] to find the values later
	 * ````
	 *  'lines' = creare a php file, where the value of the file would be:
	 * ````
	 *  <?php exit;
	 *  old line data
	 *  more older lines
	 *  another data
	 * ````
	 */
	public function __construct($dataname, $style='return', $tabs_or_space='	'){
		$this->style = $style;
		$this->tabs_or_space = $tabs_or_space;
		$this->dir_path = str_ends_with($dataname, '/') ? $dataname : $dataname.'/';
		$ex = explode('/', $this->dir_path);
		$prev = '';
		foreach($ex as $dir){
			Helpful_Files::mk_dir_writable($prev . $dir);
			$prev = $dir.'/';
		}
	}

	/**
	 * return
	 *
	 * @param string $path
	 * @return array - include($file); value is stored in return, so use as:
	 * ````
	 * <?php
	 * $stored_array = FileWriting::get_file('something/hidden/config');
	 * ````
	 */
	public function get_file($path){
		$real_file = $this->check_path($path);
		return include($real_file);
	}

	private function check_path($path){
		$dirs = explode('/', $path);
		$total = count($dirs);
		$fileName = $dirs[$total - 1];
		if($total > 1){
			$prev = '';
			for($i=0;$i<$total;$i++){
				if($dirs[$i] != $fileName){
					Helpful_Files::mk_dir_writable($this->dir_path.$prev.$dirs[$i]);
					$prev .= $dirs[$i].'/';
				}
			}
		}
		return $this->dir_path.$path.'.php';
	}
	/**
	 * save function
	 *
	 * @param string $path
	 * 		The path from within the __construct($path) directory
	 * @param string|array $data
	 * 		The string to save, or array to save
	 * @param string $how_to_update
	 * 		'add'(default) 'replace' or 'update' what is currently in the file
	 * ````<?php
	 * 		$File->save($path, $data, 'add'); 		// will add your new array to exsisting array
	 * 		$File->save($path, $data, 'replace'); // replace whole file with the new data
	 * 		$File->save($path, $data, 'update');  // will update the key values, or add new ones
	 * ````
	 * @return bool
	 */
	public function save($path, $data, $how_to_update='add'){
		$this->real_file = $this->check_path($path);
		return $this->set_up_file($data, strtolower( $how_to_update ))->save_file('w');
		//$this->save_file($this->dir_path.$path, $this->file_data($data));
		//return true;
	}

	private function set_up_file($data, $how_to_update){
		if($this->style == 'lines'){
			if(is_file($this->real_file)){
				$bkup = substr_replace($this->real_file, 'backup.php', -3, 3);
				copy($this->real_file, $bkup);
				$found = file($this->real_file);
				$found[] = $this->set_up_line_file($data);
				$this->file_data = implode(PHP_EOL, $found);
			} else {
				$this->file_data = '<?php exit;'.PHP_EOL.$this->set_up_line_file($data).PHP_EOL;
			}
		} else if($this->style == 'return'){
			if(is_file($this->real_file)){
				$bkup = substr_replace($this->real_file, 'backup.php', -3, 3);
				copy($this->real_file, $bkup);
				$found = include($this->real_file);
				if($how_to_update == 'replace'){
					// were replacing the whole file with this new data
				} else if($how_to_update == 'add') {
					$data = array_merge_recursive( $found, $data );
				} else if($how_to_update == 'update'){
					$data = array_merge( $found, $data );
				}
			}
			$this->file_data = "<?php".PHP_EOL
				."return [".PHP_EOL
					.$this->setup_return_ary($data,1).PHP_EOL
				.'];';
		} else {
			throw new \ErrorException('FileWriting style "'.$this->style.'" is not a valid acceptable style type. Currently only "lines" and "return" are the allowed values', 10, E_ERROR);
		}
		return $this;
	}

	private function setup_return_ary($data, $tabs=1){
		$file = '';
		$t = $this->tabs($tabs);
		if(is_array($data)){
			// remove the last ,
			$total = count($data);
			$c = 0;
			foreach($data as $k => $v){
				$c++;
				$add = $c < $total ? ',' : '';
				$file .= $t.'\''.$k.'\' => '.$this->check_value_for_return($v,$tabs).$add;
			}
		} else {
			$file .= $file .= $t.'\''.str_replace("'", "\'", $data).'\'';
		}
		return $file;
	}

	private function check_value_for_return($str,$tabs){
		if(is_array($str)){
			return '['.PHP_EOL
			.$this->setup_return_ary($str,$tabs+1)
			.$this->tabs($tabs).']';
		} else {
			return '\''.str_replace("'", "\'", $str).'\'';
		}
	}

	private function tabs($tabs){
		$file = '';
		for($i=0;$i<$tabs;$i++){
			$file .= $this->tabs_or_space;
		}
		return $file;
	}

	private function save_file($writing='a'){
		$fp = fopen($this->real_file, $writing);
		//echo $this->real_file;
		flock($fp, 2);
		fwrite($fp, $this->file_data);
		flock($fp, 3);
		fclose($fp);
		return true;
	}

	private function set_up_line_file($data){
		if(is_array($data)){
			if(array_is_list($data)){
				foreach($data as $v){
					$row[] = urlencode($v);
				}
			} else {
				foreach($data as $k => $v){
					$row[] = urlencode($k).'{=}'.urlencode($v);
				}
			}
			return implode('|', $row);
		} else {
			return urlencode($data);
		}
	}

	/**
	 * find function
	 *		````
	 *		Finds the key in a 'lines' style data file, or returns false.
	 *    ````
	 * @param string $path
	 * @param string $key
	 * @return bool|array
	 */
	public function find($path, $key=''){
		if(is_file($this->dir_path . $path)){
			$file = file($this->dir_path . $path);
			$rows = explode("\n", $file);
			if($key != ''){
				foreach($rows as $rr){
					$cols = explode('|', trim( $rr ));
					if(urldecode(trim( $cols[0] )) == $key){
						return $cols;
					}
				}
			}
			return false;
		} else {
			return false;
		}
	}

	/**
	 * remove function
	 *	Not setup
	 * @param string $path
	 * @param string $key
	 * @return bool
	 */
	public function remove($path, $key){
		$found = false;
		if(is_file($this->dir_path . $path)){
			$this->real_file = $this->dir_path . $path;
			$file = file($this->dir_path . $path);
			$rows = explode("\n", $file);
			$put_back_in = '';
			foreach($rows as $rr){
				$cols = explode('|', trim( $rr ));
				if(urldecode(trim( $cols[0] )) != $key){
					$put_back_in = trim($rr)."\n";
				} else {
					$found = true;
				}
			}
			$this->file_data = $put_back_in;
			$this->save_file('w');
		}
		return $found;
	}
}