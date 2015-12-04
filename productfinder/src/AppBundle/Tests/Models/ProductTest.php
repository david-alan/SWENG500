<?php
namespace AppBundle\Tests\Models;

use AppBundle\Entity\Product;

class ProductTest extends \PHPUnit_Framework_TestCase
{
    //test getter/setter for Name field
    public function testUserName()
    {
        $expectedName = 'headphones';
        $this->product->setName($expectedName);

        $this->assertEquals($expectedName, $this->product->getName());
    }

    //this function runs before each test runs
    public function setUp()
    {
        $this->product = new Product();
    }
}
