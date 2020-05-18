<?php

    namespace App\Controller;

    use Psr\Http\Message\ServerRequestInterface as Request;
    use Psr\Http\Message\ResponseInterface as Response;

    use Slim\Views\Twig;
    use DB;

    class AuthController
    {
        public function authorize(Request $request, Response $response)
        {
            $view = Twig::fromRequest($request);
            $post = $request->getParsedBody();

            $user = DB::queryFirstRow("SELECT * FROM users WHERE email=%s", $post['email']);

            if (isset($user['password']) && $post['password'] == $user['password']) {
                unset($user['password']);

                DB::replace("userSessions", [
                    'userId' => $user['id'],
                    'sessionId' => session_id(),
                    'updated_at' => date('Y-m-d H:i:s', time())
                ]);

                $_SESSION['userId'] = $user['id'];
                // $url = $post['url'];

                $url = isset($_SESSION['backUrl']) ? $_SESSION['backUrl'] : '/';
                unset($_SESSION['backUrl']);
                if(strpos($url,'review_reserve') !== false){
                    $url = '/review_reserve';
                }

                return $response->withHeader('Location', $url);
                /* return $view->render($response, $url, [
                     'selVehicle' => $selVehicle,
                     'userInfo' => $userInfo,
                     'dateLocationData' => $_SESSION
                 ]);*/
            }


            return $view->render($response->withStatus(401), 'login.html.twig', [
                'error' => 'The email or password is incorrect.'
            ]);
        }

        // public function register (Request $request, Response $response)
        // {

        // }
    }