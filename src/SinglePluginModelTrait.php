<?php


namespace Leadvertex\Plugin\Components\Db;


use Leadvertex\Plugin\Components\Db\Components\Connector;

trait SinglePluginModelTrait
{

    public static function find(): ?self
    {
        return static::findById(Connector::getReference()->getId());
    }

    abstract public static function findById(string $id): ?self;

}