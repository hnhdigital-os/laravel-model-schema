<?php

namespace HnhDigital\ModelSchema\Tests;

use HnhDigital\ModelSchema\Model;

class MockModel extends Model
{
    protected $table = 'mock_model';

    public function getAttributes($key = false)
    {
        return $key === false ? $this->attributes : $this->attributes[$key];
    }

    /**
     * Describes the model.
     *
     * @var array
     */
    protected static $schema = [
        'id' => [
            'cast'    => 'integer',
            'guarded' => true,
        ],
        'uuid' => [
            'cast'    => 'uuid',
            'guarded' => true,
            'rules'   => 'nullable',
        ],
        'name' => [
            'cast'     => 'string',
            'rules'    => 'required|min:2|max:255',
            'fillable' => true,
        ],
        'is_alive' => [
            'cast'     => 'boolean',
            'default'  => true,
        ],
        'enable_notifications' => [
            'cast'     => 'boolean',
            'default'  => false,
            'auth'     => 'check_role',
        ],
        'is_admin' => [
            'cast'     => 'boolean',
            'default'  => false,
        ],
        'created_at' => [
            'cast'           => 'datetime',
            'guarded-update' => true,
            'hidden'         => true,
        ],
        'updated_at' => [
            'cast'    => 'datetime',
            'hidden'  => true,
        ],
        'deleted_at' => [
            'cast'     => 'datetime',
            'hidden'   => true,
            'rules'    => 'nullable',
        ],
    ];

    /**
     * Protect the Is Admin attribute.
     *
     * There would be logic in here to determine the user or role.
     */
    public function authIsAdminAttribute()
    {
        return false;
    }

    /**
     * Set attribute.
     *
     * @param  bool  $value
     * @return void
     */
    public function setIsAliveAttribute($value)
    {
        $this->attributes['is_alive'] = $this->castAsBool($value);

        return $this;
    }

    /**
     * Check role.
     *
     * @return bool
     */
    public function authCheckRole()
    {
        return false;
    }
}
