<?php
use JW3B\Data\FileWriting;

include 'vendor/autoload.php';

$write = new FileWriting(dataname: 'testing');

$write->save('test', 'test phrase');

$FileWriting = new FileWriting(dataname: 'test/file-data');
$FileWriting->save('name/to-reference/later',[
	'works' => [
		'with' => 'arrays',
		'multi' => [
			'deep' => 'it doesnt',
			'matter' => [
				'how' => [
					'deep' => 'you go'
				]
			]
		]
	]
]);