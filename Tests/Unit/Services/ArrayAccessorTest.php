<?php

namespace Pouzor\MongoDBBundle\Tests\Unit\Services;

use Pouzor\MongoDBBundle\Services\ArrayAccessor;

class ArrayAccessorTest extends \PHPUnit_Framework_TestCase
{

    public function testReplaceKey()
    {
        $arrayTest = ["foo" => "bar", "foo2" => "bar2", "foo3" => "bar3"];

        ArrayAccessor::replaceKey($arrayTest, "foo", "foo4");

        $this->assertArrayHasKey("foo4", $arrayTest);
        $this->assertArrayNotHasKey("foo", $arrayTest);

        ArrayAccessor::replaceKey($arrayTest, "foo", "foo5");

        $this->assertArrayHasKey("foo4", $arrayTest);
        $this->assertArrayNotHasKey("foo", $arrayTest);
        $this->assertArrayNotHasKey("foo5", $arrayTest);

    }

    public function testDget()
    {

        $arraytest = [
            'person' => [
                'name' => 'john',
                'lastname' => 'smith',
                'childs' => [
                    ['name' => 'victor'],
                    ['name' => 'david']
                ]
            ]
        ];

        $result = ArrayAccessor::dget($arraytest, "person.name", "nope");
        $this->assertEquals($result, 'john');

        $result = ArrayAccessor::dget($arraytest, "person.foo", "bar");
        $this->assertEquals($result, 'bar');

    }

}