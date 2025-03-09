<?php

namespace JW3B\Data;
use JW3B\Helpful\Files;

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
		$this->dir_path = str_ends_with(haystack: $dataname, needle: '/') ? $dataname : $dataname.'/';
		$ex = explode(separator: '/', string: $this->dir_path);
		$prev = '';
		foreach($ex as $dir){
			Files::mk_dir_writable($prev . $dir);
			$prev = $dir.'/';
		}
	}

	/**
	 * return
	 *
	 * @param string $path
	 * @return array|bool - include($file); value is stored in return, so use as:
	 * ````
	 * <?php
	 * $stored_array = FileWriting::get_file('something/hidden/config');
	 * ````
	 */
	public function get_file($path): mixed{
		$real_file = $this->check_path(path: $path);
		if(is_file(filename: $real_file)){
			return include($real_file);
		} else {
			return false;
		}
	}

	/**
	 * get_line_file will get the contents of your lined file in an array based on each row.
	 *
	 * @param string $path
	 * @return array|bool
	 */
	public function get_line_file($path): array|bool{
		$real_file = $this->check_path(path: $path);
		if(is_file(filename: $real_file)){
			return file(filename: $real_file);
		} else {
			return false;
		}
	}

	private function check_path($path): string{
		$dirs = explode(separator: '/', string: $path);
		$total = count(value: $dirs);
		$fileName = $dirs[$total - 1];
		if($total > 1){
			$prev = '';
			for($i=0;$i<$total;$i++){
				if($dirs[$i] != $fileName){
					Files::mk_dir_writable($this->dir_path.$prev.$dirs[$i]);
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
	public function save($path, $data, $how_to_update='add'): bool{
		$this->real_file = $this->check_path(path: $path);
		return $this->set_up_file(
			data: is_array(value: $data ) ? $data : [$data],
			how_to_update: strtolower( string: $how_to_update )
		)->save_file(writing: 'w');
		//$this->save_file($this->dir_path.$path, $this->file_data($data));
		//return true;
	}

	private function set_up_file($data, $how_to_update): static{
		if($this->style == 'lines'){
			if(is_file(filename: $this->real_file)){
				$bkup = substr_replace(string: $this->real_file, replace: 'backup.php', offset: -3, length: 3);
				copy(from: $this->real_file, to: $bkup);
				$found = file(filename: $this->real_file);
				$found[] = $this->set_up_line_file(data: $data);
				$this->file_data = implode(separator: PHP_EOL, array: $found);
			} else {
				$this->file_data = '<?php exit;'.PHP_EOL.$this->set_up_line_file(data: $data).PHP_EOL;
			}
		} else if($this->style == 'return'){
			if(is_file(filename: $this->real_file)){
				$bkup = substr_replace(string: $this->real_file, replace: 'backup.php', offset: -3, length: 3);
				copy(from: $this->real_file, to: $bkup);
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
					.$this->setup_return_ary(data: $data,tabs: 1).PHP_EOL
				.'];';
		} else {
			throw new \ErrorException(message: 'FileWriting style "'.$this->style.'" is not a valid acceptable style type. Currently only "lines" and "return" are the allowed values', code: 10, severity: E_ERROR);
		}
		return $this;
	}

	private function setup_return_ary($data, $tabs=1): string{
		$file = '';
		$t = $this->tabs(tabs: $tabs);
		if(is_array(value: $data)){
			// remove the last ,
			$total = count(value: $data);
			$c = 0;
			foreach($data as $k => $v){
				$c++;
				$add = $c < $total ? ',' : '';
				$file .= $t.'\''.$k.'\' => '.$this->check_value_for_return(str: $v,tabs: $tabs).$add;
			}
		} else {
			$file .= $file .= $t.'\''.str_replace(search: "'", replace: "\'", subject: $data).'\'';
		}
		return $file;
	}

	private function check_value_for_return($str,$tabs): string{
		if(is_array($str)){
			return '['.PHP_EOL
			.$this->setup_return_ary(data: $str,tabs: $tabs+1)
			.$this->tabs(tabs: $tabs).']';
		} else {
			return '\''.str_replace(search: "'", replace: "\'", subject: $str).'\'';
		}
	}

	private function tabs($tabs): string{
		$file = '';
		for($i=0;$i<$tabs;$i++){
			$file .= $this->tabs_or_space;
		}
		return $file;
	}

	private function save_file($writing='a'): bool{
		$fp = fopen(filename: $this->real_file, mode: $writing);
		//echo $this->real_file;
		flock(stream: $fp, operation: 2);
		fwrite(stream: $fp, data: $this->file_data);
		flock(stream: $fp, operation: 3);
		fclose(stream: $fp);
		return true;
	}

	private function set_up_line_file($data): string{
		if(is_array(value: $data)){
			if(array_is_list(array: $data)){
				foreach($data as $v){
					$row[] = urlencode(string: $v);
				}
			} else {
				foreach($data as $k => $v){
					$row[] = urlencode(string: $k).'{=}'.urlencode(string: $v);
				}
			}
			return implode(separator: '|', array: $row);
		} else {
			return urlencode(string: $data);
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
	public function find($path, $key=''): array|bool{
		if(is_file(filename: $this->dir_path . $path)){
			$file = file(filename: $this->dir_path . $path);
			$rows = explode(separator: "\n", string: $file);
			if($key != ''){
				foreach($rows as $rr){
					$cols = explode(separator: '|', string: trim( string: $rr ));
					if(urldecode(string: trim( string: $cols[0] )) == $key){
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
	 * remove lined file function
	 *
	 * @param string $path
	 * @param string $key
	 * @return bool
	 */
	public function remove($path, $key): bool{
		$found = false;
		if(is_file(filename: $this->dir_path . $path)){
			$this->real_file = $this->dir_path . $path;
			$file = file(filename: $this->dir_path . $path);
			$rows = explode(separator: "\n", string: $file);
			$put_back_in = '';
			foreach($rows as $rr){
				$cols = explode(separator: '|', string: trim( string: $rr ));
				if(urldecode(string: trim( string: $cols[0] )) != $key){
					$put_back_in = trim(string: $rr)."\n";
				} else {
					$found = true;
				}
			}
			$this->file_data = $put_back_in;
			$this->save_file(writing: 'w');
		}
		return $found;
	}
}