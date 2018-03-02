<?php

use Curve\Application\Controller\DefaultController;
use Curve\Application\Repository\Redis\RedisRepository;
use Curve\Domain\Service\PrepaidCardService;

require __DIR__ . '/vendor/autoload.php';


$app = new \Slim\App;

$container = $app->getContainer();

$container['redis'] = function ($c) {
    return new \Predis\Client([
        'scheme' => 'tcp',
        'host'   => 'redis-server',
        'port'   => 6379,
    ]);
};

$container['repository'] = function ($c) {
    $redis = $c->redis;
    return new RedisRepository($redis);
};

$container['service'] = function ($c) {
    $repository = $c->repository;
    return new PrepaidCardService($repository);
};

$container['DefaultController'] = function ($c) {
    $service = $c->service;
    return new DefaultController($service);
};

$app->post('/card', 'DefaultController:emit');
$app->patch('/card/deposit', 'DefaultController:deposit');
$app->patch('/card/lock', 'DefaultController:lock');
$app->patch('/card/unlock', 'DefaultController:unlock');
$app->get('/card/{card_number}', 'DefaultController:displayBalance');
$app->post('/card/authorize', 'DefaultController:authorize');
$app->post('/card/capture', 'DefaultController:capture');
$app->post('/card/reverse', 'DefaultController:reverse');
$app->post('/card/refund', 'DefaultController:refund');

$app->run();
