<?php declare(strict_types=1);

namespace Tsufeki\KayoJsonMapper\Instantiator;

use Tsufeki\KayoJsonMapper\Instantiator;

class ClassMappingInstantiator implements Instantiator
{
    /**
     * @var array<string,string|callable>
     */
    private $classMap = [];

    /**
     * @param string $class
     * @param string $targetClass
     *
     * @return $this
     */
    public function addMapping(string $class, string $targetClass): self
    {
        $this->classMap[$class] = $targetClass;

        return $this;
    }

    /**
     * @param string   $class
     * @param callable $callback (\stdClass $data) -> string class name
     *
     * @return $this
     */
    public function addCallback(string $class, callable $callback): self
    {
        $this->classMap[$class] = $callback;

        return $this;
    }

    public function instantiate(string $class, \stdClass $data)
    {
        $class = $this->classMap[$class] ?? $class;
        if (is_callable($class)) {
            $class = $class($data);
        }

        return new $class();
    }
}
