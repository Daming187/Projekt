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

use Slim\Psr7\Response as Psr7Response;

require(__DIR__.'/../vendor/autoload.php');

ini_set('session.use_cookies', false);
ini_set('session.cache_limiter', '');

$ini = parse_ini_file(__DIR__.'/../.cred.ini');

$loader = new \Twig\Loader\FilesystemLoader(__DIR__.'/../views');
$twig = new \Twig\Environment($loader, [
    'debug' => true,
]);

function urlFor(App $app, string $routeName, array $data = [], array $queryParams = []): string {
    $routeParser = $app->getRouteCollector()->getRouteParser();
    /** @psalm-suppress MixedArgumentTypeCoercion */
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

$app->post('/login', function (Request $request, Response $response) use ($app, $ini) {
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

    $adminToken = Starface::getAuthToken($adminParams->loginId, $adminParams->password);
    if ( !isset($adminToken)) {
        $response->getBody()->write('500 Internal Server Error login');
        return $response->withStatus(500);
    }

    $authToken = Starface::getAuthToken($userParams->loginId, $userParams->password);
    if ( !isset($authToken)) {
        return $response
            ->withHeader('Location', urlFor($app, 'login'))
            ->withStatus(303);
    }

    Session::setUserToken($authToken);
    Session::setAdminToken($adminToken);

    return $response
            ->withHeader('Location', urlFor($app, 'usersMe'))
            ->withStatus(303);
})->setName('login-post');

$app->any('/logout', function (Request $request, Response $response) use ($app) {
    Session::delUserToken();
    Session::delAdminToken();

    return $response
            ->withHeader('Location', urlFor($app, 'login'))
            ->withStatus(303);
})->setName('logout');


$app->any('/users/me', function (Request $request, Response $response) use ($twig) {
    $authToken = Session::getUserToken();

    $usersMe = Starface::getUsersMe($authToken);
    /** @psalm-suppress PossiblyNullArgument */
    $response->getBody()->write($twig->render('usersMe.html', $usersMe));

    return $response;
})->setName('usersMe');

$app->any('/groups', function (Request $_request, Response $response) use ($twig) {
    $adminToken = Session::getAdminToken();

    $groups = Starface::getGroups($adminToken);
    $response->getBody()->write($twig->render('groups.html', [
        'groups' => $groups->items
    ]));

    return $response;
})->setName('groups');

/** @psalm-suppress MissingClosureParamType */
$app->any('/groups/{id}', function (Request $request, Response $response, $args) use ($twig) {
    $adminToken = Session::getAdminToken();

    /** @psalm-suppress MixedArrayAccess */
    $group = Starface::getGroup($adminToken, (int)$args['id']);
    $response->getBody()->write($twig->render('group.html', [
        'group' => $group
    ]));

    return $response;
})->setName('group');

/** @psalm-suppress MissingClosureParamType */
$app->any('/groups/{id}/edit', function (Request $request, Response $response, $args) use ($twig) {
    $adminToken = Session::getAdminToken();

    /** @psalm-suppress MixedArrayAccess */
    $group = Starface::getGroup($adminToken, (int)$args['id']);
    $response->getBody()->write($twig->render('groupEdit.html', [
        'group' => $group
    ]));

    return $response;
})->setName('groupEdit');

$app->run();