<?php
/**
 * SPDX-FileCopyrightText: 2016 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

if (php_sapi_name() !== 'cli') {
	die('Script can only be invoked via CLI');
}

if(count($argv) !== 2) {
	die('php converter.php 10_million_password_list_top_10000000.txt');
}

$passwordLengthArray = [];
$separator = "\r\n";

$maxPasswordFileLength = 15;
$count = [];
$file = fopen(__DIR__ . '/' . $argv[1], 'r');
while(!feof($file)){
	$password = trim(strtolower(fgets($file)));
	if($password !== '') {
		$count[strlen($password)] = isset($count[strlen($password)]) ? $count[strlen($password)] + 1 : 1;
		$passwordLengthArray[strlen($password)][$password] = true;
	}
}
fclose($file);

foreach($passwordLengthArray as $length => $passwords) {
	$content = <<<EOF
<?php
/**
 * SPDX-FileCopyrightText: 2016 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */
return array (

EOF;
	foreach ($passwords as $password => $true) {
		$content .= '  \'' . str_replace("'", "\\'", str_replace('\\', '\\\\', $password)) . '\' => true,' . "\n";
	}
	$content .= ');';
	file_put_contents(__DIR__ . '/list-'.$length.'.php', $content);
}
