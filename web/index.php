<?php

use PSRLinter\Linter;
use Symfony\Component\HttpFoundation\Request;

require('../vendor/autoload.php');

$app = new Silex\Application();
$app['debug'] = true;

$app->register(new Silex\Provider\MonologServiceProvider(), array(
  'monolog.logfile' => 'php://stderr',
));

$app->register(new Silex\Provider\TwigServiceProvider(), array(
    'twig.path' => __DIR__.'/views',
));

$app->get('/', function () use ($app) {
    return $app['twig']->render('index.twig', array(
        "code" => "",
        "result" => ""
    ));
});

$app->post('/', function (Request $request) use ($app) {
    $code = $request->get('code');
    $linter = new Linter();
    try {
        $report = $linter->lint($code);
        $errors = $report->getErrors();
        $result = array_map(function (\PSRLinter\Report\Error $error) {
            return [
                "description" => $error->getDescription(),
                "line" => $error->getLine(),
                "title" => $error->getTitle(),
                "level" => $error->getType()
            ];
        }, $errors);
    } catch (Exception $e) {
        $result = [
            [
                "description" => $e->getMessage(),
                "line" => "",
                "title" => "",
                "level" => "error"
            ]
        ];
    }
    return $app['twig']->render('index.twig', array(
        "code" => $code,
        "result" => $result
    ));
});

$app->run();