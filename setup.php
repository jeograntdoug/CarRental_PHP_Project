<?php

use Slim\Factory\AppFactory;
use Psr\Http\Message\ServerRequestInterface as Request;

use Slim\Views\Twig;
use Slim\Views\TwigMiddleware;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

require __DIR__ . '/vendor/autoload.php';

session_start();

$app = AppFactory::create();

// Define Custom Error Handler
$forbiddenErrorHandler = function (
    Psr\Http\Message\ServerRequestInterface $request,
    \Throwable $exception,
    bool $displayErrorDetails,
    bool $logErrors,
    bool $logErrorDetails
) use ($app) {
    $response = $app->getResponseFactory()->createResponse();
    // seems the followin can be replaced by your custom response
    // $page = new Alvaro\Pages\Error($c);
    // return $page->notFound404($request, $response);

    return $response->withHeader('Location','/forbidden',404);
};

// Add Error Middleware
$errorMiddleware = $app->addErrorMiddleware(true, true, true);
// Register the handler to handle only  HttpNotFoundException
// Changing the first parameter registers the error handler for other types of exceptions
$errorMiddleware->setErrorHandler(Slim\Exception\HttpNotFoundException::class, $forbiddenErrorHandler);
//$errorMiddleware->setErrorHandler(Slim\Exception\HttpInternalServerErrorException::class, $ErrorHandler);


// create a log channel

$log = new Logger('main');
$log->pushHandler(new StreamHandler('logs/everything.log', Logger::DEBUG));
$log->pushHandler(new StreamHandler('logs/errors.log', Logger::ERROR));

DB::$user = 'carrental';
DB::$password = 's?#-K;#"&6S0Msa:';
DB::$dbName = 'carrental';





DB::$error_handler = 'db_error_handler'; // runs on mysql query errors DB::$nonsql_error_handler = 'db_error_handler'; // runs on library errors (bad syntax, etc)

function db_error_handler($params)
{
    header("Location: /error_internal", 500);

    global $log;
    $log->error("Database erorr[Connection]: " . $params['error']);

    if ($params['query']) {
        $log->error("Database error[Query]: " . $params['query']);
    }
    die();
}

// Create Twig
$twig = Twig::create(__DIR__ . '/templates', ['cache' => __DIR__ . '/cache', 'debug' =>true]);

// Set Global variable($_SESSION)
$twig->getEnvironment()->addGlobal('session', $_SESSION);

// //set global date formatter. this is valid
// $twig->getEnvironment()
//     ->getExtension(\Twig\Extension\CoreExtension::class)
//     ->setDateFormat("F jS \\a\\t g:ia");


// Add Twig-View Middleware
$app->add(TwigMiddleware::create($app, $twig));
