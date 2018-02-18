<?php

namespace HnhDigital\ModelSchema\Tests;

use Illuminate\Database\Capsule\Manager as DB;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Events\Dispatcher;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Schema;
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
            $table->boolean('enable_notifications')->default(false);
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
            'id'                   => 'integer',
            'uuid'                 => 'uuid',
            'name'                 => 'string',
            'is_alive'             => 'boolean',
            'is_admin'             => 'boolean',
            'enable_notifications' => 'boolean',
            'created_at'           => 'datetime',
            'updated_at'           => 'datetime',
            'deleted_at'           => 'datetime',
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
            'uuid'                 => 'string|nullable',
            'name'                 => 'string|min:2|max:255',
            'is_alive'             => 'boolean',
            'is_admin'             => 'boolean',
            'enable_notifications' => 'boolean',
            'created_at'           => 'date',
            'updated_at'           => 'date',
            'deleted_at'           => 'date|nullable',
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
            'enable_notifications',
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
        ];

        $this->assertEquals($guarded, MockModel::fromSchema('guarded'));

        /**
         * Guarded from updates attributes.
         */
        $guarded = [
            'created_at',
        ];

        $this->assertEquals($guarded, MockModel::fromSchema('guarded-update'));

        /**
         * Date attributes.
         */
        $dates = [
            'created_at',
            'updated_at',
            'deleted_at',
        ];

        $this->assertEquals($dates, $model->getDates());
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

        $this->assertFalse($model->hasWriteAccess('enable_notifications'));
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
            'is_alive'             => true,
            'is_admin'             => false,
            'enable_notifications' => false,
        ];

        $this->assertEquals($dirty, $model->getDirty());

        $model = MockModel::create([
            'name' => 'test',
        ]);

        $model->is_alive = false;

        $this->assertEquals($model, $model->setDefaultValuesForAttributes());
        $this->assertEquals(['is_alive' => false], $model->getDirty());
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
     * Assert creating a model fails when validation fails.
     *
     * @return void
     */
    public function testCreateModelCatchValidationException()
    {
        $model = MockModel::create([
            'name' => 'test',
        ]);

        $this->assertEquals([], $model->getInvalidAttributes());

        $model->name = 't';

        try {
            $model->save();
        } catch (\HnhDigital\ModelSchema\Exceptions\ValidationException $exception) {
        }

        $invalid_attributes = [
            'name' => [
                'validation.min.string',
            ],
        ];

        $this->assertEquals($invalid_attributes, $model->getInvalidAttributes());
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

        $this->assertInstanceOf(\Carbon\Carbon::class, $model->created_at);
        $this->assertInstanceOf(\GeneaLabs\LaravelNullCarbon\NullCarbon::class, $model->deleted_at);

        $model = $model->fresh();

        $this->assertInstanceOf(\Carbon\Carbon::class, $model->created_at);
        $this->assertInstanceOf(\GeneaLabs\LaravelNullCarbon\NullCarbon::class, $model->deleted_at);
    }
}
