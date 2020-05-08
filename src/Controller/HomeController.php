<?php
    declare(strict_types=1);

    namespace App\Controller;

    use DB;
    use Psr\Http\Message\ServerRequestInterface as Request;
    use Psr\Http\Message\ResponseInterface as Response;
    use Slim\Views\Twig;

    final class HomeController
    {


        public function home(Request $request, Response $response, $args = [])
        {
            $view = Twig::fromRequest($request);
            return $view->render($response, 'index.html.twig');
        }

        public function login(Request $request, Response $response)
        {
            $view = Twig::fromRequest($request);
            return $view->render($response, 'login.html.twig');
        }

        public function register(Request $request, Response $response)
        {

            $view = Twig::fromRequest($request);
            return $view->render($response, "register.html.twig");
        }


        public function hello(Request $request, Response $response, $args = [])
        {
            $session = $request->getAttribute('session');

            $str = isset($session['example']) ? $session['example'] : 'Empty Session, ';

            $response->getBody()->write($str . ' Hello!');
            return $response;
        }

        public function jsondata(Request $request, Response $response, $args = [])
        {
            $data = array('name' => 'Rob', 'age' => 40);

            $payload = json_encode($data);

            $response->getBody()->write($payload);
            return $response
                ->withHeader('Content-Type', 'application/json');
        }


    }
