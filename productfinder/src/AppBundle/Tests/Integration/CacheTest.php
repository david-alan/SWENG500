<?php

namespace Tests\Integration;

use Doctrine\ORM\EntityManager;
use AppBundle\Services\CacheService;

class CacheTest extends \PHPUnit_Framework_TestCase
{
    public function testAddCache() {
        $json = file_get_contents(__DIR__ . '/../Fixtures/testData.json');

        $this->em->expects($this->exactly(2))
            ->method('persist')
            ->will($this->returnValue(NULL));

        $this->em->expects($this->exactly(2))
            ->method('flush')
            ->will($this->returnValue(NULL));

        $this->cache->addCache($json);
    }

    public function setUp()
    {
        $this->em = $this->getMockBuilder('Doctrine\ORM\EntityManager')
            ->disableOriginalConstructor()
                ->getMock();

        $this->cache = new CacheService($this->em);
    }
}
