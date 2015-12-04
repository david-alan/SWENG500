<?php
namespace AppBundle\Tests\Models;

use AppBundle\Entity\User;

class UserTest extends \PHPUnit_Framework_TestCase
{
    //test getter/setter for email field
    public function testEmail()
    {
        $expectedEmail = 'sweng500user@test.com';
        $this->user->setEmail($expectedEmail);

        $this->assertEquals($expectedEmail, $this->user->getEmail());
    }

    //test getter/setter for userName field
    public function testPassword()
    {
        $expectedPassword = 'sweng500user@test.com';
        $this->user->setPassword($expectedPassword);

        $this->assertEquals($expectedPassword, $this->user->getPassword());
    }

    //this function runs before each test runs
    public function setUp()
    {
        $this->user = new User();
    }

}
