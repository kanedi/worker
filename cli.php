<?php
/**
 * Created by PhpStorm.
 * User: martinadiyono
 * Date: 6/9/14
 * Time: 10:27 AM
 */

use Phalcon\Mvc\View,
    Phalcon\Mvc\View\Engine\Volt as VoltEngine,
    Phalcon\DI\FactoryDefault\CLI as CliDI,
    Phalcon\CLI\Console as ConsoleApp,
    Phalcon\Db\Adapter\Pdo\Mysql as DbAdapter;

define('VERSION', '1.0.0');

//Using the CLI factory default services container
$di = new CliDI();

// Define path to application directory
defined('APPLICATION_PATH')
|| define('APPLICATION_PATH', realpath(dirname(__FILE__)));

if(is_readable(APPLICATION_PATH . '/config/config.php')) {
    $config = include APPLICATION_PATH . '/config/config.php';
    $di->set('config', $config);
}

$di->set('db', function() use ($config) {
    return new DbAdapter(array(
        'host' => $config->database->host,
        'username' => $config->database->username,
        'password' => $config->database->password,
        'dbname' => $config->database->dbname
    ));
});

$di->set('view', function() use ($config) {
    $view = new View();

    $view->setViewsDir(APPLICATION_PATH . '/views');

    $view->registerEngines(array(
        '.volt' => function($view, $di) use ($config) {
                $volt = new VoltEngine($view, $di);
                $volt->setOptions(array(
                    'compiledPath' => APPLICATION_PATH . '/cache',
                    'compiledSeparator' => '_'
                ));

                $volt->getCompiler()->addFunction(
                    'numberToWord',
                    function($number)
                    {
                        return "Library\\Ozip\\StringHelper::numberToWord({$number})";
                    }
                );

                $volt->getCompiler()->addFunction(
                    'formatNumber',
                    function($number)
                    {
                        return "Library\\Ozip\\StringHelper::formatNumber({$number})";
                    }
                );

                return $volt;
            },
        '.phtml' => 'Phalcon\Mvc\View\Engine\Php'
    ));

    return $view;
}, true);

/**
 * Register the autoloader and tell it to register the tasks directory
 */
$loader = new \Phalcon\Loader();
$loader->registerDirs(
    array(
        APPLICATION_PATH . '/tasks',
        APPLICATION_PATH . '/library',
        APPLICATION_PATH . '/models',
        APPLICATION_PATH . '/views',
    )
);

$loader->registerNamespaces(
    array(
        "Library"    => APPLICATION_PATH . '/library',
		"PhpAmqpLib" => APPLICATION_PATH . '/library/PhpAmqpLib'
    )
);

$loader->register();

//Create a console application
$console = new ConsoleApp();
$console->setDI($di);

/**
 * Process the console arguments
 */
$arguments = array();
foreach($argv as $k => $arg) {
    if($k == 1) {
        $arguments['task'] = $arg;
    } elseif($k == 2) {
        $arguments['action'] = $arg;
    } elseif($k >= 3) {
        $arguments[] = $arg;
    }
}

// define global constants for the current task and action
define('CURRENT_TASK', (isset($argv[1]) ? $argv[1] : null));
define('CURRENT_ACTION', (isset($argv[2]) ? $argv[2] : null));

try {
    // handle incoming arguments
    $console->handle($arguments);
}
catch (\Phalcon\Exception $e) {
    echo $e->getMessage();
    exit(255);
}
