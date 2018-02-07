```
___  ___          _      _ _____      _
|  \/  |         | |    | /  ___|    | |
| .  . | ___   __| | ___| \ `--.  ___| |__   ___ _ __ ___   __ _
| |\/| |/ _ \ / _` |/ _ \ |`--. \/ __| '_ \ / _ \ '_ ` _ \ / _` |
| |  | | (_) | (_| |  __/ /\__/ / (__| | | |  __/ | | | | | (_| |
\_|  |_/\___/ \__,_|\___|_\____/ \___|_| |_|\___|_| |_| |_|\__,_|
```

Implements a schema approach to models. Combines the casts, fillable, hidden properties into a single schema property. This package provides the ability to store and validate automatically and implement custom casting.

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

### Enable the model

Enable the model on any given model.

```php
use HnhDigital\ModelSchema\Model;

class SomeModel extends Model
{

}
```

We recommend implementing a shared base model that you extend.

### Convert your current properties

The schema for a model is implemented using a protected property.

Here's an example:

```php
    /**
     * Describe your model.
     *
     * @var array
     */
    protected $schema = [
        'id' => [
            'cast'    => 'integer',
            'guarded' => true,
        ],
        'name' => [
            'cast'     => 'string',
            'rules'    => 'max:255',
            'fillable' => true,
        ],
        'created_at' => [
            'cast'    => 'datetime',
            'guarded' => true,
            'log'     => false,
            'hidden'  => true,
        ],
        'updated_at' => [
            'cast'    => 'datetime',
            'guarded' => true,
            'hidden'  => true,
        ],
        'deleted_at' => [
            'cast'    => 'datetime',
            'rules'  => 'nullable',
            'hidden'  => true,
        ],
    ];
```

Ensure the parent boot occurs after your triggers so that any attribute changes are done before this packages triggers the validation.

```php
    /**
     * Boot triggers.
     *
     * @return void
     */
    public static function boot()
    {
        self::updating(function ($model) {
            // Doing something.
        });

        parent::boot();
    }
```

Model's using this method will throw a ValidationException exception if they do not pass validation. Be sure to catch these.

```php
    try {
        $user = User::create(request()->all());
    } catch (HnhDigital\ModelSchema\Exceptions\ValidationException $exception) {
        // Do something about the validation.

        // You can add things to the validator.
        $exception->getValidator()->errors()->add('field', 'Something is wrong with this field!');

        // We've implemented a response.
        // This redirects the same as a validator with errors.
        return $exception->getResponse('user::add');
    }
```

## Contributing

Please see [CONTRIBUTING](https://github.com/hnhdigital-os/laravel-model-schema/blob/master/CONTRIBUTING.md) for details.

## Credits

* [Rocco Howard](https://github.com/RoccoHoward)
* [All Contributors](https://github.com/hnhdigital-os/laravel-model-schema/contributors)

## License

The MIT License (MIT). Please see [License File](https://github.com/hnhdigital-os/laravel-model-schema/blob/master/LICENSE) for more information.
