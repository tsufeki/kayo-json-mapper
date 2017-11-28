<?php declare(strict_types=1);

namespace Tsufeki\KayoJsonMapper\Loader;

use phpDocumentor\Reflection\Type;
use phpDocumentor\Reflection\Types;
use Tsufeki\KayoJsonMapper\Exception\TypeMismatchException;
use Tsufeki\KayoJsonMapper\Exception\UnsupportedTypeException;
use Tsufeki\KayoJsonMapper\Loader;
use Tsufeki\KayoJsonMapper\Context;

class DateTimeLoader implements Loader
{
    /**
     * @var string
     */
    private $format;

    public function __construct(string $format = \DateTime::RFC3339)
    {
        $this->format = $format;
    }

    public function load($data, Type $type, Context $context)
    {
        if (!($type instanceof Types\Object_) || (string)$type !== '\\DateTime') {
            throw new UnsupportedTypeException();
        }

        if (!is_string($data)) {
            throw new TypeMismatchException();
        }

        $result = \DateTime::createFromFormat($this->format, $data);
        if ($result === false) {
            $errors = \DateTime::getLastErrors()['errors'];

            throw new TypeMismatchException('Bad datetime format: ' . implode('; ', $errors));
        }

        return $result;
    }
}
