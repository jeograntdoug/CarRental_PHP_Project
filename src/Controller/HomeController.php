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

            // $paramArray = $request->getQueryParams();
            // $url = isset($paramArray['url']) ? $paramArray['url'] : "/";

            $backUrl = $request->getHeader('Referer')[0];

            $_SESSION['backUrl'] = $backUrl;

            return $view->render($response, "login.html.twig");
        }

        public function logout(Request $request, Response $response)
        {
            $html = '
                <a class="underline" href="/login">Login</a> or <a class="underline" href="/register">Register</a>
            ';

            DB::delete('userSessions', 'sessionId=%s', session_id());

            unset($_SESSION['userId']);

            $response->getBody()->write($html);

            return $response;
        }

        public function register(Request $request, Response $response)
        {

            $view = Twig::fromRequest($request);
            return $view->render($response, "register.html.twig");
        }


    }
