<?php
namespace App\Controller;

use App\UserValidator;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use DB;
use Respect\Validation\Validator as v;
use Slim\Views\Twig;

class UserController 
{
    private function isNameValid($name){
        return preg_match('/^[a-zA-Z _-]$/',$name) === 1;
    }
    
    public function create (Request $request,Response $response)
    {
        $view = Twig::fromRequest($request);
        $newUser = $request->getParsedBody();
        $photo = $request->getUploadedFiles();
        $errorList = UserValidator::getValidationErrorList($newUser);

        if(!empty($errorList)) 
        {
            return $view->render($response,'register.html.twig',[
                'errorList' => $errorList,
                'prevInput' => $newUser
            ]);
        }

        $user = DB::queryFirstRow(
            "SELECT * FROM users WHERE email=%s",$newUser['email']
        );

        if(empty($user)){
            unset($newUser['confirm']);

            if(v::key('idPhoto')->validate($photo)){

                $idPhoto = $photo['idPhoto'];
                $this->setPhoto($newUser,$idPhoto);
            }

            DB::insert('users',$newUser);
            return $response->withHeader('Location','/');        
        }

        return $view->render($response,'register.html.twig',[
            'errorList' => $errorList,
            'prevInput' => $newUser
        ]);
    }


    private function isValidPhoto($photo){
        if(!v::notEmpty()->validate($photo)){
            return false;
        }

        if(!v::numericVal()
            ->between(10*1000,1000*1000)
            ->validate($photo->getSize()))
        {
            return false;
        }

        if(!v::anyOf(
            v::equals('image/jpeg'),
            v::equals('image/jpg'),
            v::equals('image/png')
            )->validate($photo->getClientMediaType())
        ){
            return false;
        }

        return true;
    }

    private function setPhoto($newUser,$idPhoto){
        if($this->isValidPhoto($idPhoto))
        {
            $tmpPath = __DIR__ . '/../../tmp/' . $newUser['email'] . '.' ;

            if($idPhoto->getClientMediaType() == 'image/png'){
                $tmpPath .= 'png';
            } else {
                $tmpPath .= 'jpg';
            }

            $idPhoto->moveTo($tmpPath);
            $newUser['idPhoto'] = file_get_contents($tmpPath);
            //TODO : delete photo from folder
        }
    }

}