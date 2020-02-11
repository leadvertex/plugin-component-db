<?php


namespace Test\Leadvertex\Plugin\Components\Db;


use InvalidArgumentException;
use Leadvertex\Plugin\Components\Db\Components\Sort;
use PHPUnit\Framework\TestCase;

class SortTest extends TestCase
{
    public function testCreateSort()
    {
        $sort = new Sort(Sort::BY_CREATED_AT, Sort::DESC);
        $this->assertArrayHasKey(Sort::BY_CREATED_AT, $sort->get());
        $this->assertEquals(Sort::DESC, $sort->get()[Sort::BY_CREATED_AT]);
    }

    public function testCreateSortWithInvalidField()
    {
        $this->expectException(InvalidArgumentException::class);
        new Sort('InvalidField', Sort::DESC);
    }

    public function testCreateSortWithInvalidDirection()
    {
        $this->expectException(InvalidArgumentException::class);
        new Sort(Sort::BY_TAG_1, 'Equal');
    }
}