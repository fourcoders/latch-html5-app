<?php

use  Silex\Application;
use Service\GuzzleClientService;

// Loads
$app = new Application();
$app->register(new Silex\Provider\TwigServiceProvider(), array(
    'twig.path' => __DIR__.'/../views',
));
$app->register(new Silex\Provider\UrlGeneratorServiceProvider());
$app->register(new Silex\Provider\SessionServiceProvider());
$app['client'] = function () {
	return new GuzzleClientService();
};

// Controllers
$app->get('/', function () use ($app) {
    return $app['twig']->render('index.html.twig', array());
})->bind('main');

$app->get('/authenticate', function () use ($app) {
    return $app['twig']->render('authenticate.html.twig', array(
    'error' => isset($_GET['error']) ? true : false,
    ));
})->bind('authenticate');

$app->post('/login', function () use ($app) {
    $response = $app['client']->autenticate($_POST['username'],$_POST['password'], $app['session']);
    $body = $response->json();
    return (count($body) == 0) ? $app->redirect('/apps') : $app->redirect('/authenticate?error') ;
})->bind('login');

$app->get('/apps', function () use ($app) {
	$response = $app['client']->applications($app['session']->get('COOKIE_SESSION'));
	$body = $response->json();
    return $app['twig']->render('apps.html.twig', array(
    	'apps' => $body 
    ));
})->bind('apps');

$app->get('/operations/{id}', function ($id) use ($app) {
    $response = $app['client']->applications($app['session']->get('COOKIE_SESSION'));
    $body = $response->json();
    return $app['twig']->render('operations.html.twig', array(
        'apps' => $body,
        'id' => $id
    ));
})->bind('operations');

$app->get('/switch/{operator}/{mode}', function ($operator, $mode) use ($app) {
    $response = $app['client']->switcher($operator, $mode, $app['session']->get('COOKIE_SESSION'));
    return $app->redirect($_SERVER["HTTP_REFERER"]);
})->bind('switch');

$app->get('/new-service', function () use ($app) {
    return $app['twig']->render('newservice.html.twig', array(
    ));
})->bind('new-service');

$app->get('/token', function () use ($app) {
    $response = $app['client']->token($app['session']->get('COOKIE_SESSION'));
    $body = $response->json();
    return $app['twig']->render('token.html.twig', array(
        'token' => $body
    ));
})->bind('token');

$app->run();
