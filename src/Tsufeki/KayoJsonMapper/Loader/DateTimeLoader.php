<?php declare(strict_types=1);

namespace Tsufeki\KayoJsonMapper\Loader;

use phpDocumentor\Reflection\Type;
use Tsufeki\KayoJsonMapper\Context\Context;
use Tsufeki\KayoJsonMapper\Exception\BadDateTimeFormatException;
use Tsufeki\KayoJsonMapper\Exception\TypeMismatchException;
use Tsufeki\KayoJsonMapper\Exception\UnsupportedTypeException;

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

    public function getSupportedTypes(): array
    {
        return ['\\DateTime'];
    }

    public function load($data, Type $type, Context $context)
    {
        if ((string)$type !== '\\DateTime') {
            throw new UnsupportedTypeException();
        }

        if (!is_string($data)) {
            throw new TypeMismatchException('string', $data, $context);
        }

        $result = \DateTime::createFromFormat($this->format, $data);
        if ($result === false) {
            throw new BadDateTimeFormatException($this->format, \DateTime::getLastErrors()['errors'], $data, $context);
        }

        return $result;
    }
}
