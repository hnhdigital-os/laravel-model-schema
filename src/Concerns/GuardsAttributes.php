<?php

namespace HnhDigital\ModelSchema\Concerns;

trait GuardsAttributes
{
    /**
     * Get the fillable attributes for the model.
     *
     * @return array
     */
    public function getFillable()
    {
        return $this->getAttributesFromSchema('fillable', false, true);
    }

    /**
     * Set the fillable attributes for the model.
     *
     * @param  array  $fillable
     * @return $this
     */
    public function fillable(array $fillable)
    {
        $this->setSchema('fillable', $fillable, true, true, false);

        return $this;
    }

    /**
     * Get the guarded attributes for the model.
     *
     * @return array
     */
    public function getGuarded()
    {
        $guarded_create = ! $this->exists ? $this->getAttributesFromSchema('guarded-create', false, true) : [];
        $guarded_update = $this->exists ? $this->getAttributesFromSchema('guarded-update', false, true) : [];
        $guarded = $this->getAttributesFromSchema('guarded', false, true);

        return array_merge($guarded_create, $guarded_update, $guarded);
    }

    /**
     * Set the guarded attributes for the model.
     *
     * @param  array  $guarded
     * @return $this
     */
    public function guard(array $guarded)
    {
        $this->setSchema('guarded', $guarded, true, true, false);

        return $this;
    }
}
