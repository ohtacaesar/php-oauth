<?php

use Dao\UserDao;
use Dao\UserProviderDao;
use Dao\UserRoleDao;
use Middleware\Csrf;
use Slim\Container;
use Slim\Http\Environment;
use Slim\Http\Uri;
use Slim\Views\Twig;
use Slim\Views\TwigExtension;
use Twig\AppExtension;
use Twig\CsrfExtension;
use Twig\FlashExtension;
use Util\Session;

$container = $app->getContainer();

$container['pdo'] = function (Container $c) {
    $settings = $c->get('settings')['pdo'];
    return new \PDO(
        $settings['dsn'],
        $settings['username'],
        $settings['password'],
        [
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        ]
    );
};

$container['userDao'] = function (Container $c) {
    return new UserDao($c->get('pdo'));
};

$container['userRoleDao'] = function (Container $c) {
    return new UserRoleDao($c->get('pdo'));
};

$container['userProviderDao'] = function (Container $c) {
    return new UserProviderDao($c['pdo']);
};

$container['userManager'] = function (Container $c) {
    return new \Manager\UserManager(
        $c['userDao'],
        $c['userRoleDao'],
        $c['userProviderDao'],
        $c['logger']
    );
};

$container['authService'] = function (Container $c) {
    return new \Service\AuthService(
        $c['userManager'],
        $c['session'],
        $c['settings']['grantRules'],
        $c['logger']
    );
};

$container['uri'] = function () {
    return Uri::createFromEnvironment(new Environment($_SERVER));
};

$container['view'] = function (Container $c) {
    $settings = $c->get('settings')['view'];

    $view = new Twig($settings['template_path'], $settings);
    $view->addExtension(new TwigExtension($c->get('router'), $c->get('uri')));
    $view->addExtension(new Twig_Extension_Debug());
    $view->addExtension(new CsrfExtension($c['csrf']));
    $view->addExtension(new FlashExtension($c['session']));
    $view->addExtension(new AppExtension($c));

    return $view;
};

$container['logger'] = function (Container $c) {
    $settings = $c->get('settings')['logger'];
    $logger = new Monolog\Logger($settings['name']);
    $logger->pushProcessor(new Monolog\Processor\UidProcessor());
    $logger->pushHandler(new Monolog\Handler\StreamHandler($settings['path'], $settings['level']));

    return $logger;
};

$container['session'] = function (Container $c) {
    $uri = $c['uri'];
    $host = $uri->getHost();
    $cookieDomain = explode('.', $host);

    if (count($cookieDomain) > 2) {
        array_shift($cookieDomain);
        $cookieDomain = "." . join(".", $cookieDomain);
    } else {
        $cookieDomain = $host;
    }

    $config = array_merge($c['settings']['cookie'], [
        'cookie_domain' => $cookieDomain
    ]);

    return new Session($config);
};

$container['githubProvider'] = function (Container $c) {
    $conf = $c['settings']['github'];

    return new League\OAuth2\Client\Provider\Github([
        'clientId' => $conf['client_id'],
        'clientSecret' => $conf['client_secret'],
    ]);
};

$container['googleProvider'] = function (Container $c) {
    $conf = $c['settings']['google'];
    /** @var Uri $uri */
    $uri = $c['uri'];
    $uri = $uri->withPath("/google/callback")->withQuery("")->withFragment("");

    return new League\OAuth2\Client\Provider\Google([
        'clientId' => $conf['client_id'],
        'clientSecret' => $conf['client_secret'],
        'redirectUri' => (string)$uri,
        'useOidcMode' => true,
    ]);
};

$container['csrf'] = function (Container $c) {
    return new Csrf($c['session'], $c['logger']);
};
