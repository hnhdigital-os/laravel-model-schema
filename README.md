```
___  ___          _      _ _____      _
|  \/  |         | |    | /  ___|    | |
| .  . | ___   __| | ___| \ `--.  ___| |__   ___ _ __ ___   __ _
| |\/| |/ _ \ / _` |/ _ \ |`--. \/ __| '_ \ / _ \ '_ ` _ \ / _` |
| |  | | (_) | (_| |  __/ /\__/ / (__| | | |  __/ | | | | | (_| |
\_|  |_/\___/ \__,_|\___|_\____/ \___|_| |_|\___|_| |_| |_|\__,_|
```

Implements a schema approach to models. Combines the casts, fillable, hidden arrays into a single schema array. Allows custom casts and more.

[![Latest Stable Version](https://poser.pugx.org/hnhdigital-os/laravel-model-schema/v/stable.svg)](https://packagist.org/packages/hnhdigital-os/laravel-model-schema) [![Total Downloads](https://poser.pugx.org/hnhdigital-os/laravel-model-schema/downloads.svg)](https://packagist.org/packages/hnhdigital-os/laravel-model-schema) [![Latest Unstable Version](https://poser.pugx.org/hnhdigital-os/laravel-model-schema/v/unstable.svg)](https://packagist.org/packages/hnhdigital-os/laravel-model-schema) [![Built for Laravel](https://img.shields.io/badge/Built_for-Laravel-green.svg)](https://laravel.com/) [![License](https://poser.pugx.org/hnhdigital-os/laravel-model-schema/license.svg)](https://packagist.org/packages/hnhdigital-os/laravel-model-schema) [![Donate to this project using Patreon](https://img.shields.io/badge/patreon-donate-yellow.svg)](https://patreon.com/RoccoHoward)

[![Build Status](https://travis-ci.org/hnhdigital-os/laravel-model-schema.svg?branch=master)](https://travis-ci.org/hnhdigital-os/laravel-model-schema) [![StyleCI](https://styleci.io/repos/118241341/shield?branch=master)](https://styleci.io/repos/118241341) [![Test Coverage](https://codeclimate.com/github/hnhdigital-os/laravel-model-schema/badges/coverage.svg)](https://codeclimate.com/github/hnhdigital-os/laravel-model-schema/coverage) [![Issue Count](https://codeclimate.com/github/hnhdigital-os/laravel-model-schema/badges/issue_count.svg)](https://codeclimate.com/github/hnhdigital-os/laravel-model-schema) [![Code Climate](https://codeclimate.com/github/hnhdigital-os/laravel-model-schema/badges/gpa.svg)](https://codeclimate.com/github/hnhdigital-os/laravel-model-schema)

This package has been developed by H&H|Digital, an Australian botique developer. Visit us at [hnh.digital](http://hnh.digital).

## Documentation

* [Requirements](#requirements)
* [Installation](#install)
* [Configuration](#configuration)
* [Contributing](#contributing)
* [Credits](#credits)
* [License](#license)

## Requirements

* Laravel 5.5
* PHP 7.1

## Install

Via composer:

`$ composer require hnhdigital-os/laravel-model-schema ~1.0`

## Configuration

Enable the model on any given model.

```php
use HnhDigital\ModelSchema\Model;

class SomeModel extends Model
{

}
```

## Contributing

Please see [CONTRIBUTING](https://github.com/hnhdigital-os/laravel-model-schema/blob/master/CONTRIBUTING.md) for details.

## Credits

* [Rocco Howard](https://github.com/RoccoHoward)
* [All Contributors](https://github.com/hnhdigital-os/laravel-model-schema/contributors)

## License

The MIT License (MIT). Please see [License File](https://github.com/hnhdigital-os/laravel-model-schema/blob/master/LICENSE) for more information.
