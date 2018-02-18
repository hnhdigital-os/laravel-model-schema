<?php

namespace HnhDigital\ModelSchema\Tests;

use Illuminate\Database\Capsule\Manager as DB;
use PHPUnit\Framework\TestCase;

class ModelSchemaTest extends TestCase
{
    /**
     * Setup required for tests.
     *
     * @return void
     */
    public function setUp()
    {
        $this->configureDatabase();
    }

    /**
     * Configure database.
     *
     * @return void
     */
    private function configureDatabase()
    {
        $db = new DB();

        $db->addConnection([
            'driver'    => 'sqlite',
            'database'  => ':memory:',
            'charset'   => 'utf8',
            'collation' => 'utf8_unicode_ci',
            'prefix'    => '',
        ]);

        $db->bootEloquent();
        $db->setAsGlobal();

        $this->pdo = DB::connection()->getPdo();
    }

    /**
     * Assert a number of simple string searches.
     *
     * @return void
     */
    public function testSchema()
    {
        $model = new MockModel();

        $this->assertEquals($model->getSchema(), MockModel::schema());
    }

    /**
     * Assert data based on model's schema returns correctly via static or instantiated model.
     *
     * @return void
     */
    public function testData()
    {
        $model = new MockModel();

        /**
         * Casts.
         */
        $casts = [
            'id'          => 'integer',
            'uuid'        => 'uuid',
            'name'        => 'string',
            'is_alive'    => 'boolean',
            'is_admin'    => 'boolean',
            'created_at'  => 'datetime',
            'updated_at'  => 'datetime',
            'deleted_at'  => 'datetime',
        ];

        $this->assertEquals($casts, MockModel::fromSchema('cast', true));
        $this->assertEquals($casts, $model->getCasts());

        /**
         * Fillable.
         */
        $fillable = [
            'name',
        ];

        $this->assertEquals($fillable, $model->getFillable());

        /**
         * Rules.
         */
        $rules = [
            'uuid'        => 'uuid',
            'name'        => 'string|max:255',
            'is_alive'    => 'boolean',
            'is_admin'    => 'boolean',
            'created_at'  => 'date',
            'updated_at'  => 'date',
            'deleted_at'  => 'date|nullable',
        ];

        $this->assertEquals($rules, $model->getAttributeRules());

        /**
         * Attributes.
         */
        $attributes = [
            'id',
            'uuid',
            'name',
            'is_alive',
            'is_admin',
            'created_at',
            'updated_at',
            'deleted_at',
        ];

        $this->assertEquals($attributes, $model->getValidAttributes());

        /**
         * Guarded attributes.
         */
        $guarded = [
            'id',
            'uuid',
            'created_at',
            'updated_at',
        ];

        $this->assertEquals($guarded, MockModel::fromSchema('guarded'));
    }

    /**
     * Assert a number of checks using attributes that exist or not exist against this model.
     *
     * @return void
     */
    public function testAttributes()
    {
        $model = new MockModel();

        $this->assertTrue($model->isValidAttribute('name'));
        $this->assertFalse($model->isValidAttribute('name1'));
        $this->assertTrue($model->hasWriteAccess('name'));
        $this->assertFalse($model->hasWriteAccess('id'));
    }

    /**
     * Assert a number of checks using boolean values.
     *
     * @return void
     */
    public function testCasting()
    {
        $model = new MockModel();

        $model->is_alive = true;
        $this->assertEquals(true, $model->getAttributes('is_alive'));

        $model->is_alive = false;
        $this->assertEquals(false, $model->getAttributes('is_alive'));

        $model->is_alive = '0';
        $this->assertEquals(false, $model->getAttributes('is_alive'));

        $model->is_alive = '1';
        $this->assertEquals(true, $model->getAttributes('is_alive'));
    }

    /**
     * Assert changing the fillable state of an attribute.
     *
     * @return void
     */
    public function testChangingFillableState()
    {
        $model = new MockModel();

        /**
         * Pre-change Fillable.
         */
        $fillable = [
            'name',
        ];

        $this->assertEquals($fillable, $model->getFillable());

        $model->fillable(['is_alive']);

        /**
         * Post change Fillable.
         */
        $fillable = [
            'is_alive',
        ];

        $this->assertEquals($fillable, $model->getFillable());
    }

    /**
     * Assert changing the guarded state of an attribute.
     *
     * @return void
     */
    public function testChangingGuardedState()
    {
        $model = new MockModel();

        /**
         * Pre-change guarded.
         */
        $guarded = [
            'id',
            'uuid',
            'created_at',
            'updated_at',
        ];

        $this->assertEquals($guarded, $model->getGuarded());

        $model->guard(['id']);

        /**
         * Post change guarded.
         */
        $guarded = [
            'id',
        ];

        $this->assertEquals($guarded, $model->getGuarded());
    }

    /**
     * Assert write access of an attribute.
     *
     * @return void
     */
    public function testWriteAccess()
    {
        $model = new MockModel();

        $this->assertFalse($model->hasWriteAccess('id'));
        $this->assertTrue($model->hasWriteAccess('name'));

        MockModel::unguard();
        $this->assertTrue($model->hasWriteAccess('id'));
        MockModel::reguard();

        $this->assertFalse($model->hasWriteAccess('is_admin'));
    }
}
