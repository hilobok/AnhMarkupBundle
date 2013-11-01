<?php

namespace Anh\Bundle\MarkupBundle\Validator;

use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Constraint;
use Anh\Bundle\MarkupBundle\Parser;

class MarkupValidator extends ConstraintValidator
{
    protected $parser;

    public function __construct(Parser $parser)
    {
        $this->parser = $parser;
    }

    public function validate($markup, Constraint $constraint)
    {
        $errors = (array) $this->parser->validate(
            $constraint->type,
            $markup,
            $constraint->options
        );

        foreach ($errors as $message) {
            $this->context->addViolation($message);
        }
    }
}
