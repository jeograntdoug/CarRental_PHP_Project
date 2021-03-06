<?php
namespace App\Controller;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

use Slim\Views\Twig;

final class ErrorController 
{
    public function forbidden(Request $request, Response $response, $args = [])
    {
        $view = Twig::fromRequest($request);

        return $view->render($response->withStatus(403), 'errors/error_forbidden.html.twig');
    } 

    public function pageNotFound(Request $request, Response $response, $args = [])
    {
        $view = Twig::fromRequest($request);

        return $view->render($response->withStatus(404), 'errors/error_pagenotfound.html.twig');
    } 

    public function internal(Request $request, Response $response, $args = [])
    {
        $view = Twig::fromRequest($request);
        return $view->render($response->withStatus(500), 'errors/error_internal.html.twig');
    } 
}