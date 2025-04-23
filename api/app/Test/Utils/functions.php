<?php

declare(strict_types=1);

namespace App\Test\Utils {

    use App\Test\Utils\Model\AbstractFactory;
    use App\Tests\Factory\Factory;

    function factory(string $modelClass): AbstractFactory
    {
        return Factory::for($modelClass);
    }
}
