<?php


namespace Leadvertex\Plugin\Components\Db\ComponentsTest;


use Leadvertex\Plugin\Components\Db\Components\Connector;
use Leadvertex\Plugin\Components\Db\Components\PluginReference;
use Medoo\Medoo;
use PHPUnit\Framework\TestCase;

class ConnectorTest extends TestCase
{

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

}