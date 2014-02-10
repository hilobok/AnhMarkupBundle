<?php

namespace Anh\MarkupBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class MarkupIsValid extends Constraint
{
    /**
     * Parser type
     *
     * @var string
     */
    public $type;

    /**
     * Options for parser
     *
     * @var array
     */
    public $options = array();

    /**
     * {@inheritdoc}
     */
    public function validatedBy()
    {
        return 'anh_markup_validator';
    }

    /**
     * {@inheritdoc}
     */
    public function getTargets()
    {
        return self::PROPERTY_CONSTRAINT;
    }
}
