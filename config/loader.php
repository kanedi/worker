<?php

$loader = new \Phalcon\Loader();

/**
 * We're a registering a set of directories taken from the configuration file
 */
$loader->registerDirs(
	array(
		$config->application->modelsDir,
        $config->application->libraryDir
	)
);
//echo $config->application->libraryDir;exit;
$loader->registerNamespaces(
    array(
        "Library"    => $config->application->libraryDir,
        "PhpAmqpLib"    => $config->application->libraryDir . "PhpAmqpLib"
    )
);

$loader->register();