<?php
declare(strict_types=1);

namespace App\Middleware;

use Psr\Http\Message\ResponseInterface as Response;
use Slim\Psr7\Factory\ResponseFactory;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\MiddlewareInterface as Middleware;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Exception\HttpNotFoundException;

class SessionMiddleware implements Middleware
{
    /**
     * {@inheritdoc}
     */
    public function process(Request $request, RequestHandler $handler) : Response
    {
        // if (isset($_SERVER['HTTP_AUTHORIZATION'])) {
        //     session_start();
        //     $request = $request->withAttribute('session', $_SESSION);
        // }
        
        //FIXME : change to above conditional for security
        session_start();
        $request = $request->withAttribute('session', $_SESSION);

        // $uri = $request->getUri();
        // $request = $request->withUri($uri->withPath('/errors/forbidden'));


        return $handler->handle($request);
    }
}