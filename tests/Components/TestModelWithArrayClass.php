<?php

namespace Leadvertex\Plugin\Components\Db\Components;


use Leadvertex\Plugin\Components\Db\ModelInterface;
use Leadvertex\Plugin\Components\Db\Model;

class TestModelWithArrayClass extends Model implements ModelInterface
{
    public int $value_1;

    public string $value_2;

    public function setId(string $id): void
    {
        $this->id = $id;
    }

    public static function schema(): array
    {
        return [
            'value_1' => ['INT'],
            'value_2' => ['VARCHAR(255)'],
        ];
    }

    protected static function beforeWrite(array $data): array
    {
        $data['value_2'] = [$data['value_2'], 'new string'];
        return $data;
    }

}