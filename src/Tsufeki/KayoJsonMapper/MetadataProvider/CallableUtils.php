<?php declare(strict_types=1);

namespace Tsufeki\KayoJsonMapper\MetadataProvider;

final class CallableUtils
{
    /**
     * Return unique string key for given callable or null for closures.
     *
     * @param callable|\ReflectionFunctionAbstract $callable
     *
     * @return string|null
     */
    public static function getKey($callable)
    {
        /** @var string|null $class */
        $class = null;
        /** @var string|null $method */
        $method = null;

        if (is_string($callable)) {
            $method = $callable; // works for 'Cls::method' case as well
        } elseif (is_array($callable)) {
            $class = is_object($callable[0]) ? get_class($callable[0]) : $callable[0];
            $method = $callable[1];
        } elseif (is_object($callable)) {
            if ($callable instanceof \ReflectionFunctionAbstract) {
                if ($callable->isClosure()) {
                    return null;
                }

                $class = $callable instanceof \ReflectionMethod ? $callable->class : null;
                $method = $callable->name;
            } else {
                if ($callable instanceof \Closure) {
                    return null;
                }

                $class = get_class($callable);
                $method = '__invoke';
            }
        }

        return strtolower(($class ? $class . '::' : '') . $method);
    }

    // @codeCoverageIgnoreStart
    private function __construct()
    {
    }

    // @codeCoverageIgnoreEnd
}
