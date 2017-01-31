<?php

namespace Pouzor\MongoDBBundle\Tests\Unit\DocumentManager;

use Pouzor\MongoDBBundle\DocumentManager\DocumentManager;
use Psr\Log\NullLogger;

class RepositoryTest extends \PHPUnit_Framework_TestCase
{
    private $manager = null;
    private $repository = null;

    private $config = [
        "db" => "mongodbbundle",
        "host" =>  "localhost",
        "port" => "27017",
        "username" =>  null,
        "password" => null,
        "schema" => ['Foo' => ['indexes' => ["bar" => -1]]],
        "options" => []
    ];

    protected function setUp()
    {
        $logger = new NullLogger();
        $this->manager = new DocumentManager($this->config, $logger);
        $this->repository = $this->manager->getRepository("Foo");
    }

    protected function tearDown()
    {
        $this->manager = null;
    }

    public function testGetRepository()
    {

        $this->assertEquals(get_class($this->repository), 'Pouzor\MongoDBBundle\Repository\Repository');
        $this->assertEquals("Foo", $this->repository->getName());

        $this->repository->getIndexes();
    }



}