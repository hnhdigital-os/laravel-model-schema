<?php

namespace HnhDigital\ModelSchema;

/*
 * This file is part of the Laravel Model Attributes package.
 *
 * (c) H&H|Digital <hello@hnh.digital>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Illuminate\Database\Eloquent\Concerns\GuardsAttributes as EloquentGuardsAttributes;
use Illuminate\Database\Eloquent\Concerns\HasAttributes as EloquentHasAttributes;
use Illuminate\Database\Eloquent\Concerns\HidesAttributes as EloquentHidesAttributes;
use Illuminate\Database\Eloquent\Model as EloquentModel;

/**
 * This is the Model class.
 *
 * @author Rocco Howard <rocco@hnh.digital>
 */
class Model extends EloquentModel
{
    use EloquentGuardsAttributes, Concerns\GuardsAttributes {
        Concerns\GuardsAttributes::getFillable insteadof EloquentGuardsAttributes;
        Concerns\GuardsAttributes::fillable insteadof EloquentGuardsAttributes;
        Concerns\GuardsAttributes::getGuarded insteadof EloquentGuardsAttributes;
        Concerns\GuardsAttributes::guard insteadof EloquentGuardsAttributes;
    }

    use EloquentHidesAttributes, Concerns\HidesAttributes {
        Concerns\HidesAttributes::getHidden insteadof EloquentHidesAttributes;
        Concerns\HidesAttributes::setHidden insteadof EloquentHidesAttributes;
        Concerns\HidesAttributes::addHidden insteadof EloquentHidesAttributes;
        Concerns\HidesAttributes::getVisible insteadof EloquentHidesAttributes;
        Concerns\HidesAttributes::setVisible insteadof EloquentHidesAttributes;
        Concerns\HidesAttributes::addVisible insteadof EloquentHidesAttributes;
        Concerns\HidesAttributes::makeVisible insteadof EloquentHidesAttributes;
        Concerns\HidesAttributes::makeHidden insteadof EloquentHidesAttributes;
    }

    use EloquentHasAttributes, Concerns\HasAttributes {
        EloquentHasAttributes::getDirty as eloquentGetDirty;
        Concerns\HasAttributes::__set insteadof EloquentHasAttributes;
        Concerns\HasAttributes::getDirty insteadof EloquentHasAttributes;
        Concerns\HasAttributes::getCasts insteadof EloquentHasAttributes;
        Concerns\HasAttributes::castAttribute insteadof EloquentHasAttributes;
        Concerns\HasAttributes::setAttribute insteadof EloquentHasAttributes;
    }

    /**
     * Describes the model's attributes.
     *
     * @var array
     */
    protected $schema = [];

    /**
     * Stores schema requests.
     *
     * @var array
     */
    private $schema_cache = [];

    /**
     * Get schema for this model.
     *
     * @return array
     */
    public function getSchema()
    {
        return $this->schema;
    }

    /**
     * Get attributes from the schema of this model.
     *
     * @param null|string $entry
     *
     * @return array
     */
    public function getAttributesFromSchema($entry = null, $with_value = false)
    {
        if (is_null($entry)) {
            return array_keys($this->getSchema());
        }

        if ($attributes = $this->getSchemaCache($entry.'_'.(int) $with_value)) {
            return $attributes;
        }

        $attributes = [];

        foreach ($this->getSchema() as $key => $config) {
            if (array_has($config, $entry)) {
                if ($with_value) {
                    $attributes[$key] = $config[$entry];
                } else {
                    $attributes[] = $key;
                }
            }
        }

        $this->setSchemaCache($entry.'_'.(int) $with_value, $attributes);

        return $attributes;
    }

    /**
     * Cache for this key.
     *
     * @param string $key
     * @param array  $data
     *
     * @return void
     */
    private function setSchemaCache($key, $data)
    {
        $this->schema_cache[$key] = $data;
    }

    /**
     * Get cache for this key.
     *
     * @param string $key
     *
     * @return void
     */
    private function getSchemaCache($key)
    {
        if (isset($this->schema_cache[$key])) {
            return $this->schema_cache[$key];
        }

        return false;
    }

    /**
     * Break cache for this key.
     *
     * @param string $key
     *
     * @return void
     */
    private function breakSchemaCache($key)
    {
        unset($this->schema_cache[$key]);
    }

    /**
     * Set an entry within the schema.
     *
     * @param string $entry
     * @param array  $keys
     * @param bool   $reset
     * @param mixed  $reset_value
     *
     * @return array
     */
    public function setSchema($entry, $keys, $value, $reset = false, $reset_value = null)
    {
        // Reset existing values in the schema.
        if ($reset) {
            $current = $this->getSchema($entry);
            foreach ($current as $key => $value) {
                if (is_null($reset_value)) {
                    unset($this->schema[$key][$entry]);
                    continue;
                }

                array_set($this->schema, $key.'.'.$entry, $reset_value);
            }
        }

        // Update each of the keys.
        foreach ($keys as $key) {
            array_set($this->schema, $key.'.'.$entry, $value);
        }

        // Break the cache.
        $this->breakSchemaCache($entry.'_0');
        $this->breakSchemaCache($entry.'_1');

        return $this;
    }

    /**
     * Assign an array of data to this model.
     *
     * @param array $data
     *
     * @return $this
     */
    public function assign($data)
    {
        foreach ($data as $key => $value) {
            $this->{$key} = $value;
        }

        return $this;
    }

    /**
     * Boot events.
     *
     * @return void
     */
    public static function boot()
    {
        parent::boot();

        // Boot event for creating this model.
        // Set default values if specified.
        // Validate dirty attributes before commiting to save.
        self::creating(function ($model) {
            $model->setDefaultValuesForAttributes();
            if (!$model->savingValidation()) {
                $validator = $model->getValidator();
                $message = sprintf('Validation failed on creating %s.', $model->getTable());

                throw new Exceptions\ValidationException($message, 0, null, $validator);
            }
        });

        // Boot event for updating this model.
        // Validate dirty attributes before commiting to save.
        self::updating(function ($model) {
            if (!$model->savingValidation()) {
                $validator = $model->getValidator();
                $message = sprintf('Validation failed on saving %s (%s).', $model->getTable(), $model->getKey());

                throw new Exceptions\ValidationException($message, 0, null, $validator);
            }
        });

        self::retrieved(function ($model) {
        });
    }
}
