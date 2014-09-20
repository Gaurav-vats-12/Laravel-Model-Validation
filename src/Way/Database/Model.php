<?php namespace Way\Database;

use Illuminate\Database\Eloquent\Model as Eloquent;
use Illuminate\Validation\Validator;
use Illuminate\Hashing\BcryptHasher as Hash;

class Model extends Eloquent {

    /**
     * Error message bag
     * 
     * @var Illuminate\Support\MessageBag
     */
    protected $errors;

    /**
     * Validation rules
     * 
     * @var Array
     */
    protected static $rules = array();

    /**
     * Custom messages
     * 
     * @var Array
     */
    protected static $messages = array();

    /**
     * Validator instance
     * 
     * @var Illuminate\Validation\Validators
     */
    protected $validator, $hasher;

    public function __construct(array $attributes = array(), Validator $validator = null, Hash $hasher = null)
    {
        parent::__construct($attributes);

        $this->validator = $validator ?: \App::make('validator');
        $this->hasher = $hasher ?: \App::make('hash');
    }

    /**
     * Listen for save event
     */
    protected static function boot()
    {
        parent::boot();

        static::saving(function($model)
        {
            // Returning true would prevent other event listeners from firing

            return $model->validate() ? null : false;
        });
    }

    /**
     * Validates current attributes against rules
     */
    public function validate()
    {
        $v = $this->validator->make($this->attributes, static::$rules, static::$messages);

        if ($v->passes())
        {
            foreach ($this->attributes as $key => $value) {
                if ($this->endsWith($key, '_hash'))
                    $this->attributes[$key] = $this->hasher->make($value);
            }

            return true;
        }

        $this->setErrors($v->messages());

        return false;
    }

    /**
     * Set error message bag
     * 
     * @var Illuminate\Support\MessageBag
     */
    protected function setErrors($errors)
    {
        $this->errors = $errors;
    }

    /**
     * Retrieve error message bag
     */
    public function getErrors()
    {
        return $this->errors;
    }

    /**
     * Inverse of wasSaved
     */
    public function hasErrors()
    {
        return ! empty($this->errors);
    }

    protected static function endsWith($haystack, $needle)
    {
        $length = strlen($needle);
        if ($length == 0) {
            return true;
        }

        return (substr($haystack, -$length) === $needle);
    }

}
