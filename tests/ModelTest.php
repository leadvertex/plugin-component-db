<?php

namespace Leadvertex\Plugin\Components\Db;

use Leadvertex\Plugin\Components\Db\Components\Connector;
use Leadvertex\Plugin\Components\Db\Components\TestModelClass;
use Medoo\Medoo;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Filesystem\Filesystem;

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
    }

    public function testCreateModel()
    {
        $model = new TestModelClass(1, 2, 3);
        $this->assertEquals(1, $model->getCompanyId());
        $this->assertEquals(2, $model->getId());
        $this->assertEquals(3, $model->getFeature());
    }

    public function testSetModelData()
    {
        $model = new TestModelClass(1, 2, 3);
        $model->testData = 'test';
        $this->assertEquals('test', $model->testData);
    }

    public function testSetTags()
    {
        $model = new TestModelClass(1, 2, 3);
        $model->setTag1('testTag1');
        $model->setTag2('testTag2');
        $model->setTag3('testTag3');
        $this->assertEquals('testTag1', $model->getTag1());
        $this->assertEquals('testTag2', $model->getTag2());
        $this->assertEquals('testTag3', $model->getTag3());
    }

    public function testFindById()
    {
        $id = 'ddfcaefa-243c-453e-91fc-6823a5c8496e';

        /** @var TestModelClass $model */
        $model = TestModelClass::findById(1, $id, '3');

        $this->assertInstanceOf(TestModelClass::class, $model);
        $this->assertEquals(1, $model->getCompanyId());
        $this->assertEquals(3, $model->getFeature());
    }

    public function testFindByIds()
    {
        $ids = [
            'ddfcaefa-243c-453e-91fc-6823a5c8496e',
            '05626728-9890-4348-bd3f-9cf7dc1b8375',
            '0088dee9-b4cb-4e14-841f-d0cee01ba362'
        ];

        /** @var TestModelClass[] $models */
        $models = TestModelClass::findByIds(1, $ids, '5');
        $this->assertCount(2, $models);
        foreach ($models as $model) {
            $this->assertInstanceOf(TestModelClass::class, $model);
            $this->assertEquals(1, $model->getCompanyId());
            $this->assertEquals(5, $model->getFeature());
        }
    }

    public function testSaveModel()
    {
        $uuid = Uuid::uuid4()->toString();

        $model = new TestModelClass(1, $uuid, 3);
        $model->setTag1('testTag1');
        $model->setTag2('testTag2');
        $model->setTag3('testTag3');
        $model->dataName = 'name';
        $model->dataPhone = '89999999999';
        $model->save();

        $loadedModel = TestModelClass::findById(1, $uuid, 3);

        $this->assertInstanceOf(TestModelClass::class, $loadedModel);

        $this->assertInstanceOf(\DateTimeImmutable::class, $loadedModel->getCreatedAt());
        $this->assertEquals($model->getCreatedAt()->getTimestamp(), $loadedModel->getCreatedAt()->getTimestamp());

        $this->assertInstanceOf(\DateTimeImmutable::class, $loadedModel->getUpdatedAt());
        $this->assertEquals($model->getUpdatedAt()->getTimestamp(), $loadedModel->getUpdatedAt()->getTimestamp());

        $this->assertEquals(1, $loadedModel->getCompanyId());

        $this->assertEquals($uuid, $loadedModel->getId());

        $this->assertEquals(3, $loadedModel->getFeature());

        $this->assertEquals('testTag1', $loadedModel->getTag1());
        $this->assertEquals('testTag2', $loadedModel->getTag2());
        $this->assertEquals('testTag3', $loadedModel->getTag3());

        $this->assertEquals('name', $loadedModel->dataName);
        $this->assertEquals('89999999999', $loadedModel->dataPhone);
    }

    public function testUpdateModel()
    {
        $id = 'ddfcaefa-243c-453e-91fc-6823a5c8496e';

        /** @var TestModelClass $model */
        $model = TestModelClass::findById(1, $id, '3');

        $model->setTag1('newTag1');
        $model->setTag2('newTag2');
        $model->setTag3('newTag3');
        $model->save();

        $model = TestModelClass::findById(1, $id, '3');

        $this->assertInstanceOf(TestModelClass::class, $model);
        $this->assertEquals('newTag1', $model->getTag1());
        $this->assertEquals('newTag2', $model->getTag2());
        $this->assertEquals('newTag3', $model->getTag3());

    }

    public function testDeleteModel()
    {
        $id = 'ddfcaefa-243c-453e-91fc-6823a5c8496e';

        /** @var TestModelClass $model */
        $model = TestModelClass::findById(1, $id, '3');
        $model->delete();

        $this->assertNull(TestModelClass::findById(1, $id, '3'));
    }
}