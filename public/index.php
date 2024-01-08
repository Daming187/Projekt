<?php

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;

use Slim\App;
use Slim\Factory\AppFactory;
use Slim\Routing\Route;
use Slim\Routing\RouteContext;

use Middlewares\PhpSession;

use Dleschner\Slim\Starface;
use Dleschner\Slim\Starface\Login;
use Slim\Psr7\Response as Psr7Response;

require(__DIR__.'/../vendor/autoload.php');

ini_set('session.use_cookies', false);
ini_set('session.cache_limiter', '');




$ini = parse_ini_file(__DIR__.'/.cred.ini');

$loader = new \Twig\Loader\FilesystemLoader(__DIR__.'/../views');
$twig = new \Twig\Environment($loader, [
    'debug' => true,
]);

function urlFor(App $app, string $routeName, array $data = [], array $queryParams = []): string {
    $routeParser = $app->getRouteCollector()->getRouteParser();
    /** @psalm-suppress MixedArgumentTypeCoercion */
    return $routeParser->urlFor($routeName, $data , $queryParams);
} 

$app = AppFactory::create();

$app->add(function (Request $request, RequestHandler $handler) use ($app) {
    /** @psalm-suppress MixedAssignment */
    if ($route = $request->getAttribute(RouteContext::ROUTE)) {
        if ($route instanceof Route) {
            if (in_array($route->getName(), ['login', 'login-post', 'logout'])) {
                return $handler->handle($request);
            }
        }
    }

    if ( !isset($_SESSION['authToken'])) {
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

$app->post('/login', function (Request $request, Response $response) use ($app) {
    
    $params = (array)$request->getParsedBody();

    /** @psalm-suppress MixedAssignment */
    $loginId = $params['loginId'];
    /** @psalm-suppress MixedAssignment */
    $password = $params['password'];

    
    /** @psalm-suppress MixedArgument */
    $authToken = Starface::getAuthToken($loginId, $password);
    if ( !isset($authToken)) {
        return $response
            ->withHeader('Location', urlFor($app, 'login'))
            ->withStatus(303);
    }

    $_SESSION['authToken'] = $authToken;

    return $response
            ->withHeader('Location', urlFor($app, 'usersMe'))
            ->withStatus(303);
})->setName('login-post');

$app->any('/logout', function (Request $request, Response $response) use ($app) {
    unset($_SESSION['authToken']);

    return $response
            ->withHeader('Location', urlFor($app, 'login'))
            ->withStatus(303);
})->setName('logout');


$app->any('/users/me', function (Request $request, Response $response) use ($twig) {
    /** @psalm-suppress MixedAssignment */
    $authToken = $_SESSION['authToken'];

    /** @psalm-suppress MixedArgument */
    $usersMe = Starface::getUsersMe($authToken);
    /** @psalm-suppress PossiblyNullArgument */
    $response->getBody()->write($twig->render('usersMe.html', $usersMe));

    return $response;
})->setName('usersMe');

$app->any('/groups', function (Request $_request, Response $response) use ($twig) {
    /** @psalm-suppress MixedAssignment */
    $authToken = $_SESSION['authToken'];

    /** @psalm-suppress MixedArgument */
    $groups = Starface::getGroups($authToken);
    $response->getBody()->write($twig->render('groups.html', [
        'groups' => $groups
    ]));

    return $response;
})->setName('groups');

/** @psalm-suppress MissingClosureParamType */
$app->any('/groups/{id}', function (Request $request, Response $response, $args) use ($twig) {
    /** @psalm-suppress MixedAssignment */
    $authToken = $_SESSION['authToken'];

    /** @psalm-suppress MixedArrayAccess 
     * @psalm-suppress MixedArgument 
     */
    $group = Starface::getGroup($authToken, (int)$args['id']);
    $response->getBody()->write($twig->render('group.html', [
        'group' => $group
    ]));

    return $response;
})->setName('group');

$app->any('/contacts/tags', function (Request $request, Response $response) use ($twig) {
    /** @psalm-suppress MixedAssignment */
    $authToken = $_SESSION['authToken'];
    /** @psalm-suppress MixedArgument */
    $contactsTag = Starface::getContactsTag($authToken);
    $response->getBody()->write($twig->render('contactsTag.html', [
        'contactsTag' => $contactsTag
    ]));

    return $response;
})->setName('contactsTag');

$app->run();