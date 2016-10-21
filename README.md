# Container Configurator

[![Build Status](https://api.travis-ci.org/tomphp/container-configurator.svg)](https://api.travis-ci.org/tomphp/container-configurator)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/tomphp/container-configurator/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/tomphp/container-configurator/?branch=master)
[![Latest Stable Version](https://poser.pugx.org/tomphp/container-configurator/v/stable)](https://packagist.org/packages/tomphp/container-configurator)
[![Total Downloads](https://poser.pugx.org/tomphp/container-configurator/downloads)](https://packagist.org/packages/tomphp/container-configurator)
[![Latest Unstable Version](https://poser.pugx.org/tomphp/container-configurator/v/unstable)](https://packagist.org/packages/tomphp/container-configurator)
[![License](https://poser.pugx.org/tomphp/container-configurator/license)](https://packagist.org/packages/tomphp/container-configurator)

This package enables you to configure your application and the Dependency
Injection Container (DIC) via config arrays or files. Currently, supported
containers are:

* [League Of Extraordinary Packages' Container](https://github.com/thephpleague/container)
* [Pimple](http://pimple.sensiolabs.org/)

## Installation

Installation can be done easily using composer:

```
$ composer require tomphp/container-configurator
```

## Example Usage

```php
<?php

use League\Container\Container; // or Pimple\Container
use TomPHP\ContainerConfigurator\Configurator;

$config = [
    'db' => [
        'name'     => 'example_db',
        'username' => 'dbuser',
        'password' => 'dbpass',
    ],
    'di' => [
        'services' => [
            'database_connection' => [
                'class' => DatabaseConnection::class,
                'arguments' => [
                    'config.db.name',
                    'config.db.username',
                    'config.db.password',
                ],
            ],
        ],
    ],
];

$container = new Container();
Configurator::apply()->configFromArray($config)->to($container);

$db = $container->get('database_connection');
```

## Reading Files From Disk

Instead of providing the config as an array, you can also provide a list of
file pattern matches to the `fromFiles` function.

```php
Configurator::apply()
    ->configFromFile('config_dir/config.global.php')
    ->configFromFiles('json_dir/*.json')
    ->configFromFiles('config_dir/*.local.php')
    ->to($container);
```

`configFromFile(string $filename)` reads config in from a single file.

`configFromFiles(string $pattern)` reads config from multiple files using
globbing patterns.

### Merging

The reader matches files in the order they are specified. As files are
read their config is merged in; overwriting any matching keys.

### Supported Formats

Currently `.php` and `.json` files are supported out of the box. PHP 
config files **must** return a PHP array. 

`.yaml` and `.yml` files can be read when the package `symfony/yaml` is 
available. Run

```
composer require symfony/yaml
```

to install it.

## Application Configuration

All values in the config array are made accessible via the DIC with the keys
separated by a separator (default: `.`) and prefixed with constant string (default:
`config`).

#### Example

```php
$config = [
    'db' => [
        'name'     => 'example_db',
        'username' => 'dbuser',
        'password' => 'dbpass',
    ],
];

$container = new Container();
Configurator::apply()->configFromArray($config)->to($container);

var_dump($container->get('config.db.name'));
/*
 * OUTPUT:
 * string(10) "example_db"
 */
```

### Accessing A Whole Sub-Array

Whole sub-arrays are also made available for cases where you want them instead
of individual values.

#### Example

```php
$config = [
    'db' => [
        'name'     => 'example_db',
        'username' => 'dbuser',
        'password' => 'dbpass',
    ],
];

$container = new Container();
Configurator::apply()->configFromArray($config)->to($container);

var_dump($container->get('config.db'));
/*
 * OUTPUT:
 * array(3) {
 *   ["name"]=>
 *   string(10) "example_db"
 *   ["username"]=>
 *   string(6) "dbuser"
 *   ["password"]=>
 *   string(6) "dbpass"
 * }
 */
```

## Configuring Services

Another feature is the ability to add services to your container via the
config. By default, this is done by adding a `services` key under a `di` key in
the config in the following format:

```php
$config = [
    'di' => [
        'services' => [
            'logger' => [
                'class'     => Logger::class,
                'singleton' => true,
                'arguments' => [
                    StdoutLogger::class,
                ],
                'methods'   => [
                    'setLogLevel' => [ 'info' ],
                ],
            ],
            StdoutLogger::class => [],
        ],
    ],
];

$container = new Container();
Configurator::apply()->configFromArray($config)->to($container);

$logger = $container->get('logger'));
```

### Service Aliases

You can create an alias to another service by using the `service` keyword
instead of `class`:

```php
$config = [
    'database' => [ /* ... */ ],
    'di' => [
        'services' => [
            DatabaseConnection::class => [
                'service' => MySQLDatabaseConnection::class,
            ],
            MySQLDatabaseConnection::class => [
                'arguments' => [
                    'config.database.host',
                    'config.database.username',
                    'config.database.password',
                    'config.database.dbname',
                ],
            ],
        ],
    ],
];
```

### Service Factories

If you require some addition additional logic when creating a service, you can
define a Service Factory. A service factory is simply an invokable class which
can take a list of arguments and returns the service instance.

Services are added to the container by using the `factory` key instead of the
`class` key.

#### Example Config
```php
$appConfig = [
    'db' => [
        'host'     => 'localhost',
        'database' => 'example_db',
        'username' => 'example_user',
        'password' => 'example_password',
    ],
    'di' => [
        'services' => [
            'database' => [
                'factory'   => MySQLPDOFactory::class,
                'singleton' => true,
                'arguments' => [
                    'config.db.host',
                    'config.db.database',
                    'config.db.username',
                    'config.db.password',
                ],
            ],
        ],
    ],
];
```

#### Example Service Factory
```php
<?php

class MySQLPDOFactory
{
    public function __invoke($host, $database, $username, $password)
    {
        $dsn = "mysql:host=$host;dbname=$database";
        $pdo = new PDO($dsn, $username, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        return $pdo;
    }
}
```

### Injecting The Container

In the rare case that you want to inject the container in as a dependency to
one of your services, you can use `Configurator::container()` as the name
of the injected dependency. This will only work in PHP config files, it's not
available with YAML or JSON.

```php
$config = [
    'di' => [
        'services' => [
            ContainerAwareService::class => [
                'arguments' => [Configurator::container()],
            ],
        ],
    ],
];
```

### Configuring Inflectors

It is also possible to set up
[Inflectors](http://container.thephpleague.com/inflectors/) by adding an
`inflectors` key to the `di` section of the config.

```php
$appConfig = [
    'di' => [
        'inflectors' => [
            LoggerAwareInterface::class => [
                'setLogger' => ['Some\Logger']
            ],
        ],
    ],
];
```

## Extra Settings

The behaviour of the `Configurator` can be adjusted by using the
`withSetting(string $name, $value` method:

```php
Configurator::apply()
    ->configFromFiles('*.cfg.php'),
    ->withSetting(Configurator::SETTING_PREFIX, 'settings')
    ->withSetting(Configurator::SETTING_SEPARATOR, '/')
    ->to($container);
```

Available settings are:

| Name                               | Description                                     | Default         |
|------------------------------------|-------------------------------------------------|-----------------|
| SETTING_PREFIX                     | Sets prefix name for config value keys.         | `config`        |
| SETTING_SEPARATOR                  | Sets the separator for config key.              | `.`             |
| SETTING_SERVICES_KEY               | Where the config for the services is.           | `di.services`   |
| SETTING_INFLECTORS_KEY             | Where the config for the inflectors is.         | `di.inflectors` |
| SETTING_DEFAULT_SINGLETON_SERVICES | Sets whether services are singleton by default. | `false`         |

## Advanced Customisation

### Adding A Custom File Reader

You can create your own custom file reader by implementing the
`TomPHP\ContainerConfigurator\FileReader\FileReader` interface. Once you have
created your file reader, you can the
`withFileReader(string $extension, string $readerClassName)` method to enable
the reader.

**IMPORTANT**: `withFileReader()` must be called before calling
`configFromFile()` or `configFromFiles()`!

```php
Configurator::apply()
    ->withFileReader('.xml', MyCustomXMLFileReader::class)
    ->configFromFile('config.xml'),
    ->to($container);
