<?php
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;
use Slim\Views\PhpRenderer;
use Dotenv\Dotenv;

require __DIR__ . '/../vendor/autoload.php';

$dotenv = Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();
$dotenv->required(['DB_NAME', 'DB_USER', 'DB_PASS']);

$config = require __DIR__ . '/../config.php';

$app = AppFactory::create();

function getBasePath() {
    if (str_contains(__DIR__, 'PLATEAU_UseCase2025')) {
        return '/PLATEAU_UseCase2025/api';
    } else {
        return '/api';
    }
}

$app->setBasePath(getBasePath());

$renderer = new PhpRenderer(__DIR__ . '/../templates');

# IF212
# PLATEAU VIEW向けエクスポートデータ
# Examle URL:
# get_plateau_data?kind=SnowRemovalPriority&lon1=138.83276&lon2=138.869239&lat1=37.4407&lat2=37.4553&time=202602171800
$app->get('/get_plateau_data', function (Request $request, Response $response, $args) use ($renderer, $config) {
    $queryParams = $request->getQueryParams();
    $queryParams["lon1"] = floatval($queryParams["lon1"]);
    $queryParams["lon2"] = floatval($queryParams["lon2"]);
    $queryParams["lat1"] = floatval($queryParams["lat1"]);
    $queryParams["lat2"] = floatval($queryParams["lat2"]);
    return $renderer->render($response, 'get_plateau_data.php', [
        'paths' => $config['paths'],
        'queryParams' => $queryParams
    ]);
});

# get_qgis_data?time=202501021200
$app->get('/get_qgis_data', function (Request $request, Response $response, $args) use ($renderer, $config) {
    $queryParams = $request->getQueryParams();
    return $renderer->render($response, 'get_qgis_data.php', [
        'paths' => $config['paths'],
        'queryParams' => $queryParams
    ]);
});

$app->post('/submit_feedback', function (Request $request, Response $response, $args) use ($renderer, $config) {
    return $renderer->render($response, 'submit_feedback.php', [
        'config' => $config,
        'data' => $request->getParsedBody()
    ]);
});

$app->post('/submit_alert', function (Request $request, Response $response, $args) use ($renderer, $config) {
    return $renderer->render($response, 'submit_alert.php', [
        'config' => $config,
        'data' => $request->getParsedBody()
    ]);
});

$app->run();
