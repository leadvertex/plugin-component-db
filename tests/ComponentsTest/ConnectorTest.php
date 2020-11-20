<?php


namespace Leadvertex\Plugin\Components\Db\ComponentsTest;


use Leadvertex\Plugin\Components\Db\Components\Connector;
use Leadvertex\Plugin\Components\Db\Components\PluginReference;
use Leadvertex\Plugin\Components\Db\ModelTrait;
use Medoo\Medoo;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

class ConnectorTest extends TestCase
{
    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();
        Connector::init(
            new Medoo([
                'database_type' => 'sqlite',
                'database_file' => ':memory:'
            ])
        );
    }

    public function testConnectorSetReference()
    {
        $pluginReference = new PluginReference(1, 'user', 2);
        Connector::setReference($pluginReference);


        $this->assertSame($pluginReference, Connector::getReference());
    }

}