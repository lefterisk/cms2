<?php
namespace Administration\Helper\Validator;


abstract class AbstractValidator
{
    protected $errors          = array();
    protected $definition      = array();

    public function __construct($definition)
    {
        $this->definition = $definition;
    }

    public function getErrors()
    {
        return $this->errors;
    }

    public function validate()
    {
        if (count($this->errors) > 0) {
            return false;
        }
        return true;
    }

    protected function isValidVariableName($string)
    {
        if (is_string($string) && !empty($string) && preg_match('/^[A-Za-z0-9_\-]+$/', $string)) {
            return true;
        }
        return false;
    }
}