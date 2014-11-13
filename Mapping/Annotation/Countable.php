<?php

namespace Anh\MarkupBundle\Mapping\Annotation;

use Doctrine\Common\Annotations\Annotation;

/**
 * Parsable annotation for Markup behavioral extension
 *
 * @Annotation
 * @Target("PROPERTY")
 */
final class Countable extends Annotation
{
    /**
     * Type of markup
     * @var string
     * @required
     */
    public $type = '';

    /**
     * Source field from where markup will be parsed
     * @var string
     * @required
     */
    public $field = '';

    /**
     * Parser options
     * @var array
     */
    public $options = array();
}
