<?php

namespace Pouzor\MongoDBBundle\Tests\Unit\DocumentManager;

use Pouzor\MongoDBBundle\DocumentManager\DocumentManager;
use Psr\Log\NullLogger;

class DocumentManagerTest extends \PHPUnit_Framework_TestCase
{
    private $manager = null;

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
        $this->manager->removeAll("Foo");
        $this->manager->removeAll("FooFind");
    }

    protected function tearDown()
    {
        $this->manager = null;
    }

    public function testGetRepository()
    {
        $repository = $this->manager->getRepository("Foo");
        $this->assertEquals(get_class($repository), 'Pouzor\MongoDBBundle\Repository\Repository');
    }

    public function testFind() {
        $repository = $this->manager->getRepository("FooFind");

        $test = $repository->insertOne(['name' => 'test', "value" => 1]);

        $result = $this->manager->find("FooFind", $test->getInsertedId());

        $this->assertNotNull($result);
        $this->assertEquals(1, $result['value']);

    }

    public function testRemoveAll() {
        $repository = $this->manager->getRepository("FooFind");
        $repository->insertOne(['name' => 'test', "value" => 1]);
        
        $this->assertEquals(1, $repository->count());
        $this->manager->removeAll("FooFind");
        $this->assertEquals(0, $repository->count());

    }


}