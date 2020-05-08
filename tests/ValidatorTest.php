<?php

use PHPUnit\Framework\TestCase;
use App\Validator;

class ValidatorTest extends TestCase
{
    private $faker;
    private $v;

    protected function setUp():void
    {
        $this->faker = Faker\Factory::create();
        $this->v = new Validator();
    }

    /** @test */
    public function validateUserfields(){
        $userA = [
            'firstname' => 'John',
            'lastname' => 'Doe',
            'drivinglicense' => 'D1234-12345-12345',
            'address' => '12-1, rue Montreal',
            'phone' => '(555)555-5555',
            'role' => 'user',
            'email' => 'johnDoe@example.com',
            'password' => 'q1w2E3',
            'confirm' => 'q1w2E3'
        ];

        $this->assertEmpty($this->v->isValidUser($userA));
    }

    /** @test */
    public function isValidUserName(){
        $invalidUserList = [
            [ 
                'firstname' => '' ,
                'lastname' => '' ,
            ], 
            [ 
                'firstname' => null,
                'lastname' => null,
            ], 
            [ 
                'firstname' => 'asdf123',
                'lastname' => 'asdf123' ,
            ], 
            [ 
                'firstname' => ' asdf234', 
                'lastname' => ' asdf234', 
            ],
            [ 
                'firstname' => '! Asdf234', 
                'lastname' => '! Asdf234', 
            ]
        ];

        foreach($invalidUserList as $user){
            $errorList = $this->v->isValidUser($user, false);
            $this->assertEquals(2,count($errorList));
            $this->assertArrayHasKey('firstName',$errorList);
            $this->assertArrayHasKey('lastName',$errorList);
        }
    }

    /** @test */
    public function isValidUserDrivingLicense(){
        $invalidUserList = [
            [ 'drivinglicense' => '' ], 
            [ 'drivinglicense' => null ], 
            [ 'drivinglicense' => '12345-67890-12345'], 
            [ 'drivinglicense' => 'a2345-67890-12345'], 
        ];

        foreach($invalidUserList as $user){
            $errorList = $this->v->isValidUser($user, false);
            $this->assertEquals(1,count($errorList));
            $this->assertArrayHasKey('drivinglicense',$errorList);
        }
    }

    /** @test */
    public function isValidUserAddress(){
        $invalidUserList = [
            [ 'address' => '' ], 
            [ 'address' => null ], 
        ];

        foreach($invalidUserList as $user){
            $errorList = $this->v->isValidUser($user, false);
            $this->assertEquals(1,count($errorList));
            $this->assertArrayHasKey('address',$errorList);
        }
    }

    /** @test */
    public function isValidUserEmail(){
        $invalidUserList = [
            [ 'email' => '' ], 
            [ 'email' => null ], 
            [ 'email' => ' asdf.com'], 
            [ 'email' => 'asdf@.com '], 
            [ 'email' => 'a sdf@asdf.com '], 
            [ 'email' => '@asdf.com '], 
            [ 'email' => 'asdf@'], 
            [ 'email' => 'asdf@asdf.'], 
            [ 'email' => 'asdf@asdf..com'], 
            [ 'email' => 'asdf@asdf@asdf.com'], 
            [ 'email' => '.asdf@asdf.com'], 
            //[ 'email' => 'asdf.asdf@asdf.com'], //this is valid
            //[ 'email' => 'asdf@asdf.c.om'],  //this is valid
        ];

        foreach($invalidUserList as $user){
            $errorList = $this->v->isValidUser($user, false);
            $this->assertEquals(1,count($errorList));
            $this->assertArrayHasKey('email',$errorList);
        }
    }

    /** @test */
    public function isValidUserPhone(){
        $invalidUserList = [
            [ 'phone' => '' ], 
            [ 'phone' => null ], 
            [ 'phone' => '1-1-23-123-1234'], 
            [ 'phone' => '1-1234-2123-1234'], 
            // [ 'phone' => '(123)21231234'], 
            // [ 'phone' => '1(123)123-1234'], 
            // [ 'phone' => '+1(123)123-1234'], 
            //[ 'phone' => '1 123 123 1234'], 
            // [ 'phone' => '1-1231231234'], 
            // [ 'phone' => '11231231234'], 
            // [ 'phone' => '+11231231234'], 
            // [ 'phone' => '1-123-123-1234'], 
        ];

        foreach($invalidUserList as $user){
            $errorList = $this->v->isValidUser($user, false);
            $this->assertEquals(1,count($errorList));
            $this->assertArrayHasKey('phone',$errorList);
        }
    }

}