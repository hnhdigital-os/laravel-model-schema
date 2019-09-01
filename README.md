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

* [Prerequisites](#prerequisites)
* [Installation](#installation)
* [Configuration](#configuration)
* [Custom casts](#custom-casts)
* [Contributing](#contributing)
  * [Reporting issues](#reporting-issues)
  * [Pull requests](#pull-requests)
* [Credits](#credits)
* [License](#license)

## Prerequisites

* PHP >= 7.2
* Laravel >= 5.8

## Installation

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
    protected static $schema = [
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

Models implementing this package will now throw a ValidationException exception if they do not pass validation. Be sure to catch these.

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

## Custom casts

This package allows the ability to add custom casts. Simply create a trait, and register the cast on boot.


```php
trait ModelCastAsMoneyTrait
{
    /**
     * Cast value as Money.
     *
     * @param mixed $value
     *
     * @return Money
     */
    protected function castAsMoney($value, $currency = 'USD', $locale = 'en_US'): Money
    {
        return new Money($value, $currency, $locale);
    }

    /**
     * Convert the Money value back to a storable type.
     *
     * @return int
     */
    protected function castMoneyToInt($key, $value): int
    {
        if (is_object($value)) {
            return (int) $value->amount();
        }

        return (int) $value->amount();
    }

    /**
     * Register the casting definitions.
     */
    public static function bootModelCastAsMoneyTrait()
    {
        static::registerCastFromDatabase('money', 'castAsMoney');
        static::registerCastToDatabase('money', 'castMoneyToInt');
        static::registerCastValidator('money', 'int');
    }
}
```

Defining your attributes would look like this:

```php
    ...

    'currency' => [
        'cast'     => 'string',
        'rules'    => 'min:3|max:3',
        'fillable' => true,
    ],
    'total_amount' => [
        'cast'        => 'money',
        'cast-params' => '$currency:en_US',
        'default'     => 0,
        'fillable'    => true,
    ],
    ...
```

Casting parameters would include a helper function, or a local model method.

eg `$currency:user_locale()`, or `$currency():$locale()`

### Available custom casts

 * Cast the attribute to [Money](https://github.com/hnhdigital-os/laravel-model-schema-money)

## Contributing

Please review the [Contribution Guidelines](https://github.com/hnhdigital-os/laravel-model-schema/blob/master/CONTRIBUTING.md).

Only PRs that meet all criterium will be accepted.

## Code of conduct

Please observe and respect all aspects of the included [Code of Conduct](https://github.com/hnhdigital-os/laravel-model-schema/blob/master/CODE_OF_CONDUCT.md).

### Reporting issues

When reporting issues, please fill out the included [template](https://github.com/hnhdigital-os/laravel-model-schema/blob/master/ISSUE_TEMPLATE.md) as completely as possible. Incomplete issues may be ignored or closed if there is not enough information included to be actionable.

## Credits

* [Rocco Howard](https://github.com/RoccoHoward)
* [All Contributors](https://github.com/hnhdigital-os/laravel-model-schema/contributors)

## License

The MIT License (MIT). Please see [License File](https://github.com/hnhdigital-os/laravel-model-schema/blob/master/LICENSE) for more information.
