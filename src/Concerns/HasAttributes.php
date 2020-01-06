<?php

namespace HnhDigital\ModelSchema\Concerns;

use HnhDigital\NullCarbon\NullCarbon;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Translation\FileLoader;
use Illuminate\Translation\Translator;
use Illuminate\Validation\Validator;

trait HasAttributes
{
    /**
     * Model custom cast from database definitions.
     *
     * @var array
     */
    protected static $cast_from = [

    ];

    /**
     * Default cast from database definitions.
     *
     * @var array
     */
    protected static $default_cast_from = [
        'uuid'       => 'castAsString',
        'int'        => 'castAsInt',
        'integer'    => 'castAsInt',
        'real'       => 'castAsFloat',
        'float'      => 'castAsFloat',
        'double'     => 'castAsFloat',
        'decimal'    => 'castAsFloat',
        'string'     => 'castAsString',
        'bool'       => 'castAsBool',
        'boolean'    => 'castAsBool',
        'object'     => 'castAsObject',
        'array'      => 'castAsArray',
        'json'       => 'castAsArray',
        'collection' => 'castAsCollection',
        'commalist'  => 'castAsCommaList',
        'date'       => 'castAsDate',
        'datetime'   => 'castAsDateTime',
        'timestamp'  => 'castAsTimestamp',
    ];

    /**
     * Model custom cast to database definitions.
     *
     * @var array
     */
    protected static $cast_to = [

    ];

    /**
     * Default cast to database definitions.
     *
     * @var array
     */
    protected static $default_cast_to = [
        'bool'       => 'castToBoolean',
        'boolean'    => 'castToBoolean',
        'date'       => 'castToDateTime',
        'object'     => 'castToJson',
        'array'      => 'castToJson',
        'json'       => 'castToJson',
        'collection' => 'castToJson',
        'commalist'  => 'castToCommaList',
    ];

    /**
     * Cast type to validation type.
     *
     * @var array
     */
    protected static $cast_validation = [

    ];

    /**
     * Default cast type to validation type.
     *
     * @var array
     */
    protected static $default_cast_validation = [
        'uuid'      => 'string',
        'bool'      => 'boolean',
        'int'       => 'integer',
        'real'      => 'numeric',
        'float'     => 'numeric',
        'double'    => 'numeric',
        'decimal'   => 'numeric',
        'datetime'  => 'date',
        'timestamp' => 'date',
    ];

    /**
     * Validator.
     *
     * @var Validator.
     */
    private $validator;

    /**
     * Set's mising attributes.
     *
     * This covers situations where values have defaults but are not fillable, or
     * date field.
     *
     * @return void
     */
    public function addMissingAttributes()
    {
        foreach ($this->getSchema() as $key => $settings) {
            if (! Arr::has($this->attributes, $key)) {
                Arr::set($this->attributes, $key, Arr::get($settings, 'default', null));
            }
        }
    }

    /**
     * Return a list of the attributes on this model.
     *
     * @return array
     */
    public function getValidAttributes()
    {
        return array_keys($this->getSchema());
    }

    /**
     * Is key a valid attribute?
     *
     * @return bool
     */
    public function isValidAttribute($key)
    {
        return Arr::has($this->getSchema(), $key);
    }

    /**
     * Has write access to a given key on this model.
     *
     * @param string $key
     *
     * @return bool
     */
    public function hasWriteAccess($key)
    {
        if (static::$unguarded) {
            return true;
        }

        // Attribute is guarded.
        if (in_array($key, $this->getGuarded())) {
            return false;
        }

        $method = $this->getAuthMethod($key);

        if ($method !== false) {
            return $this->$method($key);
        }

        // Check for the presence of a mutator for the auth operation
        // which simply lets the developers check if the current user
        // has the authority to update this value.
        if ($this->hasAuthAttributeMutator($key)) {
            $method = 'auth'.Str::studly($key).'Attribute';

            return $this->{$method}($key);
        }

        return true;
    }

    /**
     * Set default values on this attribute.
     *
     * @return $this
     */
    public function setDefaultValuesForAttributes()
    {
        // Only works on new models.
        if ($this->exists) {
            return $this;
        }

        // Defaults on attributes.
        $defaults = $this->getAttributesFromSchema('default', true);

        // Remove attributes that have been given values.
        $defaults = Arr::except($defaults, array_keys($this->getDirty()));

        // Unguard.
        static::unguard();

        // Allocate values.
        foreach ($defaults as $key => $value) {
            $this->{$key} = $value;
        }

        // Reguard.
        static::reguard();

        return $this;
    }

    /**
     * Get the casts array.
     *
     * @return array
     */
    public function getCasts()
    {
        if ($this->getIncrementing()) {
            return array_merge(
                [
                    $this->getKeyName() => $this->getKeyType(),
                ],
                $this->getAttributesFromSchema('cast', true)
            );
        }

        return $this->getAttributesFromSchema('cast', true);
    }

    /**
     * Get the casts params array.
     *
     * @return array
     */
    public function getCastParams()
    {
        if ($this->getIncrementing()) {
            return array_merge(
                [
                    $this->getKeyName() => $this->getKeyType(),
                ],
                $this->getAttributesFromSchema('cast', true)
            );
        }

        return $this->getAttributesFromSchema('cast-params', true);
    }

    /**
     * Cast an attribute to a native PHP type.
     *
     * @param string $key
     * @param mixed  $value
     *
     * @return mixed
     */
    protected function castAttribute($key, $value)
    {
        $method = $this->getCastFromMethod($key);

        if ($method === false) {
            return $value;
        }

        if (stripos($method, 'date') === false && is_null($value)) {
            return $value;
        }

        // Casting method is local.
        if (is_string($method) && method_exists($this, $method)) {
            $paramaters = $this->getCastAsParamaters($key);

            return $this->$method($value, ...$paramaters);
        }

        return $value;
    }

    /**
     * Get the method to cast the value of this given key.
     * (when using getAttribute).
     *
     * @param string $key
     *
     * @return string|array
     */
    protected function getCastFromMethod($key)
    {
        return $this->getCastFromDefinition($this->getCastType($key));
    }

    /**
     * Get the method to cast this attribte type.
     *
     * @param string $type
     *
     * @return string|array|bool
     */
    protected function getCastFromDefinition($type)
    {
        // Custom definitions.
        if (Arr::has(static::$cast_from, $type)) {
            return Arr::get(static::$cast_from, $type);
        }

        // Fallback to default.
        if (Arr::has(static::$default_cast_from, $type)) {
            return Arr::get(static::$default_cast_from, $type);
        }

        return false;
    }

    /**
     * Get the method to cast this attribte tyepca.
     *
     * @param string $type
     *
     * @return string|array|bool
     */
    protected function getCastAsParamaters($key)
    {
        $cast_params = $this->getCastParams();

        $paramaters = explode(':', Arr::get($cast_params, $key, ''));
        $parsed = $this->parseCastParamaters($paramaters);

        return $parsed;
    }

    /**
     * Parse the given cast parameters.
     *
     * @param array $paramaters
     *
     * @return array
     */
    private function parseCastParamaters($paramaters)
    {
        foreach ($paramaters as &$value) {
            // Local callable method. ($someMethod())
            if (substr($value, 0, 1) === '$' && stripos($value, '()') !== false) {
                $method = substr($value, 1, -2);
                $value = is_callable([$this, $method]) ? $this->{$method}() : null;

            // Local attribute. ($some_attribute)
            } elseif (substr($value, 0, 1) === '$') {
                $key = substr($value, 1);
                $value = $this->{$key};

            // Callable function (eg helper). (some_function())
            } elseif (stripos($value, '()') !== false) {
                $method = substr($value, 0, -2);
                $value = is_callable($method) ? $method() : null;
            }

            // String value.
        }

        return $paramaters;
    }

    /**
     * Get the attributes that should be converted to dates.
     *
     * @return array
     */
    public function getDates()
    {
        return $this->cache(__FUNCTION__, function () {
            $casts = $this->getCasts();

            $dates = [];

            foreach ($casts as $key => $cast) {
                if ($cast == 'datetime') {
                    $dates[] = $key;
                }
            }

            return $dates;
        });
    }

    /**
     * Get the auths array.
     *
     * @return array
     */
    protected function getAuths()
    {
        return $this->getAttributesFromSchema('auth', true);
    }

    /**
     * Get auth method.
     *
     * @param string $key
     *
     * @return string|array
     */
    public function getAuthMethod($key)
    {
        if (Arr::has($this->getAuths(), $key)) {
            $method = 'auth'.Str::studly(Arr::get($this->getAuths(), $key));

            return method_exists($this, $method) ? $method : false;
        }

        return false;
    }

    /**
     * Set a given attribute on the model.
     *
     * @param string $key
     * @param mixed  $value
     *
     * @return $this
     */
    public function setAttribute($key, $value)
    {
        // First we will check for the presence of a mutator for the set operation
        // which simply lets the developers tweak the attribute as it is set on
        // the model, such as "json_encoding" an listing of data for storage.
        if ($this->hasSetMutator($key)) {
            $method = 'set'.Str::studly($key).'Attribute';

            return $this->{$method}($value);
        }

        // Get the method that modifies this value before storing.
        $method = $this->getCastToMethod($key);

        // Get the rules for this attribute.
        $rules = $this->getAttributeRules($key);

        // Skip casting if null is allowed.
        if (is_null($value) && stripos($rules, 'nullable') !== false) {
            $this->attributes[$key] = $value;

            return $this;
        }

        // Casting method is local.
        if (is_string($method) && method_exists($this, $method)) {
            $value = $this->$method($key, $value);
        }

        // If this attribute contains a JSON ->, we'll set the proper value in the
        // attribute's underlying array. This takes care of properly nesting an
        // attribute in the array's value in the case of deeply nested items.
        if (Str::contains($key, '->')) {
            return $this->fillJsonAttribute($key, $value);
        }

        $this->attributes[$key] = $value;

        return $this;
    }

    /**
     * Determine if a auth check exists for an attribute.
     *
     * @param string $key
     *
     * @return bool
     */
    public function hasAuthAttributeMutator($key)
    {
        return method_exists($this, 'auth'.Str::studly($key).'Attribute');
    }

    /**
     * Get the method to cast the value of this given key.
     * (when using setAttribute).
     *
     * @param string $key
     *
     * @return string|array
     */
    protected function getCastToMethod($key)
    {
        return $this->getCastToDefinition($this->getCastType($key));
    }

    /**
     * Get the method to cast this attribte back to it's original form.
     *
     * @param string $type
     *
     * @return string|array|bool
     */
    protected function getCastToDefinition($type)
    {
        // Custom definitions.
        if (Arr::has(static::$cast_to, $type)) {
            return Arr::get(static::$cast_to, $type);
        }

        // Fallback to default.
        if (Arr::has(static::$default_cast_to, $type)) {
            return Arr::get(static::$default_cast_to, $type);
        }

        return false;
    }

    /**
     * Cast value as an bool.
     *
     * @param mixed $value
     *
     * @return bool
     */
    protected function castAsBool($value)
    {
        // If value is provided as string verison of true/false, convert to boolean.
        if ($value === 'true' || $value === 'false') {
            $value = $value === 'true';
        }

        return (bool) (int) $value;
    }

    /**
     * Cast value as an int.
     *
     * @param mixed $value
     *
     * @return int
     */
    protected function castAsInt($value)
    {
        return (int) $value;
    }

    /**
     * Cast value as a float.
     *
     * @param mixed $value
     *
     * @return float
     */
    protected function castAsFloat($value)
    {
        switch ((string) $value) {
            case 'Infinity':
                return INF;
            case '-Infinity':
                return -INF;
            case 'NaN':
                return NAN;
        }

        return (float) $value;
    }

    /**
     * Cast value as a strng.
     *
     * @param mixed $value
     *
     * @return string
     */
    protected function castAsString($value)
    {
        return (string) $value;
    }

    /**
     * Cast value .
     *
     * @param mixed $value
     *
     * @return array
     */
    protected function castAsArray($value)
    {
        return $this->fromJson($value);
    }

    /**
     * Cast value as an object.
     *
     * @param mixed $value
     *
     * @return array
     */
    protected function castAsObject($value)
    {
        return $this->fromJson($value, true);
    }

    /**
     * Cast date as a DateTime instance.
     *
     * @return DateTime
     */
    protected function castAsDate($value)
    {
        return $this->castAsDateTime($value);
    }

    /**
     * Return a datetime as DateTime object.
     *
     * @param mixed $value
     *
     * @return \Illuminate\Support\Carbon
     */
    protected function castAsDateTime($value)
    {
        if (is_null($value)) {
            return new NullCarbon();
        }

        return $this->eloquentAsDateTime($value);
    }

    /**
     * Cast comma list to array.
     *
     * @return array
     */
    protected function castAsCommaList($value)
    {
        if (is_array($value)) {
            return $value;
        }

        return explode(',', $value);
    }

    /**
     * Ensure all DateTime casting is redirected.
     *
     * @param mixed $value
     *
     * @return \Illuminate\Support\Carbon
     */
    protected function asDateTime($value)
    {
        return $this->castAsDateTime($value);
    }

    /**
     * Cast to boolean.
     *
     * @return bool
     */
    protected function castToBoolean($key, $value)
    {
        unset($key);

        return $this->castAsBool($value);
    }

    /**
     * Cast to JSON.
     *
     * @return bool
     */
    protected function castToJson($key, $value)
    {
        return $this->castAttributeAsJson($key, $value);
    }

    /**
     * Cast date as a DateTime instance.
     *
     * @return DateTime
     */
    protected function castToDateTime($key, $value)
    {
        unset($key);

        return $this->fromDateTime($value);
    }

    /**
     * Cast array to string.
     *
     * @return array
     */
    protected function castToCommaList($key, $value)
    {
        unset($key);

        if (is_string($value)) {
            return $value;
        }

        return implode(',', $value);
    }

    /**
     * Get the attributes that have been changed since last sync.
     *
     * @return array
     */
    public function getDirty()
    {
        return $this->eloquentGetDirty();
    }

    /**
     * Validate the model before saving.
     *
     * @return array
     */
    public function savingValidation()
    {
        global $app;

        // Create translator if missing.
        if (is_null($app['translator'])) {
            $app['translator'] = new Translator(new FileLoader(new Filesystem, 'lang'), 'en');
        }

        $this->preValidationCast();
        $this->validator = new Validator($app['translator'], $this->getDirty(), $this->getAttributeRules());

        if ($this->validator->fails()) {
            return false;
        }

        return true;
    }

    /**
     * Before validating, ensure the values are correctly casted.
     *
     * Mostly integer or boolean values where they can be set to either.
     * eg 1 for true.
     *
     * @return void
     */
    private function preValidationCast()
    {
        $rules = $this->getAttributeRules();

        // Check each dirty attribute.
        foreach ($this->getDirty() as $key => $value) {
            $this->attributes[$key] = static::preCastAttribute(Arr::get($rules, $key, ''), $value);
        }
    }

    /**
     * Pre-cast attribute to the correct value.
     *
     * @param mixed $rules
     * @param mixed $value
     *
     * @return mixed
     */
    public static function preCastAttribute($rules, $value)
    {
        // Get the rules.
        if (is_string($rules)) {
            $rules = explode('|', $rules);
        }

        // First item is always the cast type.
        $cast_type = Arr::get($rules, 0, false);

        // Check if the value can be nullable.
        $is_nullable = in_array('nullable', $rules);

        return self::castType($cast_type, $value, $is_nullable);
    }

    /**
     * Cast a value to native.
     *
     * @param string $cast_type
     * @param mixed  $value
     * @param bool   $is_nullable
     *
     * @return mixed
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    private static function castType($cast_type, $value, $is_nullable)
    {
        if ($value === 'NULL') {
            $value = null;
        }

        // Is null and allows null.
        if (is_null($value) && $is_nullable) {
            return $value;
        }

        // Boolean type.
        if ($cast_type === 'boolean') {
            $value = $value === 'true' ? true : $value;
            $value = $value === 'false' ? false : $value;
            $value = boolval($value);

            return $value;
        }

        // Numeric type.
        if ($cast_type === 'numeric') {
            return (float) preg_replace('/[^0-9.-]*/', '', $value);
        }

        // Integer type.
        if ($cast_type === 'integer' && is_numeric($value)) {
            return intval($value);
        }

        $value = strval($value);

        // Empty value and allows null.
        if (empty($value) && $is_nullable) {
            $value = null;
        }

        return $value;
    }

    /**
     * Get rules for attributes.
     *
     * @param string|null $attribute_key
     *
     * @return array
     */
    public function getAttributeRules($attribute_key = null)
    {
        $result = [];
        $attributes = $this->getAttributesFromSchema();
        $casts = $this->getCasts();
        $casts_back = $this->getAttributesFromSchema('cast-back', true);
        $rules = $this->getAttributesFromSchema('rules', true);

        // Build full rule for each attribute.
        foreach ($attributes as $key) {
            $result[$key] = [];
        }

        // If any casts back are configured, replace the value found in casts.
        // Handy if we read integer values as datetime, but save back as an integer.
        $casts = array_merge($casts, $casts_back);

        // Build full rule for each attribute.
        foreach ($casts as $key => $cast_type) {
            $cast_validator = $this->parseCastToValidator($cast_type);

            if (! empty($cast_validator)) {
                $result[$key][] = $cast_validator;
            }

            if ($this->exists) {
                $result[$key][] = 'sometimes';
            }
        }

        // Assign specified rules.
        foreach ($rules as $key => $rule) {
            $result[$key][] = $this->verifyRule($rule);
        }

        // Key name could be composite. Only remove from rules if singlular.
        $key_name = $this->getKeyName();

        if (is_string($key_name)) {
            unset($result[$key_name]);
        }

        foreach ($result as $key => $rules) {
            $result[$key] = implode('|', $rules);
        }

        if (! is_null($attribute_key)) {
            return Arr::get($result, $attribute_key, '');
        }

        return $result;
    }

    /**
     * Verify the rules.
     *
     * @param string $rules
     *
     * @return string
     */
    public function verifyRule($rule)
    {
        $rules = explode('|', $rule);

        foreach ($rules as &$entry) {
            if (stripos($entry, 'unique') !== false) {
                if (stripos($entry, ':') === false) {
                    $entry .= ':'.$this->getTable();
                }
            }
        }

        return implode('|', $rules);
    }

    /**
     * Convert attribute type to validation type.
     *
     * @param string $type
     *
     * @return string
     */
    private function parseCastToValidator($type)
    {
        if (Arr::has(static::$cast_validation, $type)) {
            return Arr::get(static::$cast_validation, $type);
        }

        if (Arr::has(static::$default_cast_validation, $type)) {
            return Arr::get(static::$default_cast_validation, $type);
        }

        return $type;
    }

    /**
     * Get the validator instance.
     *
     * @return Validator
     */
    public function getValidator()
    {
        return $this->validator;
    }

    /**
     * Get invalid attributes.
     *
     * @return array
     */
    public function getInvalidAttributes()
    {
        if (is_null($this->getValidator())) {
            return [];
        }

        return $this->getValidator()->errors()->messages();
    }

    /**
     * Dynamically set attributes on the model.
     *
     * @param string $key
     * @param mixed  $value
     *
     * @return void
     */
    public function __set($key, $value)
    {
        if ($this->isValidAttribute($key) && $this->hasWriteAccess($key)) {
            $this->setAttribute($key, $value);
        }
    }

    /**
     * Register cast from database definition.
     *
     * @param string $cast
     * @param mixed  $method
     *
     * @return void
     */
    public static function registerCastFromDatabase($cast, $method)
    {
        Arr::set(static::$cast_from, $cast, $method);
    }

    /**
     * Register cast to database definition.
     *
     * @param string $cast
     * @param mixed  $method
     *
     * @return void
     */
    public static function registerCastToDatabase($cast, $method)
    {
        Arr::set(static::$cast_to, $cast, $method);
    }

    /**
     * Register cast to database definition.
     *
     * @param string $cast
     * @param string $validator
     *
     * @return void
     */
    public static function registerCastValidator($cast, $validator)
    {
        Arr::set(static::$cast_validation, $cast, $validator);
    }
}
