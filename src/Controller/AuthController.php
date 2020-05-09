<?php

namespace App\Controller;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Respect\Validation\Validator as v;
use Slim\Views\Twig;
use DB;

class AuthController
{
    public function authenticate (Request $request, Response $response)
    {
        $post = $request->getParsedBody();

        $user = DB::queryFirstRow("SELECT * FROM users WHERE email=%s",$post['email']);

        // if(v::notEmpty()->validate($user)) {

        // }
        if(isset($user['passsword']) && $post['password'] === $user['password']){
            unset($user['password']);
            $_SESSION['user'] = $user;
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