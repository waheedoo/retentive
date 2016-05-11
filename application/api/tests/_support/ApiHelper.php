<?php
namespace Codeception\Module;

// here you can define custom actions
// all public methods declared in helper class will be available in $I

class ApiHelper extends \Codeception\Module
{
    public function amAuthenticated($username = 'default_user') {
//        // $token = ...
//        $this
//            ->getModule('REST')
//            ->amBearerAuthenticated($token);
    }
}
