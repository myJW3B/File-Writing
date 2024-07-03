<?php

namespace JW3B\Data;
use JW3B\erday\Helpful_Files;

class FileWriting {
	public static $dir_path = __DIR__.'/../../cache/';

	public static function save($path, $data){
		$dirs = explode('/', $path);
		$total = count($dirs);
		$fileName = $dirs[$total - 1];
		if($total > 1){
			for($i=0;$i<$total;$i++){
				if($dirs[$i] != $fileName){
					Helpful_Files::mk_dir_writable(self::$dir_path . $path.$dirs[$i]);
				}
			}
		}
		$fp = fopen(self::$dir_path . $path.'.dat', 'w');
		flock($fp, 2);
		fwrite($fp, self::file_data($data));
		flock($fp, 3);
		fclose($fp);
	}

	public static function file_data($str){
		if(is_array($str)){
			return implode('|', $str);
		} else {
			return $str;
		}
	}

	public static function check($path, $key){
		if(is_file(self::$dir_path . $path)){
			$file = file(self::$dir_path . $path);
			$rows = explode("\n", $file);
			foreach($rows as $r => $rr){
				$cols = explode('|', trim( $rr ));
				if(trim( $cols[0] ) == $key){
					return ($r+1);
				}
			}
			return false;
		} else {
			return false;
		}
	}

	public static function remove($path, $key){
		if(is_file(self::$dir_path . $path)){
			$file = file(self::$dir_path . $path);
			$rows = explode("\n", $file);
			$put_back_in = '';
			foreach($rows as $rr){
				$cols = explode('|', trim( $rr ));
				if(trim( $cols[0] ) != $key){
					$put_back_in = trim($rr)."\n";
				}
			}
			return false;
		} else {
			return false;
		}
	}
}