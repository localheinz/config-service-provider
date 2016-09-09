<?php

namespace tests\acceptance;

use TomPHP\ConfigServiceProvider\Configurator;

trait SupportsApplicationConfig
{
    public function testItAddsConfigToTheContainer()
    {
        $config = ['keyA' => 'valueA'];

        Configurator::apply()
            ->configFromArray($config)
            ->to($this->container);

        $this->assertEquals('valueA', $this->container->get('config.keyA'));
    }

    public function testItCascadeAddsConfigToTheContainer()
    {
        Configurator::apply()
            ->configFromArray(['keyA' => 'valueA', 'keyB' => 'valueX'])
            ->configFromArray(['keyB' => 'valueB'])
            ->to($this->container);

        $this->assertEquals('valueA', $this->container->get('config.keyA'));
    }

    public function testItAddsGroupedConfigToTheContainer()
    {
        Configurator::apply()
            ->configFromArray(['group1' => ['keyA' => 'valueA']])
            ->to($this->container);

        $this->assertEquals(['keyA' => 'valueA'], $this->container->get('config.group1'));
        $this->assertEquals('valueA', $this->container->get('config.group1.keyA'));
    }

    public function testItAddsConfigToTheContainerWithAnAlternativeSeparator()
    {
        Configurator::apply()
            ->configFromArray(['keyA' => 'valueA'])
            ->withSetting('config_separator', '/')
            ->to($this->container);

        $this->assertEquals('valueA', $this->container->get('config/keyA'));
    }

    public function testItAddsConfigToTheContainerWithAnAlternativePrefix()
    {
        Configurator::apply()
            ->configFromArray(['keyA' => 'valueA'])
            ->withSetting('config_prefix', 'settings')
            ->to($this->container);

        $this->assertEquals('valueA', $this->container->get('settings.keyA'));
    }

    public function testItAddsConfigToTheContainerWithNoPrefix()
    {
        Configurator::apply()
            ->configFromArray(['keyA' => 'valueA'])
            ->withSetting('config_prefix', '')
            ->to($this->container);

        $this->assertEquals('valueA', $this->container->get('keyA'));
    }
}