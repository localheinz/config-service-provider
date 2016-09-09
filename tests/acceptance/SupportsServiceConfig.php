<?php

namespace tests\acceptance;

use tests\mocks\ExampleClass;
use tests\mocks\ExampleClassWithArgs;
use TomPHP\ConfigServiceProvider\Configurator;

trait SupportsServiceConfig
{
    public function testItAddsServicesToTheContainer()
    {
        $config = [
            'di' => [
                'services' => [
                    'example_class' => [
                        'class' => ExampleClass::class,
                    ],
                ],
            ],
        ];

        Configurator::apply()
            ->configFromArray($config)
            ->to($this->container);

        $this->assertInstanceOf(ExampleClass::class, $this->container->get('example_class'));
    }

    public function testItAddsServicesToTheContainerForADifferentConfigKey()
    {
        $config = [
            'di' => [
                'example_class' => [
                    'class' => ExampleClass::class,
                ],
            ],
        ];

        Configurator::apply()
            ->configFromArray($config)
            ->withSetting('services_key', 'di')
            ->to($this->container);

        $this->assertInstanceOf(ExampleClass::class, $this->container->get('example_class'));
    }

    public function testItCreatesUniqueServiceInstancesByDefault()
    {
        $config = [
            'di' => [
                'services' => [
                    'example_class' => [
                        'class'     => ExampleClass::class,
                        'singleton' => false,
                    ],
                ],
            ],
        ];

        Configurator::apply()
            ->configFromArray($config)
            ->to($this->container);

        $instance1 = $this->container->get('example_class');
        $instance2 = $this->container->get('example_class');

        $this->assertNotSame($instance1, $instance2);
    }

    public function testItCanCreateSingletonServiceInstances()
    {
        $config = [
            'di' => [
                'services' => [
                    'example_class' => [
                        'class'     => ExampleClass::class,
                        'singleton' => true,
                    ],
                ],
            ],
        ];

        Configurator::apply()
            ->configFromArray($config)
            ->to($this->container);

        $instance1 = $this->container->get('example_class');
        $instance2 = $this->container->get('example_class');

        $this->assertSame($instance1, $instance2);
    }

    public function testItCanCreateSingletonServiceInstancesByDefault()
    {
        $config = [
            'di' => [
                'services' => [
                    'example_class' => [
                        'class' => ExampleClass::class,
                    ],
                ],
            ],
        ];

        Configurator::apply()
            ->configFromArray($config)
            ->withSetting('singleton_services', true)
            ->to($this->container);

        $instance1 = $this->container->get('example_class');
        $instance2 = $this->container->get('example_class');

        $this->assertSame($instance1, $instance2);
    }

    public function testItCanCreateUniqueServiceInstancesWhenSingletonIsDefault()
    {
        $config = [
            'di' => [
                'services' => [
                    'example_class' => [
                        'class'     => ExampleClass::class,
                        'singleton' => false,
                    ],
                ],
            ],
        ];

        Configurator::apply()
            ->configFromArray($config)
            ->withSetting('singleton_services', true)
            ->to($this->container);

        $instance1 = $this->container->get('example_class');
        $instance2 = $this->container->get('example_class');

        $this->assertNotSame($instance1, $instance2);
    }

    public function testItAddsConstructorArguments()
    {
        $config = [
            'di' => [
                'services' => [
                    'example_class' => [
                        'class' => ExampleClassWithArgs::class,
                        'arguments' => [
                            'arg1',
                            'arg2',
                        ],
                    ],
                ],
            ],
        ];

        Configurator::apply()
            ->configFromArray($config)
            ->to($this->container);

        $instance = $this->container->get('example_class');

        $this->assertEquals(['arg1', 'arg2'], $instance->getConstructorArgs());
    }

    public function testItResolvesConstructorArgumentsIfTheyAreServiceNames()
    {
        $config = [
            'arg1' => 'value1',
            'arg2' => 'value2',
            'di' => [
                'services' => [
                    'example_class' => [
                        'class' => ExampleClassWithArgs::class,
                        'arguments' => [
                            'config.arg1',
                            'config.arg2',
                        ],
                    ],
                ],
            ],
        ];

        Configurator::apply()
            ->configFromArray($config)
            ->to($this->container);

        $instance = $this->container->get('example_class');

        $this->assertEquals(['value1', 'value2'], $instance->getConstructorArgs());
    }

    public function testItUsesTheStringIfConstructorArgumentsAreClassNames()
    {
        $config = [
            'di' => [
                'services' => [
                    'example_class' => [
                        'class' => ExampleClassWithArgs::class,
                        'arguments' => [
                            ExampleClass::class,
                            'arg2',
                        ],
                    ],
                ],
            ],
        ];

        Configurator::apply()
            ->configFromArray($config)
            ->to($this->container);

        $instance = $this->container->get('example_class');

        $this->assertEquals([ExampleClass::class, 'arg2'], $instance->getConstructorArgs());
    }

    public function testItCallsSetterMethods()
    {
        $config = [
            'di' => [
                'services' => [
                    'example_class' => [
                        'class' => ExampleClass::class,
                        'methods' => [
                            'setValue' => ['the value'],
                        ],
                    ],
                ],
            ],
        ];

        Configurator::apply()
            ->configFromArray($config)
            ->to($this->container);

        $instance = $this->container->get('example_class');

        $this->assertEquals('the value', $instance->getValue());
    }

    public function testItResolvesSetterMethodArgumentsIfTheyAreServiceNames()
    {
        $config = [
            'arg' => 'value',
            'di' => [
                'services' => [
                    'example_class' => [
                        'class' => ExampleClass::class,
                        'methods' => [
                            'setValue' => ['config.arg'],
                        ],
                    ],
                ],
            ],
        ];

        Configurator::apply()
            ->configFromArray($config)
            ->to($this->container);

        $instance = $this->container->get('example_class');

        $this->assertEquals('value', $instance->getValue());
    }

    public function testItUsesTheStringIffSetterMethodArgumentsAreClassNames()
    {
        $config = [
            'di' => [
                'services' => [
                    'example_class' => [
                        'class' => ExampleClass::class,
                        'methods' => [
                            'setValue' => [ExampleClass::class],
                        ],
                    ],
                ],
            ],
        ];

        Configurator::apply()
            ->configFromArray($config)
            ->to($this->container);

        $instance = $this->container->get('example_class');

        $this->assertSame(ExampleClass::class, $instance->getValue());
    }
}