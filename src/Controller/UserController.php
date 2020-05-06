<?php
namespace App\Controller;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use DB;

use Slim\Views\Twig;

class UserController 
{
    private function isNameValid($name){
        return preg_match('/^[a-zA-Z _-]$/',$name) === 1;
    }
    
    public function create (Request $request,Response $response)
    {
        $post = $request->getParsedBody();

        $errorList = [];
        //TODO: password and email validation

        // $johnDoe = [
        //     'firstname' => 'john',
        //     'lastname' => 'doe',
        //     'drivinglicense' => '123456789',
        //     'address' => '123, rue johnabbott',
        //     'phone' => '123-456-789',
        //     'role' => 'user',
        //     'email' => 'johndoe@example.com',
        //     'password' => 'q1w2E#'
        // ];





        if(!empty($errorList)) 
        {
            $view = Twig::fromRequest($request);

            return $view->render($response,'auth/register.html.twig',[
                'errorList' => $errorList,
                'prevInput' => $post
            ]);
        }

        $user = DB::queryFirstRow(
            "SELECT * FROM users WHERE email=%s",$post['email']
        );

        if(empty($user)){
            DB::insert('users',$post);
            return $response->withHeader('Location','/');        
        }

        $view = Twig::fromRequest($request);

        return $view->render($response,'auth/register.html.twig',[
            'errorList' => $errorList,
            'prevInput' => $post
        ]);
    }



}