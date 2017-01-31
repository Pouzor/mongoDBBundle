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



}