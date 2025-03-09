# File-Writing
An easy way to store some config operations within text files.

# Install
````shell
composer require myjw3b/file-writing
````

# Autoload
make sure to include this at the top of your page
````php
use JW3B\Data\FileWriting;
include "vendor/autoload.php";
````
# Storage
````php
$FileWriting = new FileWriting('cache/file-data');
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
````
# Retreiving Data
````php
$FileWriting = new FileWriting('cache/file-data');
$var = $FileWriting->get_file('name/to-reference/later');

echo '<pre>'.print_r($var,1).'</pre>';
````
# Output
````
Array
(
    [works] => Array
        (
            [with] => arrays
            [multi] => Array
                (
                    [deep] => it doesnt
                    [matter] => Array
                        (
                            [how] => Array
                                (
                                    [deep] => you go
                                )
                        )
                )
        )
)
````