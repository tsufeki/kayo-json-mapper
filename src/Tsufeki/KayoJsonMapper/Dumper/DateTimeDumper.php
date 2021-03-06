<?php declare(strict_types=1);

namespace Tsufeki\KayoJsonMapper\Dumper;

use Tsufeki\KayoJsonMapper\Context\Context;
use Tsufeki\KayoJsonMapper\Exception\UnsupportedTypeException;

class DateTimeDumper implements Dumper
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

    public function dump($value, Context $context)
    {
        if (!($value instanceof \DateTime)) {
            throw new UnsupportedTypeException();
        }

        return $value->format($this->format);
    }
}
