<?php

namespace Leadvertex\Plugin\Components\Db;

use DateTimeImmutable;
use Leadvertex\Plugin\Components\Db\Components\Connector;
use Leadvertex\Plugin\Components\Db\Components\Limit;
use Leadvertex\Plugin\Components\Db\Components\Sort;
use Medoo\Medoo;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;
use RuntimeException;
use Symfony\Component\Filesystem\Filesystem;
use Leadvertex\Plugin\Components\Db\Components\TestModelClass;

class ModelTest extends TestCase
{
    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        $filesystem = new Filesystem();
        $filesystem->remove(__DIR__ . '/testDB.db');
        $filesystem->mirror(__DIR__ . '/DBFiles/', __DIR__ . '/');

        Connector::init(
            new Medoo([
                'database_type' => 'sqlite',
                'database_file' => __DIR__ . '/testDB.db'
            ])
        );

        Connector::setCompanyId(1);
    }

    public function testCreateModel()
    {
        $model = new TestModelClass(2, 3);
        $this->assertEquals(Connector::getCompanyId(), $model->getCompanyId());
        $this->assertEquals(2, $model->getId());
        $this->assertEquals(3, $model->getFeature());
    }

    public function testCreateModelWithNullId()
    {
        $model = new TestModelClass(null, 3);
        $this->assertEquals(Connector::getCompanyId(), $model->getCompanyId());
        $this->assertNotNull($model->getId());
        $this->assertEquals(3, $model->getFeature());
    }

    public function testSetModelData()
    {
        $model = new TestModelClass(2, 3);
        $model->testData = 'test';
        $model->newData = ['ololo' => ['alala']];
        $model->nullData = ['field' => null];
        $this->assertEquals('test', $model->testData);
        $this->assertEquals(['ololo' => ['alala']], $model->newData);
        $this->assertEquals(['field' => null], $model->nullData);
    }

    public function testSetTags()
    {
        $model = new TestModelClass(2, 3);
        $model->setTag_1('testTag1');
        $model->setTag_2('testTag2');
        $model->setTag_3('testTag3');
        $this->assertEquals('testTag1', $model->getTag_1());
        $this->assertEquals('testTag2', $model->getTag_2());
        $this->assertEquals('testTag3', $model->getTag_3());
    }

    public function testGetUndefinedDataField()
    {
        $model = new TestModelClass(2, 3);
        $this->assertNull($model->nullData);
    }

    public function testFindById()
    {
        $id = 'ddfcaefa-243c-453e-91fc-6823a5c8496e';

        /** @var TestModelClass $model */
        $model = TestModelClass::findById($id, '3');

        $this->assertInstanceOf(TestModelClass::class, $model);
        $this->assertEquals($model->getId(), $id);
        $this->assertEquals(Connector::getCompanyId(), $model->getCompanyId());
        $this->assertEquals(3, $model->getFeature());
        $this->assertEquals("name", $model->nameData);
        $this->assertEquals("89999999999", $model->phoneData);
        $this->assertNull($model->nullData);
        $this->assertEquals("testTag1", $model->getTag_1());
        $this->assertEquals("testTag2", $model->getTag_2());
        $this->assertEquals("testTag3", $model->getTag_3());
    }

    public function testFindByIdWithoutFeature()
    {
        $id = 'ddfcaefa-243c-453e-91fc-6823a5c8496e';

        /** @var TestModelClass $model */
        $model = TestModelClass::findById($id, null);

        $this->assertInstanceOf(TestModelClass::class, $model);
        $this->assertEquals($model->getId(), $id);
        $this->assertEquals(Connector::getCompanyId(), $model->getCompanyId());
        $this->assertEquals(3, $model->getFeature());
        $this->assertEquals("name", $model->nameData);
        $this->assertEquals("89999999999", $model->phoneData);
        $this->assertNull($model->nullData);
        $this->assertEquals("testTag1", $model->getTag_1());
        $this->assertEquals("testTag2", $model->getTag_2());
        $this->assertEquals("testTag3", $model->getTag_3());
    }

    public function testFindByIds()
    {
        $ids = [
            'ddfcaefa-243c-453e-91fc-6823a5c8496e',
            '05626728-9890-4348-bd3f-9cf7dc1b8375',
            '0088dee9-b4cb-4e14-841f-d0cee01ba362'
        ];

        /** @var TestModelClass[] $models */
        $models = TestModelClass::findByIds( $ids, '5');
        $this->assertCount(2, $models);
        foreach ($models as $model) {
            $this->assertContains($model->getId(), $ids);
            $this->assertInstanceOf(TestModelClass::class, $model);
            $this->assertEquals(Connector::getCompanyId(), $model->getCompanyId());
            $this->assertEquals(5, $model->getFeature());
            $this->assertEquals("name", $model->nameData);
            $this->assertEquals("89999999999", $model->phoneData);
        }
    }

    public function testFindByIdsWithoutFeature()
    {
        $ids = [
            'ddfcaefa-243c-453e-91fc-6823a5c8496e',
            '05626728-9890-4348-bd3f-9cf7dc1b8375',
            '0088dee9-b4cb-4e14-841f-d0cee01ba362'
        ];

        /** @var TestModelClass[] $models */
        $models = TestModelClass::findByIds( $ids, null);
        $this->assertCount(3, $models);
        foreach ($models as $model) {
            $this->assertContains($model->getId(), $ids);
            $this->assertInstanceOf(TestModelClass::class, $model);
            $this->assertEquals(Connector::getCompanyId(), $model->getCompanyId());
            $this->assertEquals("name", $model->nameData);
            $this->assertEquals("89999999999", $model->phoneData);
        }
    }

    public function testFindManyWithFullRequest()
    {
        /** @var TestModelClass[] $models */
        $models = TestModelClass::findMany([5, 3], ['testTag1'], ['testTag2', 'testTag4'], ['testTag3', 'testTag2'], new Limit(2), new Sort(Sort::BY_FEATURE, Sort::DESC));

        $this->assertCount(2, $models);
        foreach ($models as $model) {
            $this->assertEquals(5, $model->getFeature());
            $this->assertEquals('testTag1', $model->getTag_1());
            $this->assertContains($model->getTag_2(), ['testTag2', 'testTag4']);
            $this->assertContains($model->getTag_3(), ['testTag3', 'testTag2']);
        }
    }

    public function testFindManyWithOnlyFeature()
    {
        /** @var TestModelClass[] $models */
        $models = TestModelClass::findMany([3]);

        $this->assertCount(1, $models);
        $this->assertEquals(3, $models[0]->getFeature());
    }

    public function testFindManyWithOnlyOneTag()
    {
        /** @var TestModelClass[] $models */
        $models = TestModelClass::findMany([], [], ['testTag2', 'testTag4']);

        $this->assertCount(3, $models);
        foreach ($models as $model) {
            $this->assertContains($model->getTag_2(), ['testTag2', 'testTag4']);
        }
    }

    public function testSaveModel()
    {
        $uuid = Uuid::uuid4()->toString();

        $model = new TestModelClass($uuid, 3);
        $model->setTag_1('testTag1');
        $model->setTag_2('testTag2');
        $model->setTag_3('testTag3');
        $model->nameData = 'name';
        $model->phoneData = '89999999999';
        $this->assertTrue($model->save());

        $loadedModel = TestModelClass::findById($uuid, 3);

        $this->assertInstanceOf(TestModelClass::class, $loadedModel);

        $this->assertInstanceOf(DateTimeImmutable::class, $loadedModel->getCreatedAt());
        $this->assertEquals($model->getCreatedAt()->getTimestamp(), $loadedModel->getCreatedAt()->getTimestamp());

        $this->assertNull($loadedModel->getUpdatedAt());

        $this->assertEquals(Connector::getCompanyId(), $loadedModel->getCompanyId());

        $this->assertEquals($uuid, $loadedModel->getId());

        $this->assertEquals(3, $loadedModel->getFeature());

        $this->assertEquals('testTag1', $loadedModel->getTag_1());
        $this->assertEquals('testTag2', $loadedModel->getTag_2());
        $this->assertEquals('testTag3', $loadedModel->getTag_3());

        $this->assertEquals('name', $loadedModel->nameData);
        $this->assertEquals('89999999999', $loadedModel->phoneData);
    }

    public function testUpdateModel()
    {
        $updateTime = new DateTimeImmutable();
        $id = 'ddfcaefa-243c-453e-91fc-6823a5c8496e';

        /** @var TestModelClass $model */
        $model = TestModelClass::findById($id, '3');

        $model->setTag_1('newTag1');
        $model->setTag_2('newTag2');
        $model->setTag_3('newTag3');
        $model->setUpdatedAt($updateTime);
        $model->nameData = "updatedName";
        $this->assertTrue($model->save());

        $model = TestModelClass::findById($id, '3');

        $this->assertInstanceOf(TestModelClass::class, $model);
        $this->assertInstanceOf(DateTimeImmutable::class, $model->getUpdatedAt());
        $this->assertEquals($updateTime->getTimestamp(), $model->getUpdatedAt()->getTimestamp());
        $this->assertEquals("updatedName", $model->nameData);
        $this->assertEquals("89999999999", $model->phoneData);
        $this->assertEquals('newTag1', $model->getTag_1());
        $this->assertEquals('newTag2', $model->getTag_2());
        $this->assertEquals('newTag3', $model->getTag_3());
        $this->assertEquals('updatedName', $model->nameData);
        $this->assertEquals('89999999999', $model->phoneData);

    }

    public function testDeleteModel()
    {
        $id = 'ddfcaefa-243c-453e-91fc-6823a5c8496e';

        /** @var TestModelClass $model */
        $model = TestModelClass::findById($id, '3');
        $this->assertTrue($model->delete());

        $this->assertNull(TestModelClass::findById($id, '3'));
    }

    public function testDeleteNewModel()
    {
        $model = new TestModelClass(2, 3);
        $this->assertFalse($model->delete());
    }

    public function testModelGuardCompanyId()
    {
        $this->expectException(RuntimeException::class);
        $model = new TestModelClass(2, 3);
        Connector::setCompanyId(2);
        $model->save();
    }


}