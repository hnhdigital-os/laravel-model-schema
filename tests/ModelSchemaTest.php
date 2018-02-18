<?php

namespace HnhDigital\ModelSchema\Tests;

use Illuminate\Container\Container;
use Illuminate\Database\Capsule\Manager as DB;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Events\Dispatcher;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Testing\Fakes\EventFake;
use Illuminate\Support\Facades\Event;
use Illuminate\Translation\FileLoader;
use Illuminate\Translation\Translator;
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
        global $app;

        $dispatcher = new Dispatcher();
        Event::swap($dispatcher);
        Model::setEventDispatcher($dispatcher);
        Model::clearBootedModels();

        $app['translation.loader'] = new FileLoader(new Filesystem(''), 'en');
        $app['translator'] = new Translator($app['translation.loader'], 'en');

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

        $db->schema()->create('mock_model', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->integer('id')->primary()->autoincrement();
            $table->char('uuid', 36)->nullable();
            $table->string('name');
            $table->boolean('is_alive')->default(true);
            $table->boolean('is_admin')->default(false);
            $table->timestamp('created_at')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->timestamp('updated_at')->nullable();
            $table->timestamp('deleted_at')->nullable();
        });
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
            'uuid'        => 'string|nullable',
            'name'        => 'string|min:2|max:255',
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

    /**
     * Assert write access of an attribute.
     *
     * @return void
     */
    public function testDefaultValues()
    {
        $model = new MockModel();

        $this->assertEquals([], $model->getDirty());

        $model->setDefaultValuesForAttributes();

        $dirty = [
            'is_alive' => true,
            'is_admin' => false,
        ];

        $this->assertEquals($dirty, $model->getDirty());
    }

    /**
     * Assert creating a model fails when validation fails.
     *
     * @return void
     *
     * @expectedException HnhDigital\ModelSchema\Exceptions\ValidationException
     */
    public function testCreateModelValidationException()
    {
        $model = MockModel::create([
            'name' => 't',
        ]);

        $this->assertTrue($model->exists());
    }

    /**
     * Assert write access of an attribute.
     *
     * @return void
     */
    public function testCreateModel()
    {
        $model = MockModel::create([
            'name' => 'test',
        ]);

        $this->assertTrue($model->exists());
    }
}
