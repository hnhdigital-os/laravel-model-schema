<?php

namespace HnhDigital\ModelSchema\Exceptions;

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
        array_set($response, 'is_error', true);
        array_set($response, 'message', $this->getMessage());
        array_set($response, 'fields', array_keys($this->validator->errors()->messages()));
        array_set($response, 'feedback', $this->validator->errors()->all());
        array_set($response, 'errors', $this->validator->errors());

        if (array_has($config, 'feedback.html')) {
            array_set($response, 'feedback', '<ul><li>'.implode('</li><li>', array_get($response, 'feedback')).'</li></ul>');
        }

        // JSON response required.
        if (request()->ajax() || request()->wantsJson()) {
            return response()
                ->json($response, 422);
        }

        // Redirect response, flash to session.
        session()->flash('is_error', true);
        session()->flash('message', array_get($response, 'message', ''));
        session()->flash('feedback', array_get($response, 'feedback', ''));
        session()->flash('fields', array_get($response, 'fields', []));

        // Redirect to provided route.
        return redirect()
            ->route($route, $parameters)
            ->withErrors($this->getValidator())
            ->withInput();
    }
}
