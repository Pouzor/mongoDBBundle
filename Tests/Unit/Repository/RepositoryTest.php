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
        "host" => "localhost",
        "port" => "27017",
        "username" => null,
        "password" => null,
        "schema" => ['Foo' => ['indexes' => ["bar" => -1]]],
        "options" => []
    ];

    protected function setUp()
    {
        $logger = new NullLogger();
        $this->manager = new DocumentManager($this->config, $logger);
        $this->repository = $this->manager->getRepository("Foo");
        $this->manager->removeAll("Foo");

    }

    protected function tearDown()
    {
        $this->manager = null;
    }

    public function testGetRepository()
    {

        $this->assertEquals(get_class($this->repository), 'Pouzor\MongoDBBundle\Repository\Repository');
        $this->assertEquals("Foo", $this->repository->getName());

        $indexes = $this->repository->getIndexes();
        $this->assertArrayHasKey("bar", $indexes);
        $this->assertEquals(-1, $indexes['bar']);
    }

    public function testInsertMany()
    {
        $datas = [
            [
                "name" => "foo",
                "value" => 1
            ],
            [
                "name" => "bar",
                "value" => 2
            ]
        ];

        $this->assertEquals(0, $this->repository->count());
        $this->repository->insertMany($datas);

        $this->assertEquals(2, $this->repository->count());

        $foo = $this->repository->findOneBy(["name" => "foo"]);
        $this->assertEquals(1, $foo['value']);

        $bar = $this->repository->findOneBy(["name" => "bar"]);
        $this->assertEquals(2, $bar['value']);

    }

    public function testUpdate()
    {
        $data = [
            "name" => "foo",
            "value" => 1
        ];

        $test = $this->repository->insertOne($data);

        $this->assertEquals(1, $this->repository->count());
        $this->repository->update($test->getInsertedId(), ['$set' => ["value" => 2]]);

        $newValue = $this->repository->find($test->getInsertedId());

        $this->assertEquals(2, $newValue["value"]);
        $this->assertEquals(1, $this->repository->count());
    }

    public function testUpdateMany()
    {
        $datas = [
            [
                "name" => "foo",
                "value" => 1
            ],
            [
                "name" => "bar",
                "value" => 2
            ],
            [
                "name" => "foo",
                "value" => 3
            ]
        ];

        $this->repository->insertMany($datas);
        $this->assertEquals(1, $this->repository->count(['name' => 'foo', 'value' => 3]));

        $this->repository->updateMany(['name' => 'foo'], ['$set' => ['value' => 3]]);
        $this->assertEquals(2, $this->repository->count(['name' => 'foo', 'value' => 3]));

    }

    public function testReplaceOne()
    {
        $datas = [
            [
                "name" => "foo",
                "value" => 1
            ],
            [
                "name" => "bar",
                "value" => 2
            ],
            [
                "name" => "foo",
                "value" => 3
            ]
        ];

        $this->repository->insertMany($datas);
        $this->assertEquals(1, $this->repository->count(['name' => 'foo', 'value' => 3]));
        $this->repository->replaceOne(['name' => 'foo'], ["name" => "foo", "value" => 5]);

        $this->assertEquals(1, $this->repository->count(["name" => "foo", "value" => 5]));
    }

    public function testDelete()
    {
        $data = [
            "name" => "foo",
            "value" => 1
        ];

        $test = $this->repository->insertOne($data);
        $this->assertEquals(1, $this->repository->count());

        $return = $this->repository->delete($test->getInsertedId());
        $this->assertEquals(0, $this->repository->count());
        $this->assertEquals(1, $return->getDeletedCount());
        $return = $this->repository->delete($test->getInsertedId());

        $this->assertEquals(0, $return->getDeletedCount());

    }

    public function testDeleteOne()
    {
        $datas = [
            [
                "name" => "foo",
                "value" => 1
            ],
            [
                "name" => "bar",
                "value" => 2
            ],
            [
                "name" => "foo",
                "value" => 3
            ]
        ];

        $this->repository->insertMany($datas);
        $this->assertEquals(2, $this->repository->count(['name' => 'foo']));

        $this->repository->deleteOne(['name' => 'foo']);
        $this->assertEquals(1, $this->repository->count(['name' => 'foo']));
    }

    public function testDeleteMany()
    {
        $datas = [
            [
                "name" => "foo",
                "value" => 1
            ],
            [
                "name" => "bar",
                "value" => 2
            ],
            [
                "name" => "foo",
                "value" => 3
            ]
        ];

        $this->repository->insertMany($datas);
        $this->assertEquals(2, $this->repository->count(['name' => 'foo']));

        $this->repository->deletemany(['name' => 'foo']);
        $this->assertEquals(0, $this->repository->count(['name' => 'foo']));
    }

    public function testAggregate()
    {
        $datas = [
            [
                "name" => "foo",
                "value" => 1
            ],
            [
                "name" => "bar",
                "value" => 2
            ],
            [
                "name" => "foo",
                "value" => 3
            ]
        ];

        $this->repository->insertMany($datas);

        $result = $this->repository->aggregate(
            [
                [
                    '$match' => [
                        "name" => "bar"
                    ]
                ]
            ]
        );

        $i = 0;
        //In specific version of mongodb driver, $result->toArray() doesn't work, nor count
        foreach ($result as $data) {
            $i++;
        }

        $this->assertEquals(1, $i);

    }

    public function testMin() {
        $datas = [
            [
                "name" => "foo",
                "value" => 10
            ],
            [
                "name" => "bar",
                "value" => 5
            ],
            [
                "name" => "foo",
                "value" => 30
            ]
        ];

        $this->repository->insertMany($datas);

        $result = $this->repository->min("value");
        $this->assertEquals(5, $result);

        $result = $this->repository->min("value", ["name" => "foo"]);
        $this->assertEquals(10, $result);
    }

    public function testMax() {
        $datas = [
            [
                "name" => "foo",
                "value" => 10
            ],
            [
                "name" => "bar",
                "value" => 5
            ],
            [
                "name" => "foo",
                "value" => 30
            ]
        ];

        $this->repository->insertMany($datas);

        $result = $this->repository->max("value");
        $this->assertEquals(30, $result);

        $result = $this->repository->max("value", ["name" => "bar"]);
        $this->assertEquals(5, $result);
    }

}