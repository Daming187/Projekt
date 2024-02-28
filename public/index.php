<?php

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;

use Slim\App;
use Slim\Factory\AppFactory;
use Slim\Routing\Route;
use Slim\Routing\RouteContext;

use Middlewares\PhpSession;

use Dleschner\Slim\Session;
use Dleschner\Slim\Starface;

use Dleschner\Slim\Client\Login;
use Dleschner\Slim\Client\GroupEdit;
use Dleschner\Slim\Parsers;
use Slim\Psr7\Response as Psr7Response;

require(__DIR__.'/../vendor/autoload.php');

ini_set('session.use_cookies', false);
ini_set('session.cache_limiter', '');

$ini = parse_ini_file(__DIR__.'/../.cred.ini');


$loader = new \Twig\Loader\FilesystemLoader(__DIR__.'/../views');
$twig = new \Twig\Environment($loader, [
    'debug' => true,
]);

$starface = new Starface(Parsers::parseUrlField($ini));

/**
 * @param array<string, string> $data
 * @param array<string, string> $queryParams
 */
function urlFor(App $app, string $routeName, array $data = [], array $queryParams = []): string {
    $routeParser = $app->getRouteCollector()->getRouteParser();
    return $routeParser->urlFor($routeName, $data , $queryParams);
} 

/**
 * @template T
 * 
 * @param class-string<T> $classname
 * @param mixed           $value
 * 
 * @return ?T
 */
function castObjecct(string $classname, $value) {
    if ($value instanceof $classname) {
        return $value;
    }
    return null;
}

$app = AppFactory::create();

$app->add(function (Request $request, RequestHandler $handler) use ($app) {
    if ($route = castObjecct(Route::class, $request->getAttribute(RouteContext::ROUTE))) {
        if (in_array($route->getName(), ['login', 'login-post', 'logout'])) {
            return $handler->handle($request);
        }
    }

    if ( !Session::hasUserToken()) {
        return (new Psr7Response())
            ->withHeader('Location', urlFor($app, 'login'))
            ->withStatus(303)
            ;
    } else {
        return $handler->handle($request);
    }
});

$app->addRoutingMiddleware();

$app->add(new PhpSession());

$app->addErrorMiddleware(true, true, true);

$app->get('/login', function (Request $request, Response $response) use ($twig) {
    $response->getBody()->write($twig->render('login.html'));
    return $response;
})->setName('login');

$app->post('/login', function (Request $request, Response $response) use ($app, $ini, $starface) {
    try {
        $userParams = Login::parse($request->getParsedBody());
    } catch (RuntimeException $oje) {
        $response->getBody()->write('400 Bad Request');
        return $response->withStatus(400);
    }

    try {
        $adminParams = Login::parse($ini);
    } catch (RuntimeException $oje) {
        $response->getBody()->write('500 Internal Server Error parse');
        return $response->withStatus(500);
    }

    $adminToken = $starface->getAuthToken($adminParams->loginId, $adminParams->password);
    if ( !isset($adminToken)) {
        $response->getBody()->write('500 Internal Server Error login');
        return $response->withStatus(500);
    }

    $authToken = $starface->getAuthToken($userParams->loginId, $userParams->password);
    if ( !isset($authToken)) {
        return $response
            ->withHeader('Location', urlFor($app, 'login'))
            ->withStatus(303);
    }

    Session::setUserToken($authToken);
    Session::setAdminToken($adminToken);

    return $response
            ->withHeader('Location', urlFor($app, 'groups'))
            ->withStatus(303);
})->setName('login-post');

$app->any('/logout', function (Request $request, Response $response) use ($app) {
    Session::delUserToken();
    Session::delAdminToken();

    return $response
            ->withHeader('Location', urlFor($app, 'login'))
            ->withStatus(303);
})->setName('logout');


$app->any('/users/me', function (Request $request, Response $response) use ($twig, $starface) {
    $authToken = Session::getUserToken();

    $user = $starface->getUsersMe($authToken);
    $response->getBody()->write($twig->render('usersMe.html', [
        'user' => $user
    ]));

    return $response;
})->setName('usersMe');

$app->any('/groups', function (Request $_request, Response $response) use ($twig, $starface) {
    $adminToken = Session::getAdminToken();

    $groups = $starface->getGroups($adminToken);
    $response->getBody()->write($twig->render('groups.html', [
        'groups' => $groups->items
    ]));

    return $response;
})->setName('groups');

$app->any('/groups/{id}', function (Request $_request, Response $response, array $args) use ($twig, $starface) {
    $adminToken = Session::getAdminToken();

    $group = $starface->getGroup($adminToken, Parsers::parseIdField($args));
    $response->getBody()->write($twig->render('group.html', [
        'group' => $group
    ]));

    return $response;
})->setName('group');

$app->get('/groups/{id}/edit', function (Request $_request, Response $response, array $args) use ($twig, $starface) {
    $adminToken = Session::getAdminToken();

    $group = $starface->getGroup($adminToken, Parsers::parseIdField($args));
    $response->getBody()->write($twig->render('groupEdit.html', [
        'group' => $group
    ]));

    return $response;
})->setName('groupEdit');

$app->post('/groups/{id}/edit', function (Request $request, Response $response, array $args) use ($app, $starface) {
    $adminToken = Session::getAdminToken();

    $users = GroupEdit::parse($request->getParsedBody())->users;

    $group = $starface->getGroup($adminToken, Parsers::parseIdField($args));

    foreach ($group->assignableUsers as $assignableUser) {
        $id = $assignableUser->id;
        if (isset($users[$id])) {
            $group = $group->setAssigned($id, $users[$id]);
        }
    }

    $starface->putGroup($adminToken, $group);

    return $response
        ->withHeader('Location', urlFor($app, 'group', [ 'id' => (string)$group->id ]))
        ->withStatus(303);
});

$app->run();