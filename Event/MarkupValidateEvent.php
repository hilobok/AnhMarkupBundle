<?php

namespace Anh\Bundle\MarkupBundle\Event;

class MarkupValidateEvent extends MarkupEvent
{
    /**
     * Errors
     */
    protected $errors;

    public function getErrors()
    {
        return $this->errors;
    }

    public function setErrors($errors)
    {
        $this->errors = $errors;
    }
}
