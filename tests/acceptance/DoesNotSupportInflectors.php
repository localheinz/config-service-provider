<?php

namespace tests\acceptance;

use TomPHP\ConfigServiceProvider\Configurator;
use TomPHP\ConfigServiceProvider\Exception\UnsupportedFeatureException;

trait DoesNotSupportInflectors
{
    public function testInflectorsAreUnsupported()
    {
        $this->setExpectedException(UnsupportedFeatureException::class);

        $config = [
            'di' => [
                'inflectors' => [],
            ],
        ];

        Configurator::apply()->configFromArray($config)->to($this->container);
    }
}