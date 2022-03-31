<?php
use Phalcon\Di\FactoryDefault;
use Phalcon\Loader;
use Phalcon\Mvc\View;
use Phalcon\Mvc\Application;
use Phalcon\Url;
use Phalcon\Db\Adapter\Pdo\Mysql;
use Phalcon\Config;
use Phalcon\Config\ConfigFactory;
use Phalcon\Events\Event;
use Phalcon\Events\Manager as EventsManager;

$config = new Config([]);

// Define some absolute path constants to aid in locating resources
define('BASE_PATH', dirname(__DIR__));
define('APP_PATH', BASE_PATH . '/app');

// Register an autoloader
$loader = new Loader();

$loader->registerDirs(
    [
        APP_PATH . "/controllers/",
        APP_PATH . "/models/",
    ]
);
$loader->registerNamespaces(
    [
        'App\Components' => APP_PATH.'/components',
        'App\Notification' => APP_PATH.'/notification'
    ]
);
$loader->register();

$container = new FactoryDefault();

$container->set(
    'view',
    function () {
        $view = new View();
        $view->setViewsDir(APP_PATH . '/views/');
        return $view;
    }
);

$container->set(
    'url',
    function () {
        $url = new Url();
        $url->setBaseUri('/');
        return $url;
    }
);

$application = new Application($container);

$container->set(
    'db',
    function () {
        $config=$this->get('config')->db;
        return new Mysql(
            [
                'host'     => $config->host,
                'username' => $config->username,
                'password' => $config->password,
                'dbname'   => $config->dbname,
            ]
        );
    }
);
//Event manager
$eventsManager=new EventsManager();
$eventsManager->attach(
    'notification',
    new App\Notification\NotificationListener()
);
$application->setEventsManager($eventsManager);
$eventsManager->attach(
    'application:beforeHandleRequest',
    new App\Notification\NotificationListener()
);
$container->set(
    'EventsManager',
    $eventsManager
);
$container->set(
    'config',
    function () {
        $file='../app/etc/config.php';
        $factory=new ConfigFactory();
        return $factory->newInstance('php', $file);
    }
);
try {
    // Handle the request
    $response = $application->handle(
        $_SERVER["REQUEST_URI"]
    );

    $response->send();
} catch (\Exception $e) {
    echo 'Exception: ', $e->getMessage();
}
