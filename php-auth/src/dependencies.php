<?php

$container = $app->getContainer();

/**
 * @return PDO
 */
$container['pdo'] = function () {
    $config = yaml_parse_file(__DIR__ . '/../config.yml');

    return new \PDO(
        $config['pdo']['dsn'],
        $config['pdo']['username'],
        $config['pdo']['passwd'],
        [PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]
    );
};

/**
 * @param \Slim\Container $c
 * @return \Dao\UserDao
 */
$container['userDao'] = function ($c) {
    return new \Dao\UserDao($c->get('pdo'));
};

/**
 * @return \Slim\Http\Uri
 */
$container['uri'] = function () {
    return \Slim\Http\Uri::createFromEnvironment(new \Slim\Http\Environment($_SERVER));
};

/**
 * @param \Slim\Container $c
 * @return \Slim\Views\Twig
 */
$container['view'] = function ($c) {
    $view = new \Slim\Views\Twig(__DIR__ . '/../templates', [
        'debug' => true,
        # 'cache'
    ]);
    $view->addExtension(new \Slim\Views\TwigExtension($c->get('router'), $c->get('uri')));
    $view->addExtension(new Twig_Extension_Debug());

    return $view;
};
