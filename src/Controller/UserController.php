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
            $photoBinary = $this->photoFileToBinary($idPhoto);

            $newUser['idPhoto'] = $photoBinary;

            $idPhotoBase64 = 'data:image/png;base64,' . base64_encode($photoBinary);
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
            
            $userId = DB::insertId();

            DB::replace('userSessions',[
                'sessionId' => session_id(),
                'userId' => $userId,
                'updated_at' => date('Y-m-d H:i:s',time())
            ]);

            return $response->withHeader('Location','/');        
        }


        $errorList['email'] = 'Email already exists';

        return $view->render($response,'register.html.twig',[
            'errorList' => $errorList,
            'prevInput' => $newUser,
            'idPhotoBase64' => $idPhotoBase64
        ]);
    }

    public function update(Request $request,Response $response, array $args){
        $id = $args['id'];

        if(!$this->isUserAuthenticated($id)){
            return $response->withHeader('Location','/errors/forbidden');
        }

        $view = Twig::fromRequest($request);
        $newUser = $request->getParsedBody();
        $newUser['role'] = 'user';
        $photo = $request->getUploadedFiles();
        $errorList = UserValidator::getValidationErrorList($newUser,false);

        // Photo
        $idPhotoBase64 = null;
        if(v::key('idPhoto')->validate($photo)
            && $photo['idPhoto']->getSize() != 0){

            $idPhoto = $photo['idPhoto'];
            $photoBinary = $this->photoFileToBinary($idPhoto);

            $newUser['idPhoto'] = $photoBinary;

            $idPhotoBase64 = 'data:image/png;base64,' . base64_encode($photoBinary);
        }else if(v::key('idPhotoBase64',v::notEmpty())->validate($newUser)){
            $idPhotoBase64 = $newUser['idPhotoBase64'];
            $newUser['idPhoto'] = $this->base64ToBinary($idPhotoBase64);
        }

        unset($newUser['idPhotoBase64']);

        if(!empty($errorList)) 
        {
            return $view->render($response,'profile.html.twig',[
                'errorList' => $errorList,
                'user' => $newUser,
                'idPhotoBase64' => $idPhotoBase64
            ]);
        }

        DB::update('users', $newUser, 'id=%s',$id);

        return $view->render($response, 'edit_profile_succeed.html.twig');
    }

    
    public function show (Request $request,Response $response, array $args){
        $id = $args['id'];

        if(!$this->isUserAuthenticated($id)){
            return $response->withHeader('Location','/errors/forbidden');
        }

        $user = DB::queryFirstRow("SELECT * FROM users WHERE id=%s", $id);

        $idPhotoBase64 = isset($user['idPhoto']) ? 'data:image/png;base64,' . base64_encode($user['idPhoto']) : '';

        $view = Twig::fromRequest($request);
        return $view->render($response, 'profile.html.twig',[
            'user' => [
                'id' => $user['id'],
                'email' => $user['email'],
                'name' => $user['firstname'],
                'firstname' => $user['firstname'],
                'lastname' => $user['lastname'],
                'drivinglicense' => $user['drivinglicense'],
                'address' => $user['address'],
                'phone' => $user['phone']
            ],
            'idPhotoBase64' => $idPhotoBase64,
        ]);
    }



    /**
     * Helper Methods
     */

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

    private function photoFileToBinary($idPhoto){
        if($this->isValidPhoto($idPhoto))
        {
            $tmpPath = __DIR__ . '/../../tmp/'. session_id() .'.' ;

            if($idPhoto->getClientMediaType() == 'image/png'){
                $tmpPath .= 'png';
            } else {
                $tmpPath .= 'jpg';
            }

            $idPhoto->moveTo($tmpPath);
            $imgResource = imageCreateFromString(file_get_contents($tmpPath));
            $newImage = imagescale($imgResource,525,300);

            ob_start();
            imagepng($newImage);
            $binaryImg = ob_get_clean(); 

            unlink($tmpPath);

            return $binaryImg;
        }
    }

    private function base64ToBinary($base){
        $data = explode(';base64,',$base,2);
        return base64_decode($data[1]);
    }

    private function isUserAuthenticated($targetUserId){
        // 2 mins max
        $expiredTime = date('Y-m-d H:i:s',time() - 60 * 2);

        $userId = DB::queryFirstField(
                    "SELECT userId 
                    FROM userSessions
                    WHERE sessionId = %s
                    AND updated_at > %s", 
                    session_id(), $expiredTime);
        
        return $targetUserId === $userId;
    }
}