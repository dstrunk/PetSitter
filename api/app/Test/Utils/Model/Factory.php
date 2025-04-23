<?php

namespace App\Test\Utils\Model;

use App\Test\Utils\Model\Trait\HasFactory;

class Factory
{
    public static function for(string $model): AbstractFactory
    {
        $reflection = new \ReflectionClass($model);
        $attributes = $reflection->getAttributes(HasFactory::class);
        $factoryAttribute = $attributes[0]->newInstance();
        $factoryClass = $factoryAttribute->factoryClass;

        if (!class_exists($factoryClass)) {
            throw new \LogicException("$factoryClass does not exist");
        }

        return new $factoryClass($model);
    }
}
