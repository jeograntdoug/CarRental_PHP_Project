<?php

    require "setup.php";


//s?#-K;#"&6S0Msa:

    use Psr\Http\Message\ResponseInterface as Response;
    use Psr\Http\Message\ServerRequestInterface as Request;

    use Slim\Views\Twig;




    $app->get('/', function (Request $request, Response $response) {
        $view = Twig::fromRequest($request);
        return $view->render($response, "index.html.twig");
    });


    $app->run();

