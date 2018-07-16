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
        EloquentHasAttributes::asDateTime as eloquentAsDateTime;
        EloquentHasAttributes::getDirty as eloquentGetDirty;
        Concerns\HasAttributes::__set insteadof EloquentHasAttributes;
        Concerns\HasAttributes::asDateTime insteadof EloquentHasAttributes;
        Concerns\HasAttributes::getDirty insteadof EloquentHasAttributes;
        Concerns\HasAttributes::getCasts insteadof EloquentHasAttributes;
        Concerns\HasAttributes::getDates insteadof EloquentHasAttributes;
        Concerns\HasAttributes::castAttribute insteadof EloquentHasAttributes;
        Concerns\HasAttributes::setAttribute insteadof EloquentHasAttributes;
    }

    /**
     * Describe the database table.
     *
     * @var array
     */
    protected static $db_table;

    /**
     * Describes the schema for this model.
     *
     * @var array
     */
    protected static $schema = [];

    /**
     * Stores schema requests.
     *
     * @var array
     */
    protected static $schema_cache = [];

    /**
     * Describes the schema for this instantiated model.
     *
     * @var array
     */
    protected $model_schema = [];

    /**
     * Stores schema requests.
     *
     * @var array
     */
    private $model_schema_cache = [];

    /**
     * Cache.
     *
     * @var array
     */
    private $model_cache = [];

    /**
     * Get the schema for this model.
     *
     * @return array
     */
    public static function schema()
    {
        return static::$schema;
    }

    /**
     * Get table name.
     *
     * @return string
     */
    public function getTable()
    {
        if (!empty(static::$db_table)) {
            return static::$db_table;
        }

        return parent::getTable();
    }

    /**
     * Get table name (static).
     *
     * @return string
     */
    public static function getDbTable()
    {
        return static::$db_table;
    }

    /**
     * Get attributes from the schema of this model.
     *
     * @param null|string $entry
     *
     * @return array
     */
    public static function fromSchema($entry = null, $with_value = false)
    {
        if (is_null($entry)) {
            return array_keys(static::schema());
        }

        if ($attributes = static::schemaCache($entry.'_'.(int) $with_value)) {
            return $attributes;
        }

        $attributes = [];

        foreach (static::schema() as $key => $config) {
            if (array_has($config, $entry)) {
                if ($with_value) {
                    $attributes[$key] = $config[$entry];
                } else {
                    $attributes[] = $key;
                }
            }
        }

        static::schemaCache($entry.'_'.(int) $with_value, $attributes);

        return $attributes;
    }

    /**
     * Set or get Cache for this key.
     *
     * @param string $key
     * @param array  $data
     *
     * @return void
     */
    private static function schemaCache(...$args)
    {
        if (count($args) == 1) {
            $key = array_pop($args);
            if (isset(static::$schema_cache[$key])) {
                return static::$schema_cache[$key];
            }

            return false;
        }

        list($key, $value) = $args;

        static::$schema_cache[$key] = $value;
    }

    /**
     * Break cache for this key.
     *
     * @param string $key
     *
     * @return void
     */
    private static function unsetSchemaCache($key)
    {
        unset(static::$schema_cache[$key]);
    }

    /**
     * Get schema for this instantiated model.
     *
     * @return array
     */
    public function getSchema()
    {
        return array_replace_recursive(static::schema(), $this->model_schema);
    }

    /**
     * Get attributes from the schema of this model.
     *
     * @param null|string $entry
     *
     * @return array
     */
    public function getAttributesFromSchema($entry = null, $with_value = false, $is_value = null)
    {
        if (is_null($entry)) {
            return array_keys($this->getSchema());
        }

        if ($attributes = $this->getSchemaCache($entry.'_'.(int) $with_value)) {
            return $attributes;
        }

        $attributes = [];

        foreach ($this->getSchema() as $key => $config) {
            if (!is_null($is_value) && $is_value != array_get($config, $entry)) {
                continue;
            }

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
        $this->model_schema_cache[$key] = $data;
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
        if (isset($this->model_schema_cache[$key])) {
            return $this->model_schema_cache[$key];
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
        unset($this->model_schema_cache[$key]);
    }

    /**
     * Set an entry within the schema.
     *
     * @param string       $entry
     * @param string|array $keys
     * @param bool         $reset
     * @param mixed        $reset_value
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

                array_set($this->model_schema, $key.'.'.$entry, $reset_value);
            }
        }

        // Keys can be a single string attribute.
        if (!is_array($keys)) {
            $keys = [$keys];
        }

        // Update each of the keys.
        foreach ($keys as $key) {
            array_set($this->model_schema, $key.'.'.$entry, $value);
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
     * Cache this value.
     *
     * @param string $key
     * @param mixed  $value
     *
     * @return mixed
     */
    protected function cache($key, $value)
    {
        if (array_has($this->model_cache, $key)) {
            return array_get($this->model_cache, $key);
        }

        if (is_callable($value)) {
            $result = $value();
        } else {
            $result = $value;
        }

        array_set($this->model_cache, $key, $result);

        return $result;
    }

    /**
     * Boot events.
     *
     * @return void
     */
    protected static function boot()
    {
        parent::boot();

        // Boot event for creating this model.
        // Set default values if specified.
        // Validate dirty attributes before commiting to save.
        static::creating(function ($model) {
            $model->setDefaultValuesForAttributes();
            if (!$model->savingValidation()) {
                $validator = $model->getValidator();
                $issues = $validator->errors()->all();

                $message = sprintf(
                    "Validation failed on creating %s.\n%s",
                    $model->getTable(),
                    implode("\n", $issues)
                );

                throw new Exceptions\ValidationException($message, 0, null, $validator);
            }
        });

        // Boot event once model has been created.
        // Add missing attributes using the schema.
        static::created(function ($model) {
            $model->addMissingAttributes();
        });

        // Boot event for updating this model.
        // Validate dirty attributes before commiting to save.
        static::updating(function ($model) {
            if (!$model->savingValidation()) {
                $validator = $model->getValidator();
                $issues = $validator->errors()->all();

                $message = sprintf(
                    "Validation failed on saving %s (%s).\n%s",
                    $model->getTable(),
                    $model->getKey(),
                    implode("\n", $issues)
                );

                throw new Exceptions\ValidationException($message, 0, null, $validator);
            }
        });
    }
}
