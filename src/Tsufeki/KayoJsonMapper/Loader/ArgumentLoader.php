<?php declare(strict_types=1);

namespace Tsufeki\KayoJsonMapper\Loader;

use Tsufeki\KayoJsonMapper\Context\Context;
use Tsufeki\KayoJsonMapper\Exception\InvalidDataException;
use Tsufeki\KayoJsonMapper\MetadataProvider\CallableMetadataProvider;

class ArgumentLoader
{
    /**
     * @var Loader
     */
    private $loader;

    /**
     * @var CallableMetadataProvider
     */
    private $callableMetadataProvider;

    public function __construct(Loader $loader, CallableMetadataProvider $callableMetadataProvider)
    {
        $this->loader = $loader;
        $this->callableMetadataProvider = $callableMetadataProvider;
    }

    /**
     * Load arguments for callable.
     *
     * @param array|\stdClass $data
     * @param callable        $callable
     * @param Context         $context
     *
     * @return array Unserialized arguments as positional array.
     */
    public function loadArguments($data, callable $callable, Context $context): array
    {
        if (is_object($data) && $data instanceof \stdClass) {
            $data = get_object_vars($data);
        }

        if (!is_array($data)) {
            throw new InvalidDataException('Argument data must array or stdClass');
        }

        $metadata = $this->callableMetadataProvider->getCallableMetadata($callable);

        $args = [];
        $paramPos = 0;
        $argPos = 0;
        while ($paramPos < count($metadata->parameters)) {
            $param = $metadata->parameters[$paramPos];

            if (array_key_exists($argPos, $data)) {
                $key = $argPos;
            } elseif (array_key_exists($param->name, $data)) {
                $key = $param->name;
            } else {
                if (!$param->optional) {
                    throw new InvalidDataException('Not enough arguments');
                }
                break;
            }

            $args[] = $this->loader->load($data[$key], $param->type, $context);
            unset($data[$key]);

            $argPos++;
            if (!$param->variadic) {
                $paramPos++;
            }
        }

        if (!empty($data)) {
            throw new InvalidDataException(
                'Some arguments could not be matched: ' . implode(', ', array_keys($data))
            );
        }

        return $args;
    }
}