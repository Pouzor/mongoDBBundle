<?php

namespace Pouzor\MongoDBBundle\Tests\Unit\Services;

use Pouzor\MongoDBBundle\Constants\DriverClasses;
use Pouzor\MongoDBBundle\Constants\Query;

class QueryTest extends \PHPUnit_Framework_TestCase
{

    public function testCreateDate()
    {
        $time = Query::createDate(null);
        $this->assertEquals(get_class($time), DriverClasses::DATE_CLASS);

        $timestamp = time();

        $time = Query::createDate($timestamp);
        $this->assertEquals(get_class($time), DriverClasses::DATE_CLASS);
        $this->assertEquals($time->toDateTime()->getTimestamp(), $timestamp);

        $time = Query::createDate("2017-01-01T00:00:00");
        $this->assertEquals(strtotime("2017-01-01T00:00:00"), $time->toDateTime()->getTimestamp());

    }

    /**
     * @expectedException Exception
     */
    public function testCreateDateException()
    {
        $this->expectException(Query::createDate("bla"));
    }
}