<?php

use PHPUnit\Framework\TestCase;
use Slim\Factory\ServerRequestCreatorFactory;
use Slim\Psr7\Factory\ServerRequestFactory;

require('vendor/autoload.php');

class UserControllerTest extends TestCase
{
    protected $client;
    protected $faker;
    protected $session;

    /**
     * This method is called before the first test of this test class is run.
     */
    public static function setUpBeforeClass(): void
    {
        DB::$user = 'carrental';
        DB::$password = 'QFzyVmD4mYO8Ah8G';
        DB::$dbName = 'carrental';
        DB::$port = 3333;
    }

    /**
     * This method is called after the last test of this test class is run.
     */
    public static function tearDownAfterClass(): void
    {
    }

    protected function setUp():void
    {
        $this->faker = Faker\Factory::create();
        $this->session = new GuzzleHttp\Cookie\CookieJar;
        $this->client = new GuzzleHttp\Client([
            'base_uri' => 'http://project.ipd20:8888',
            'http_errors' => false,
            'cookies' => true
        ]);
    }

    protected function tearDown(): void
    {
        DB::query("DELETE FROM users");
    }


    /**
     * @test
     */
    public function guestCanRegister() {
        $johnDoe = [
            'firstname' => 'john',
            'lastname' => 'doe',
            'drivinglicense' => 'D1234-12345-12345',
            'address' => '12-1, rue Montreal',
            'phone' => '(555)555-5555',
            'role' => 'user',
            'email' => 'johndoe@example.com',
            'password' => 'q1w2E3',
            'confirm' => 'q1w2E3'
        ];

        $response = $this->client->post('/register', [
            'form_params' =>$johnDoe
        ]);

        $this->assertEquals(200, $response->getStatusCode());

        $countJohnDoe = DB::queryFirstField("SELECT COUNT(*) FROM users WHERE email=%s",$johnDoe['email']);

        $this->assertEquals(1, $countJohnDoe);
    }

    private function createMultipart($user, $photoPath){
        $multipart = [];

        foreach($user as $key => $value){
            array_push(
                $multipart, 
                [
                    'name' => $key,
                    'contents' => $value,
                ]
            );
        }
        
        array_push(
            $multipart,
            [
                'name' => 'idPhoto',
                'contents' => fopen($photoPath,'r')
            ]
        );
        return $multipart;
    }

    /**
     * @test
     */
    public function guestCanRegisterWithPhoto() {
        $janeDoe = [
            'firstname' => 'jane',
            'lastname' => 'doe',
            'drivinglicense' => 'D1234-12345-12345',
            'address' => '12-1, rue Montreal',
            'phone' => '(555)555-5555',
            'role' => 'user',
            'email' => 'janedoe@example.com',
            'password' => 'q1w2E3',
            'confirm' => 'q1w2E3'
        ];

        $multipart = $this->createMultipart($janeDoe,__DIR__. '/../tmp/test.jpg');
        $response = $this->client->post('/register', [
            'multipart' => $multipart
            ]
        );

        $this->assertEquals(200, $response->getStatusCode());

        $countJaneDoe = DB::queryFirstField("SELECT COUNT(*) FROM users WHERE email=%s",$janeDoe['email']);

        $this->assertEquals(1, $countJaneDoe);
    }

    /**
     *  @test 
    */
    public function onlyRegisteredUserCanLogin(){ // FIXME : write better test
        $johnDoe = [
            'firstname' => 'john',
            'lastname' => 'doe',
            'drivinglicense' => 'D1234-12345-12345',
            'address' => '12-1, rue Montreal',
            'phone' => '(555)555-5555',
            'role' => 'user',
            'email' => 'johndoe@example.com',
            'password' => 'q1w2E3',
            'confirm' => 'q1w2E3'
        ];
        $this->client->post('/register', [
            'form_params' =>$johnDoe
        ]);


        $cookie = new GuzzleHttp\Cookie\CookieJar;
        $johnResponse = $this->client->post('/login',[
            'cookies' => $cookie,
            'form_params' => [
                'email' => 'johndoe@example.com',
                'password' => 'q1w2E3'
            ]
        ]);

        $this->assertEquals(200, $johnResponse->getStatusCode());
        $path = $cookie->getCookieByName('PHPSESSID')->getPath();
        $this->assertEquals('/',$path);

        $janeResponse = $this->client->post('/login',[
            'cookies' => $cookie,
            'form_params' => [
                'email' => 'janeDoe@example.com',
                'password' => 'q1w2E3'
            ]
        ]);

        $this->assertEquals(200, $janeResponse->getStatusCode());
        $path = $cookie->getCookieByName('PHPSESSID')->getPath();
        $this->assertEquals('/',$path);
    }

    
    /** 
     * @test 
     * */
    public function userCanSeeOnlyItsProfile(){
        $johnDoe = [
            'email' => 'johndoe@example.com',
            'password' => 'q1w2E3',
        ];
        $this->createUser($johnDoe);
        $johnId = $this->getUserId($johnDoe['email']);
        
        $janeDoe = [
            'email' => 'janedoe@example.com',
            'password' => 'q1w2E3'
        ];
        $this->createUser($janeDoe);
        $janeId = $this->getUserId($janeDoe['email']);

        $janeResponse = $this->client->post('/login',[
            'form_params' => $janeDoe
        ]);
        
        $janeResponse = $this->client->request('GET','/user/' . $johnId);
        $this->assertEquals(403,$janeResponse->getStatusCode());

        $janeResponse = $this->client->request('GET','/user/' . $janeId);
        $this->assertEquals(200,$janeResponse->getStatusCode());
    }







    private function createUser($user){
        $newUser['firstname'] = isset($user['firstname'])
                        ? $user['firstname'] : $this->faker->firstName;
        $newUser['lastname'] = isset($user['lastname'])
                        ? $user['lastname'] : $this->faker->lastName;
        $newUser['drivinglicense'] = isset($user['drivinglicense'])
                        ? $user['drivinglicense'] 
                        : $this->faker->regexify('[A-Z][0-9]{4}-[0-9]{5}-[0-9]{5}');
        $newUser['address'] = isset($user['address'])
                        ? $user['address'] : $this->faker->streetAddress;
        $newUser['phone'] = isset($user['phone'])
                        ? $user['phone'] : $this->faker->regexify('([0-9]{3})[0-9]{3}-[0-9]{4}');
        $newUser['role'] = isset($user['role']) ? $user['role'] : 'user';

        $newUser['email'] = isset($user['email'])
                        ? $user['email'] : $this->faker->email;
        $newUser['password'] = isset($user['password'])
                        ? $user['password'] : 'q1w2E3';
        $newUser['confirm'] = isset($user['confirm'])
                        ? $user['confirm'] : 'q1w2E3';

        $response = $this->client->post('/register', [
            'form_params' =>$newUser
        ]);
    }

    private function getUserId($email){
        return DB::queryFirstField("SELECT id FROM users WHERE email =%s",$email);
    }
}