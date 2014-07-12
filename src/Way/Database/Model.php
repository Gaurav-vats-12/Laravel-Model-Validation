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
        // if the key's value is greater than 0, then its an existing model
        // so we will replace the placeholder (:id) with the id value
        // otherwise we will just replace it with an empty string
        $replace = ($this->getKey() > 0) ? $this->getKey() : '';
        foreach (static::$rules as $key => $rule)
        {
            static::$rules[$key] = str_replace(':id', $replace, $rule);
        }
    
        $v = $this->validator->make($this->attributes, static::$rules, static::$messages);

        if ($v->passes())
        {
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
    
    /**
     * Check if $field has rules
     * 
     * @var String
     *
     * return Array|Boolean(false)
     */
    public function hasRules($field) {
        return isset(static::$rules[$field]) ? (is_array(static::$rules[$field]) ? static::$rules[$field] : [static::$rules[$field]]) : false;
    }
    
    /**
     * Check if $field has $type rules
     *
     * @var String
     * @var String|Array
     *
     * return Boolean
     */
    public function is($field, $type) {
        $is = false;
        
        if ($this->hasRules($field)) {            
            foreach ($this->hasRules($field) as $rule)
            {
                if (is_array($type))
                {
                    foreach ($type as $kind) {
                        $pos = strpos($rule, $kind);
                        
                        if ($pos !== false)
                        {
                            $is = true;
                        }
                    }
                } else {                
                    $pos = strpos($rule, $type);
                    
                    if ($pos !== false)
                    {
                        $is = true;
                    }
                }
            }
        }
        
        return $is;
    }
}
