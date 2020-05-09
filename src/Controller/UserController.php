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
        $newUser['role'] = 'user';
        $photo = $request->getUploadedFiles();
        $errorList = UserValidator::getValidationErrorList($newUser);

        // Photo
        $idPhotoBase64 = null;
        if(v::key('idPhoto')->validate($photo)
            && $photo['idPhoto']->getSize() != 0){

            $idPhoto = $photo['idPhoto'];
            $photoBinary = $this->photoFileToBinary($newUser['email'],$idPhoto);

            $newUser['idPhoto'] = $photoBinary;

            $idPhotoBase64 = 'data:' . $idPhoto->getClientMediaType() . ';base64,' . base64_encode($photoBinary);
        }else if(v::key('idPhotoBase64',v::notEmpty())->validate($newUser)){
            $idPhotoBase64 = $newUser['idPhotoBase64'];
            $newUser['idPhoto'] = $this->base64ToBinary($idPhotoBase64);
        }

        unset($newUser['idPhotoBase64']);

        if(!empty($errorList)) 
        {
            return $view->render($response,'register.html.twig',[
                'errorList' => $errorList,
                'prevInput' => $newUser,
                'idPhotoBase64' => $idPhotoBase64
            ]);
        }

        $user = DB::queryFirstRow(
            "SELECT * FROM users WHERE email=%s",$newUser['email']
        );

        if(empty($user)){
            unset($newUser['confirm']);

            DB::insert('users',$newUser);
            return $response->withHeader('Location','/');        
        }


        $errorList['email'] = 'Email already exists';

        return $view->render($response,'register.html.twig',[
            'errorList' => $errorList,
            'prevInput' => $newUser,
            'idPhotoBase64' => $idPhotoBase64
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

    private function photoFileToBinary($email, $idPhoto){
        if($this->isValidPhoto($idPhoto))
        {
            $tmpPath = __DIR__ . '/../../tmp/' . $email . '.' ;

            if($idPhoto->getClientMediaType() == 'image/png'){
                $tmpPath .= 'png';
            } else {
                $tmpPath .= 'jpg';
            }

            $idPhoto->moveTo($tmpPath);
            $binaryImg = file_get_contents($tmpPath);
            unlink($tmpPath);

            return $binaryImg;
        }
    }

    private function base64ToBinary($base){
        $data = explode(';base64,',$base,2);
        return base64_decode($data[1]);
    }

}