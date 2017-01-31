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

        $result = ArrayAccessor::dget($arraytest, "person.childs.0.name", "bar");
        $this->assertEquals($result, 'victor');
        $result = ArrayAccessor::dget($arraytest, "person.childs.1.name", "bar");
        $this->assertEquals($result, 'david');
    }


    public function testDset() {
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

        ArrayAccessor::dset($arraytest, "person.name", "doe");
        $this->assertEquals($arraytest['person']['name'], 'doe');

        ArrayAccessor::dset($arraytest, "person.childs.0.name", "doom");
        $this->assertEquals($arraytest['person']['childs'][0]["name"], 'doom');

        ArrayAccessor::dset($arraytest, "person.childs.3.name", "birst");
        $this->assertEquals($arraytest['person']['childs'][3]["name"], 'birst');

    }

    public function testDcount() {
        $arraytest = [
            'person' => [
                'name' => 'john',
                'lastname' => 'smith',
                'childs' => [
                    ['name' => 'victor'],
                    ['name' => 'david']
                ]
            ],
            'work' => "none"
        ];

        $value = ArrayAccessor::dcount($arraytest, "person");
        $this->assertEquals(3, $value);

        $value = ArrayAccessor::dcount($arraytest, "person.childs");
        $this->assertEquals(2, $value);

        $value = ArrayAccessor::dcount($arraytest, "person.nope");
        $this->assertNull($value);

        $value = ArrayAccessor::dcount($arraytest, "person.childs.3");
        $this->assertNull($value);

        $value = ArrayAccessor::dcount($arraytest, "person.name.0");
        $this->assertNull($value);

    }

    public function testDdel() {
        $arraytest = [
            'person' => [
                'name' => 'john',
                'lastname' => 'smith',
                'childs' => [
                    ['name' => 'victor'],
                    ['name' => 'david']
                ]
            ],
            'work' => "none"
        ];

        ArrayAccessor::ddel($arraytest, "person.childs.2");
        $this->assertArrayNotHasKey(2, $arraytest['person']['childs']);

        ArrayAccessor::ddel($arraytest, "person.name.0");
        $this->assertFalse(is_array($arraytest['person']['name']));
    }

    public function testGetKeyExists() {
        $arraytest = [
            'person' => 'john'
        ];

        $this->assertEquals(ArrayAccessor::get_key_exist($arraytest, "person"), 'john');
        $this->assertEquals(ArrayAccessor::get_key_exist($arraytest, "work", 'nope'), 'nope');
        
    }

}