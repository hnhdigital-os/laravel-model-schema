<?php

namespace HnhDigital\ModelSchema\Concerns;

use Illuminate\Support\Arr;

trait HidesAttributes
{
    /**
     * Get the hidden attributes for the model.
     *
     * @return array
     */
    public function getHidden()
    {
        return $this->getAttributesFromSchema('hidden', false, true);
    }

    /**
     * Set the hidden attributes for the model.
     *
     * @param array $hidden
     *
     * @return $this
     */
    public function setHidden(array $hidden)
    {
        $this->setSchema('hidden', $hidden, true, true, false);

        return $this;
    }

    /**
     * Add hidden attributes for the model.
     *
     * @param array|string|null $attributes
     *
     * @return $this
     */
    public function addHidden($attributes = null)
    {
        $hidden = array_merge(
            $this->getHidden(),
            is_array($attributes) ? $attributes : func_get_args()
        );

        $this->setSchema('hidden', $hidden, true, true, false);

        return $this;
    }

    /**
     * Get the visible attributes for the model.
     *
     * @return array
     */
    public function getVisible()
    {
        return array_merge(array_diff(
            $this->getAttributesFromSchema(),
            $this->getAttributesFromSchema('hidden', false, true)
        ), $this->appends);
    }

    /**
     * Set the visible attributes for the model.
     *
     * @param array $visible
     *
     * @return $this
     */
    public function setVisible(array $visible)
    {
        $this->setSchema('hidden', $visible, false, true, true);

        return $this;
    }

    /**
     * Add visible attributes for the model.
     *
     * @param array|string|null $attributes
     *
     * @return $this
     */
    public function addVisible($attributes = null)
    {
        $this->setSchema('hidden', is_array($attributes) ? $attributes : func_get_args(), false);

        return $this;
    }

    /**
     * Make the given, typically hidden, attributes visible.
     *
     * @param array|string $attributes
     *
     * @return $this
     */
    public function makeVisible($attributes)
    {
        $hidden = array_diff($this->getHidden(), Arr::wrap($attributes));

        $this->setSchema('hidden', $hidden, true, true, false);

        if (! empty($this->getVisible())) {
            $this->addVisible($attributes);
        }

        return $this;
    }

    /**
     * Make the given, typically visible, attributes hidden.
     *
     * @param array|string $attributes
     *
     * @return $this
     */
    public function makeHidden($attributes)
    {
        $attributes = Arr::wrap($attributes);

        $this->setSchema('visible', array_diff($this->getVisible(), $attributes), true);
        $this->setSchema('hidden', array_unique(array_merge($this->getHidden(), $attributes)), true);

        return $this;
    }
}
