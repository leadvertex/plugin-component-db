<?php


namespace Test\Leadvertex\Plugin\Components\Db;


use Leadvertex\Plugin\Components\Db\Components\Limit;
use PHPUnit\Framework\TestCase;

class LimitTest extends TestCase
{
    public function testCreateLimit()
    {
        $limit = new Limit(5);
        $this->assertEquals(5, $limit->get());

        $limit = (new Limit(5, 2))->get();
        $this->assertIsArray($limit);
        $this->assertEquals(5, $limit[0]);
        $this->assertEquals(2, $limit[1]);
    }


}