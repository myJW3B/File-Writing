<?php

namespace JW3B\Data;
use JW3B\erday\Helpful_Files;

class FileWriting {
	public $dir_path; //__DIR__.'/../../../../file-writing/';

	/**
	 * __construct() function
	 * @param string $path
	 * 		The directory where the files are saved.
	 */
	public function __construct($path){
		$this->dir_path = $path;
		Helpful_Files::mk_dir_writable($this->dir_path);
	}

	/**
	 * save function
	 *
	 * @param string $path
	 * 		The path from within the __construct($path) directory
	 * @param string|array $data
	 * 		The string to save, or array to save
	 * 		Currently we cannot save the keys to the array,
	 * 		Maybe in a later version we can.
	 * @return bool
	 */
	public function save($path, $data){
		$dirs = explode('/', $path);
		$total = count($dirs);
		$fileName = $dirs[$total - 1];
		if($total > 1){
			for($i=0;$i<$total;$i++){
				if($dirs[$i] != $fileName){
					Helpful_Files::mk_dir_writable($this->dir_path.$dirs[$i]);
				}
			}
		}
		$this->save_file($this->dir_path.$path, $this->file_data($data));
		return true;
	}

	private function save_file($path, $data, $writing='a'){
		$fp = fopen($path.'.dat', $writing);
		flock($fp, 2);
		fwrite($fp, $data);
		flock($fp, 3);
		fclose($fp);
	}
	/**
	 * file_data function
	 *
	 * @param string|array $a
	 * 		used to save the data into the file.
	 * @return string
	 */
	private function file_data($a){
		if(is_array($a)){
			foreach($a as $val){
				$ary[] = urlencode($val);
			}
			$line = implode('|', $ary);
			return $line;
		} else {
			return urlencode($a);
		}
	}

	/**
	 * check function
	 *		Checks to see if the key is found in the data file.
	 *		This is useful when storing an array.
	 * @param string $path
	 * @param string $key
	 * @return bool|array
	 */
	public function check($path, $key=''){
		if(is_file($this->dir_path . $path)){
			$file = file($this->dir_path . $path);
			$rows = explode("\n", $file);
			if($key != ''){
				foreach($rows as $r => $rr){
					$cols = explode('|', trim( $rr ));
					if(trim( $cols[0] ) == $key){
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
	 *
	 * @param string $path
	 * @param string $key
	 * @return bool
	 */
	public function remove($path, $key){
		if(is_file($this->dir_path . $path)){
			$file = file($this->dir_path . $path);
			$rows = explode("\n", $file);
			$put_back_in = '';
			foreach($rows as $rr){
				$cols = explode('|', trim( $rr ));
				if(urldecode(trim( $cols[0] )) != $key){
					$put_back_in = trim($rr)."\n";
				}
			}
			$this->save_file($this->dir_path.$path, $put_back_in, 'w');
			return false;
		} else {
			return false;
		}
	}
}