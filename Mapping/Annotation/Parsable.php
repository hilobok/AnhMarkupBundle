<?php

namespace Anh\Bundle\MarkupBundle\Mapping\Annotation;

use Doctrine\Common\Annotations\Annotation;

/**
 * Parsable annotation for Markup behavioral extension
 *
 * @Annotation
 * @Target("PROPERTY")
 */
final class Parsable extends Annotation
{
    /**
     * Type of markup
     * @var string
     * @required
     */
    public $type = 'bbcode';

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
