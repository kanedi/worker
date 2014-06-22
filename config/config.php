<?php

$localConfig = include('local.php');
$database = $localConfig['database'];
$baseUri = $localConfig['baseUri'];
$emailConf = $localConfig['email'];

return new \Phalcon\Config(array(
	'database' => $database,
	'application' => array(
		'controllersDir' => __DIR__ . '/../../app/controllers/',
		'modelsDir'      => __DIR__ . '/../../app/models/',
		'viewsDir'       => __DIR__ . '/../../app/views/',
		'pluginsDir'     => __DIR__ . '/../../app/plugins/',
		'libraryDir'     => __DIR__ . '/../../app/library/',
		'cacheDir'       => __DIR__ . '/../../app/cache/',
		'baseUri'        => $baseUri,
        'publicUrl'      => '127.0.0.1/sendmail',
	),
    'mail' => $emailConf,
    'rabbitmq' => $localConfig['rabbitmq'],
    'path_doc' => '/tmp/',
));

