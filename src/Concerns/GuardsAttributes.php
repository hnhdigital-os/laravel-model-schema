<?php

namespace HnhDigital\ModelAttributes\Concerns;

trait GuardsAttributes
{
    /**
     * Get the fillable attributes for the model.
     *
     * @return array
     */
    public function getFillable()
    {
        return $this->getAttributesFromStructure('fillable');
    }

    /**
     * Set the fillable attributes for the model.
     *
     * @param array $fillable
     *
     * @return $this
     */
    public function fillable(array $fillable)
    {
        $this->updateStructure('fillable', $fillable, true, true, false);

        return $this;
    }

    /**
     * Get the guarded attributes for the model.
     *
     * @return array
     */
    public function getGuarded()
    {
        $guarded_create = !$this->exists ? $this->getAttributesFromStructure('guarded-create') : [];
        $guarded_update = $this->exists ? $this->getAttributesFromStructure('guarded-update') : [];
        $guarded = $this->getAttributesFromStructure('guarded');

        return array_merge($guarded_create, $guarded_update, $guarded);
    }

    /**
     * Set the guarded attributes for the model.
     *
     * @param array $guarded
     *
     * @return $this
     */
    public function guard(array $guarded)
    {
        $this->updateStructure('guarded', $guarded, true, true, false);

        return $this;
    }
}
