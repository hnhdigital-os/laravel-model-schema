<?php

namespace HnhDigital\ModelSchema\Exceptions;

use Illuminate\Support\Arr;

class ValidationException extends \Exception
{
    /**
     * Validator.
     *
     * @var Validator
     */
    protected $validator;

    private static $response = [
        'is_error' => false,
        'feedback' => '',
        'fields'   => [],
        'changes'  => [],
    ];

    /**
     * Exception constructor.
     *
     * @param string         $message
     * @param int            $code
     * @param Exception|null $previous
     * @param Validator|null $validator
     */
    public function __construct($message = null, $code = 0, Exception $previous = null, $validator = null)
    {
        $this->validator = $validator;

        parent::__construct($message, $code, $previous);
    }

    /**
     * Get the validator.
     *
     * @return Validator
     */
    public function getValidator()
    {
        return $this->validator;
    }

    public function setResponse($response)
    {
        self::$response = $response;
    }

    /**
     * Get a response to return.
     *
     * @return string|array
     */
    public function getResponse($route, $parameters, $config = [])
    {
        // Copy standard response.
        $response = self::$response;

        // Fill the response.
        Arr::set($response, 'is_error', true);
        Arr::set($response, 'message', $this->getMessage());
        Arr::set($response, 'fields', array_keys($this->validator->errors()->messages()));
        Arr::set($response, 'feedback', $this->validator->errors()->all());
        Arr::set($response, 'errors', $this->validator->errors());

        if (Arr::has($config, 'feedback.html')) {
            Arr::set(
                $response,
                'feedback',
                '<ul><li>'.implode('</li><li>', Arr::get($response, 'feedback')).'</li></ul>'
            );
        }

        // JSON response required.
        if (request()->ajax() || request()->wantsJson()) {
            return response()
                ->json($response, 422);
        }

        // Redirect response, flash to session.
        session()->flash('is_error', true);
        session()->flash('message', Arr::get($response, 'message', ''));
        session()->flash('feedback', Arr::get($response, 'feedback', ''));
        session()->flash('fields', Arr::get($response, 'fields', []));

        // Redirect to provided route.
        return redirect()
            ->route($route, $parameters)
            ->withErrors($this->getValidator())
            ->withInput();
    }
}
