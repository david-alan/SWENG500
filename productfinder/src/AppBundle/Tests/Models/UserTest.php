<?php
namespace AppBundle\Tests\Models;

use AppBundle\Entity\User;

class UserTest extends \PHPUnit_Framework_TestCase
{
    //test getter/setter for userName field
    public function testUserName()
    {
        $expectedUserName = 'sweng500user';
        $this->user->setUserName($expectedUserName);

        $this->assertEquals($expectedUserName, $this->user->getUserName());
    }

    //this function runs before each test runs
    public function setUp()
    {
        $this->user = new User();
    }

}
