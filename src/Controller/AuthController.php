<?php

namespace App\Controller;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

use Slim\Views\Twig;
use DB;

class AuthController
{
    public function authorize (Request $request, Response $response)
    {
        $post = $request->getParsedBody();

        $user = DB::queryFirstRow("SELECT * FROM users WHERE email=%s",$post['email']);

        if(isset($user['password']) && $post['password'] == $user['password']){
            unset($user['password']);

            DB::replace("userSessions",[
                'userId' => $user['id'],
                'sessionId' => session_id(),
                'updated_at' => date('Y-m-d H:i:s',time())
            ]);

            $_SESSION['userId'] = $user['id'];
            return $response->withHeader('Location','/');
        }

        $view = Twig::fromRequest($request);
        return $view->render($response->withStatus(401), 'login.html.twig',[
                'error' => 'The email or password is incorrect.'
        ]);
    }


    // public function register (Request $request, Response $response)
    // {

    // }
}