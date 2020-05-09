<?php

namespace App;

use Respect\Validation\Exceptions\NestedValidationException;
use Respect\Validation\Validator as v;

class UserValidator
{
    public static function getValidationErrorList($user, $forAll = true)
    {
        $errorList = [];
        if (!v::notEmpty()->validate($user)) {
            $errorList = [
                'firstname' => 'Must be 1~20 letters',
                'lastname' => 'Must be 1~20 letters',
                'drivinglicense' => 'Invlid Driving License',
                'address' => 'Must be 1~50 letters',
                'email' => 'Invalid Email address',
                'phone' => 'Invalid Phone number',
                'role' => 'Must be user or admin',
                'password' => 'At least one Upperletter and 1~100 long',
                'confirm' => 'Password is not matched'
            ];

            return $errorList;
        }


        // firstname
        if (!v::key('firstname', v::alpha(' ')->length(1, 20), $forAll)->validate($user)) {
            $errorList['firstname'] = 'Must be 1~20 letters';
        }

        // lastname
        if (!v::key('lastname', v::alpha(' ')->length(1, 20), $forAll)->validate($user)) {
            $errorList['lastname'] = 'Must be 1~20 letters';
        }

        // drivinglicense
        if (!v::key('drivinglicense', v::regex('/[A-Z][0-9]{4}-[0-9]{5}-[0-9]{5}/'), $forAll)->validate($user)) {
            $errorList['drivinglicense'] = 'Invlid Driving License';
        }

        // address
        if (!v::key('address', v::stringVal()->length(1, 50), $forAll)->validate($user)) {
            $errorList['address'] = 'Must be 1~50 letters';
        }

        // email
        if (!v::key('email', v::email()->length(1, 50), $forAll)->validate($user)) {
            $errorList['email'] = 'Invalid Email address';
        }

        // phone
        if (!v::key('phone', v::phone(), $forAll)->validate($user)) {
            $errorList['phone'] = 'Invalid Phone number';
        }

        // role
        if (!v::key('role', v::anyOf(v::equals('admin'), v::equals('user')), $forAll)->validate($user)) {
            $errorList['role'] = 'Must be user or admin';
        }


        // password
        if (!v::key(
            'password', 
            v::alnum()
                ->regex('/[A-Z]{1}/')
                ->length(1, 100), 
            $forAll
        )->validate($user)) {
            $errorList['password'] = 'At least one Upperletter and 1~100 long';
        }

        if (!v::key(
            'confirm',
            v::alnum()
                ->regex('/[A-Z]{1}/')
                ->length(1, 100),
            $forAll
        )->validate($user)) {
            $errorList['confirm'] = 'Invalid Confirm password';
        }

        if (
            v::key('password')->validate($user)
            && v::key('password')->validate($user)
            && !v::keyValue('confirm', 'equals', 'password')->validate($user)
        ) {
            $errorList['confirm'] = 'Password is not matched';
        }

        return $errorList;
    }
}
