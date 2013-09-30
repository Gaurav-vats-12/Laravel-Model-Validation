<?php namespace Way\Database;

use Illuminate\Database\Eloquent\Model as Eloquent;
use Illuminate\Validation\Validator;

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
     * Validator instance
     *
     * @var Illuminate\Validation\Validators
     */
    protected $validator;

    public function __construct(array $attributes = array(), Validator $validator = null)
    {
        parent::__construct($attributes);

        $this->validator = $validator ?: \App::make('validator');
    }

    /**
     * Listen for save event
     */
    protected static function boot()
    {
        parent::boot();

        static::saving(function($model)
        {
            return $model->validate();
        });
    }

    /**
     * Validates current attributes against rules
     */
    public function validate()
    {
        $v = $this->validator->make($this->attributes, $this->getRules());

        if ($v->passes())
        {
            return true;
        }

        $this->setErrors($v->messages());

        return false;
    }

    /**
     * Process defined rules for more flexibility
     *
     * @return array
     */
    public function getRules()
    {
        if (empty(static::$rules)) {
            return array();
        }

        // get the model's ID.
        $id = $this->getKey() ?: 'NULL';

        // Replace placeholders
        array_walk(static::$rules, function(&$item) use ($id)
        {
            $item = str_ireplace(':id:', $id, $item);
        });

        return static::$rules;
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

}
