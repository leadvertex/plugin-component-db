<?php


namespace Leadvertex\Plugin\Components\Db\ComponentsTest;


use Leadvertex\Plugin\Components\Db\Components\Connector;
use Leadvertex\Plugin\Components\Db\Components\PluginReference;
use Medoo\Medoo;
use PHPUnit\Framework\TestCase;

class PluginReferenceTest extends TestCase
{

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

}