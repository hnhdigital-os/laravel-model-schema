<?php

namespace HnhDigital\ModelAttributes;

/*
 * This file is part of the Laravel Model Attributes package.
 *
 * (c) H&H|Digital <hello@hnh.digital>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Illuminate\Database\Eloquent\Model as EloquentModel;
use Illuminate\Database\Eloquent\Concerns\GuardsAttributes as EloquentGuardsAttributes;
use Illuminate\Database\Eloquent\Concerns\HasAttributes as EloquentHasAttributes;
use Illuminate\Database\Eloquent\Concerns\HidesAttributes as EloquentHidesAttributes;

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
    protected $structure = [];

    /**
     * Stores structure requests.
     *
     * @var array
     */
    private $structure_cache = [];

    /**
     * Get structure for this model.
     *
     * @return array
     */
    public function getStructure()
    {
        return $this->structure;
    }

    /**
     * Get attributes from the structure of this model.
     *
     * @param null|string $entry
     *
     * @return array
     */
    public function getAttributesFromStructure($entry = null, $with_value = false)
    {
        if (is_null($entry)) {
            return array_keys($this->getStructure());
        }

        if ($attributes = $this->getStructureCache($entry.'_'.(int) $with_value)) {
            return $attributes;
        }

        $attributes = [];

        foreach ($this->getStructure() as $key => $config) {
            if (array_has($config, $entry)) {
                if ($with_value) {
                    $attributes[$key] = $config[$entry];
                } else {
                    $attributes[] = $key;
                }
            }
        }

        $this->setStructureCache($entry.'_'.(int) $with_value, $attributes);

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
    private function setStructureCache($key, $data)
    {
        $this->structure_cache[$key] = $data;
    }

    /**
     * Get cache for this key.
     *
     * @param string $key
     *
     * @return void
     */
    private function getStructureCache($key)
    {
        if (isset($this->structure_cache[$key])) {
            return $this->structure_cache[$key];
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
    private function breakStructureCache($key)
    {
        unset($this->structure_cache[$key]);
    }

    /**
     * Set an entry within the structure.
     *
     * @param string $entry
     * @param array  $keys
     * @param bool   $reset
     * @param mixed  $reset_value
     *
     * @return array
     */
    public function setStructure($entry, $keys, $value, $reset = false, $reset_value = null)
    {
        // Reset existing values in the structure.
        if ($reset) {
            $current = $this->getStructure($entry);
            foreach ($current as $key => $value) {
                if (is_null($reset_value)) {
                    unset($this->structure[$key][$entry]);
                    continue;
                }

                array_set($this->structure, $key.'.'.$entry, $reset_value);
            }
        }

        // Update each of the keys.
        foreach ($keys as $key) {
            array_set($this->structure, $key.'.'.$entry, $value);
        }

        // Break the cache.
        $this->breakStructureCache($entry.'_0');
        $this->breakStructureCache($entry.'_1');

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
        // Boot event for creating this model.
        // Set default values if specified.
        // Validate dirty attributes before commiting to save.
        self::creating(function ($model) {
            $model->setDefaultValuesForAttributes();
            if (!$model->savingValidation()) {
                return false;
            }
        });

        // Boot event for updating this model.
        // Validate dirty attributes before commiting to save.
        self::updating(function ($model) {
            if (!$model->savingValidation()) {
                return false;
            }
        });

        self::retrieved(function ($model) {

        });

        parent::boot();
    }
}
