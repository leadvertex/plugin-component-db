<?php


namespace Leadvertex\Plugin\Components\Db\ComponentsTest;


use Leadvertex\Plugin\Components\Db\Components\Connector;
use Leadvertex\Plugin\Components\Db\Components\PluginReference;
use Medoo\Medoo;
use PHPUnit\Framework\TestCase;
use RuntimeException;

class ConnectorTest extends TestCase
{
    public function testGetDb()
    {
        $medoo = new Medoo([
            'database_type' => 'sqlite',
            'database_file' => ':memory:'
        ]);
        Connector::init($medoo);

        $pluginReference = new PluginReference(1, 'user', 2);
        Connector::setReference($pluginReference);
        $this->assertSame($medoo, Connector::db());
    }

    public function testConnectorSetReference()
    {
        Connector::init(
            new Medoo([
                'database_type' => 'sqlite',
                'database_file' => ':memory:'
            ])
        );

        $pluginReference = new PluginReference(1, 'user', 2);
        Connector::setReference($pluginReference);

        $this->assertSame($pluginReference, Connector::getReference());
    }

    public function testConnectorReferenceNotConfigured()
    {
        Connector::init(
            new Medoo([
                'database_type' => 'sqlite',
                'database_file' => ':memory:'
            ])
        );
        try {
            $actual = Connector::getReference();
        } catch (RuntimeException $e) {
            $actual = $e->getMessage();
        }

        $this->assertSame('Plugin reference is not configured', $actual);
    }

    public function testConnectorMedooNotConfigured()
    {
        try {
            $actual = Connector::db();
        } catch (RuntimeException $e) {
            $actual = $e->getMessage();
        }

        $this->assertSame('Medoo was not configured', $actual);
    }

    public function testGetReference()
    {
        Connector::init(
            new Medoo([
                'database_type' => 'sqlite',
                'database_file' => ':memory:'
            ])
        );
        $pluginReference = new PluginReference(1, 'user', 2);
        Connector::setReference($pluginReference);

        $this->assertEquals('user', $pluginReference->getAlias());
        $this->assertEquals(1, $pluginReference->getCompanyId());
        $this->assertEquals(2, $pluginReference->getId());
        $this->assertEquals($pluginReference, new PluginReference(1, 'user', 2));
    }

    public function testConnectorHasReference()
    {
        Connector::init(
            new Medoo([
                'database_type' => 'sqlite',
                'database_file' => ':memory:'
            ])
        );
        $actual = Connector::hasReference();

        $this->assertSame(false, $actual);

        $pluginReference = new PluginReference(1, 'user', 2);
        Connector::setReference($pluginReference);

        $actual = Connector::hasReference();

        $this->assertSame(true, $actual);
    }
}