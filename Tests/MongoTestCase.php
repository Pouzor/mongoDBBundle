<?php

namespace Pouzor\MongoDBBundle\Tests;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Pouzor\MongoDBBundle\Tests\App\AppKernel;
use Pouzor\MongoDBBundle\DocumentManager\DocumentManager;
use Psr\Log\NullLogger;

class MongoTestCase extends WebTestCase
{

    public $testRepo;
    public $manager;
    public $k;
    public $container = null;

    private $config = [
        "db" => "mongodbbundle",
        "host" => "localhost",
        "port" => "27017",
        "username" => null,
        "password" => null,
        "schema" => ['Foo' => ['indexes' => ["bar" => -1]]],
        "options" => []
    ];

    /**
     * Setup the test class
     */
    public function setUp()
    {

        $this->k = new AppKernel('test', true);
        $this->k->boot();
        $this->container = $this->k->getContainer();

        $logger = new NullLogger();
        $this->manager = new DocumentManager($this->config, $logger);


        $this->testRepo = $this->manager->getRepository('Foo');
        $this->testRepo->deleteMany([], []);
        $this->testRepo->dropIndexes();

    }

}
