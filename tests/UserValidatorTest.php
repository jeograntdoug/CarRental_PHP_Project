<?php

use PHPUnit\Framework\TestCase;
use App\UserValidator;

class UserValidatorTest extends TestCase
{
    private $faker;
    private $v;

    protected function setUp():void
    {
        $this->faker = Faker\Factory::create();
        $this->v = new UserValidator();
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
    public function emptyUserIsNotValid(){
        $emptyUser = [
            [
                'firstname' => null,
                'lastname' => null,
                'drivinglicense' => null,
                'address' => null,
                'phone' => null,
                'role' => null,
                'email' => null,
                'password' => null,
                'confirm' => null
            ]
        ];

        $errorList = $this->v->isValidUser($emptyUser);
        $this->assertEquals(9,count($errorList));

        $errorList = $this->v->isValidUser([]);
        $this->assertEquals(9,count($errorList));

        $errorList = $this->v->isValidUser(null);
        $this->assertEquals(9,count($errorList));

        $errorList = $this->v->isValidUser('');
        $this->assertEquals(9,count($errorList));
    }

    /** @test */
    public function isValidUserName(){
        $invalidFieldList = [
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

        foreach($invalidFieldList as $user){
            $errorList = $this->v->isValidUser($user, false);
            $this->assertArrayHasKey('firstName',$errorList);
            $this->assertArrayHasKey('lastName',$errorList);
            $this->assertEquals(2,count($errorList));
        }
    }

    /** @test */
    public function isValidUserDrivingLicense(){
        $invalidFieldList = [
            [ 'drivinglicense' => '' ], 
            [ 'drivinglicense' => null ], 
            [ 'drivinglicense' => '12345-67890-12345'], 
            [ 'drivinglicense' => 'a2345-67890-12345'], 
        ];

        foreach($invalidFieldList as $user){
            $errorList = $this->v->isValidUser($user, false);
            $this->assertEquals(1,count($errorList));
            $this->assertArrayHasKey('drivinglicense',$errorList);
        }
    }

    /** @test */
    public function isValidUserAddress(){
        $invalidFieldList = [
            [ 'address' => '' ], 
            [ 'address' => null ], 
        ];

        foreach($invalidFieldList as $user){
            $errorList = $this->v->isValidUser($user, false);
            $this->assertEquals(1,count($errorList));
            $this->assertArrayHasKey('address',$errorList);
        }
    }

    /** @test */
    public function isValidUserEmail(){
        $invalidFieldList = [
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

        foreach($invalidFieldList as $user){
            $errorList = $this->v->isValidUser($user, false);
            $this->assertEquals(1,count($errorList));
            $this->assertArrayHasKey('email',$errorList);
        }
    }

    /** @test */
    public function isValidUserPhone(){
        $invalidFieldList = [
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

        foreach($invalidFieldList as $user){
            $errorList = $this->v->isValidUser($user, false);
            $this->assertEquals(1,count($errorList));
            $this->assertArrayHasKey('phone',$errorList);
        }
    }


    /** @test */
    public function isValidUserRole(){
        $invalidFieldList = [
            [ 'role' => '' ], 
            [ 'role' => null ],
            [ 'role' => 'hahahah' ], 
        ];

        foreach($invalidFieldList as $user){
            $errorList = $this->v->isValidUser($user, false);
            $this->assertEquals(1,count($errorList));
            $this->assertArrayHasKey('role',$errorList);
        }
    }

    /** @test */
    public function isValidUserPassword(){
        $invalidFieldList = [
            [ 
                'password' => '',
                'confirm' => '',
            ], 
            [
                'password' => null,
                'confirm' => null,
            ],
            [
                'password' => null,
                'confirm' => 'Hahahah',
            ],
            [
                'password' => 'hahahha',
                'confirm' => '',
            ],
            [ 
                'password' => ' Asdf123',
                'confirm' => 'Hahahah',
            ], 
            [ 
                'password' => '#Asdf123',
                'confirm' => 'Hahahah',
            ], 
        ];

        foreach($invalidFieldList as $user){
            $errorList = $this->v->isValidUser($user, false);
            $this->assertArrayHasKey('password',$errorList);
            $this->assertArrayHasKey('confirm',$errorList);
            $this->assertEquals(2,count($errorList));
        }


        $invalidFieldList = [
            [ 
                'confirm' => '',
            ], 
            [
                'confirm' => null,
            ],
            [
                'password' => 'Hahahah',
                'confirm' => null,
            ],
            [
                'password' => 'Hahahah',
                'confirm' => '',
            ],
            [
                'password' => 'Hahahah',
                'confirm' => 'Hohohoh',
            ],
        ];

        foreach($invalidFieldList as $user){
            $errorList = $this->v->isValidUser($user, false);
            $this->assertArrayHasKey('confirm',$errorList);
            $this->assertEquals(1,count($errorList));
        }
    }

}