<?php

namespace Anh\Bundle\MarkupBundle\Validator;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class Markup extends Constraint
{
    public $type;

    public function validatedBy()
    {
        return 'anh_markup_validator';
    }

    public function getTargets()
    {
        return self::PROPERTY_CONSTRAINT;
    }
}
