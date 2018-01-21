<?php

namespace HnhDigital\ModelAttributes\Concerns;

trait HidesAttributes
{
    /**
     * Get the hidden attributes for the model.
     *
     * @return array
     */
    public function getHidden()
    {
        return $this->getAttributesFromStructure('hidden');
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
        $this->updateStructure('hidden', $hidden, true, true, false);

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
            $this->getHidden(), is_array($attributes) ? $attributes : func_get_args()
        );

        $this->updateStructure('hidden', $hidden, true, true, false);

        return $this;
    }

    /**
     * Get the visible attributes for the model.
     *
     * @return array
     */
    public function getVisible()
    {
        return $this->getAttributesFromStructure('visible');
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
        $this->updateStructure('visible', $visible, true, true, false);

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
        $visible = array_merge(
            $this->getVisible(), is_array($attributes) ? $attributes : func_get_args()
        );

        $this->updateStructure('visible', $visible, true, true, false);

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
        $hidden = array_diff($this->getHidden(), (array) $attributes);

        $this->updateStructure('hidden', $hidden, true, true, false);

        if (!empty($this->getVisible())) {
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
        $attributes = (array) $attributes;

        $this->updateStructure('visible', array_diff($this->getVisible(), $attributes), true);
        $this->updateStructure('hidden', array_unique(array_merge($this->getHidden(), $attributes)), true);

        return $this;
    }
}
