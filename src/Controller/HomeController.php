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
            $user = $request->getAttributes();

            $view = Twig::fromRequest($request);
            return $view->render($response, 'index.html.twig', [
                'user' => $request->getAttribute('user')
            ]);
        }

        public function login(Request $request, Response $response)
        {
            $view = Twig::fromRequest($request);
            return $view->render($response, 'login.html.twig');
        }

        public function logout(Request $request, Response $response)
        {
            $html ='
                <a class="underline" href="/login">Login</a> or <a class="underline" href="/register">Register</a>
            ';

            DB::delete('userSessions','sessionId=%s',session_id());

            $response->getBody()->write($html);

            return $response;
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
