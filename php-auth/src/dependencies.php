<?php

use Slim\Container;

$container = $app->getContainer();

$container['pdo'] = function (Container $c) {
    $settings = $c->get('settings')['pdo'];
    return new \PDO(
        $settings['dsn'],
        $settings['username'],
        $settings['passwd'],
        [
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        ]
    );
};

$container['redis'] = function (Container $c) {
    $redis = new Redis();
    $redis->connect('redis', 6379);

    return $redis;
};


$container['userDao'] = function (Container $c) {
    return new \Dao\UserDao($c->get('pdo'));
};

$container['userRoleDao'] = function (Container $c) {
    return new \Dao\UserRoleDao($c->get('pdo'));
};

$container['userSessionDao'] = function (Container $c) {
    return new \Dao\UserSessionDao($c->get('pdo'));
};

$container['loginService'] = function (Container $c) {
    return new \Service\LoginService(
        $c->get('userDao'),
        $c->get('userRoleDao'),
        $c->get('userSessionDao'),
        $c->get('settings')['clientId'],
        $c->get('settings')['clientSecret']
    );
};


$container['uri'] = function () {
    return \Slim\Http\Uri::createFromEnvironment(new \Slim\Http\Environment($_SERVER));
};

$container['view'] = function (Container $c) {
    $settings = $c->get('settings')['view'];

    $view = new \Slim\Views\Twig($settings['template_path'], $settings);
    $view->addExtension(new \Slim\Views\TwigExtension($c->get('router'), $c->get('uri')));
    $view->addExtension(new Twig_Extension_Debug());

    return $view;
};

$container['logger'] = function (Container $c) {
    $settings = $c->get('settings')['logger'];
    $logger = new Monolog\Logger($settings['name']);
    $logger->pushProcessor(new Monolog\Processor\UidProcessor());
    $logger->pushHandler(new Monolog\Handler\StreamHandler($settings['path'], $settings['level']));

    return $logger;
};

