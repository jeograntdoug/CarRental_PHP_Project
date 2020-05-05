<?php
declare(strict_types=1);

namespace App\Middleware;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\MiddlewareInterface as Middleware;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;

class AuthMiddleware implements Middleware
{
    /**
     * {@inheritdoc}
     */
    public function process(Request $request, RequestHandler $handler) : Response
    {
        //TODO : check user is valid
        $isValidUser = true;
        if(!$isValidUser){
            $uri = $request->getUri();
            $request = $request->withUri($uri->withPath('/errors/forbidden'));
        }

        return $handler->handle($request);
    }
}