<?php declare(strict_types=1);

namespace Tsufeki\KayoJsonMapper;

use Tsufeki\KayoJsonMapper\Dumper\ArrayDumper;
use Tsufeki\KayoJsonMapper\Dumper\DateTimeDumper;
use Tsufeki\KayoJsonMapper\Dumper\DispatchingDumper;
use Tsufeki\KayoJsonMapper\Dumper\Dumper;
use Tsufeki\KayoJsonMapper\Dumper\ObjectDumper;
use Tsufeki\KayoJsonMapper\Dumper\ScalarDumper;
use Tsufeki\KayoJsonMapper\Loader\ArrayLoader;
use Tsufeki\KayoJsonMapper\Loader\DateTimeLoader;
use Tsufeki\KayoJsonMapper\Loader\DispatchingLoader;
use Tsufeki\KayoJsonMapper\Loader\Instantiator\ClassMappingInstantiator;
use Tsufeki\KayoJsonMapper\Loader\Instantiator\Instantiator;
use Tsufeki\KayoJsonMapper\Loader\Loader;
use Tsufeki\KayoJsonMapper\Loader\MixedLoader;
use Tsufeki\KayoJsonMapper\Loader\ObjectLoader;
use Tsufeki\KayoJsonMapper\Loader\ScalarLoader;
use Tsufeki\KayoJsonMapper\Loader\UnionLoader;
use Tsufeki\KayoJsonMapper\MetadataProvider\AccessorStrategy\AccessorStrategy;
use Tsufeki\KayoJsonMapper\MetadataProvider\AccessorStrategy\StandardAccessorStrategy;
use Tsufeki\KayoJsonMapper\MetadataProvider\CachedClassMetadataProvider;
use Tsufeki\KayoJsonMapper\MetadataProvider\CallableMetadataProvider;
use Tsufeki\KayoJsonMapper\MetadataProvider\ClassMetadataProvider;
use Tsufeki\KayoJsonMapper\MetadataProvider\Phpdoc\PhpdocTypeExtractor;
use Tsufeki\KayoJsonMapper\MetadataProvider\ReflectionCallableMetadataProvider;
use Tsufeki\KayoJsonMapper\MetadataProvider\ReflectionClassMetadataProvider;

class MapperBuilder
{
    /**
     * @var AccessorStrategy|null
     */
    private $accessorStrategy;

    /**
     * @var CallableMetadataProvider|null
     */
    private $callableMetadataProvider;

    /**
     * @var ClassMetadataProvider|null
     */
    private $classMetadataProvider;

    /**
     * @var Instantiator|null
     */
    private $instantiator;

    /**
     * @var array<string,string|callable>
     */
    private $classMappings = [];

    /**
     * @var Loader[]
     */
    private $loaders = [];

    /**
     * @var Dumper[]
     */
    private $dumpers = [];

    /**
     * @var string
     */
    private $dateTimeFormat = \DateTime::RFC3339;

    /**
     * @var int|float
     */
    private $dumpMaxDepth = INF;

    public static function create(): self
    {
        return new static();
    }

    /**
     * @param AccessorStrategy $accessorStrategy
     *
     * @return $this
     */
    public function setAccessorStrategy(AccessorStrategy $accessorStrategy): self
    {
        $this->accessorStrategy = $accessorStrategy;

        return $this;
    }

    /**
     * @param CallableMetadataProvider $callableMetadataProvider
     *
     * @return $this
     */
    public function setCallableMetadataProvider(CallableMetadataProvider $callableMetadataProvider): self
    {
        $this->callableMetadataProvider = $callableMetadataProvider;

        return $this;
    }

    /**
     * @param ClassMetadataProvider $classMetadataProvider
     *
     * @return $this
     */
    public function setClassMetadataProvider(ClassMetadataProvider $classMetadataProvider): self
    {
        $this->classMetadataProvider = $classMetadataProvider;

        return $this;
    }

    /**
     * @param Instantiator $instantiator
     *
     * @return $this
     */
    public function setInstantiator(Instantiator $instantiator): self
    {
        $this->instantiator = $instantiator;

        return $this;
    }

    /**
     * @param string $class
     * @param string $targetClass
     *
     * @return $this
     */
    public function addClassMapping(string $class, string $targetClass): self
    {
        $this->classMappings[$class] = $targetClass;

        return $this;
    }

    /**
     * @param string   $class
     * @param callable $callback (\stdClass $data) -> string class name
     *
     * @return $this
     */
    public function addClassMappingCallback(string $class, callable $callback): self
    {
        $this->classMappings[$class] = $callback;

        return $this;
    }

    /**
     * @param Loader $loader
     *
     * @return $this
     */
    public function addLoader(Loader $loader): self
    {
        $this->loaders[] = $loader;

        return $this;
    }

    /**
     * @param Dumper $dumper
     *
     * @return $this
     */
    public function addDumper(Dumper $dumper): self
    {
        $this->dumpers[] = $dumper;

        return $this;
    }

    /**
     * @param string $dateTimeFormat
     *
     * @return $this
     */
    public function setDateTimeFormat(string $dateTimeFormat): self
    {
        $this->dateTimeFormat = $dateTimeFormat;

        return $this;
    }

    /**
     * @param int $dumpMaxDepth
     *
     * @return $this
     */
    public function setDumpMaxDepth(int $dumpMaxDepth): self
    {
        $this->dumpMaxDepth = $dumpMaxDepth;

        return $this;
    }

    public function getMapper(): Mapper
    {
        $phpdocTypeExtractor = new PhpdocTypeExtractor();
        $accessorStrategy = $this->accessorStrategy ?? new StandardAccessorStrategy();
        $callableMetadataProvider = $this->callableMetadataProvider ?? new ReflectionCallableMetadataProvider($phpdocTypeExtractor);

        $classMetadataProvider = $this->classMetadataProvider ?? new CachedClassMetadataProvider(
            new ReflectionClassMetadataProvider(
                $callableMetadataProvider,
                $accessorStrategy,
                $phpdocTypeExtractor
            )
        );

        $instantiator = $this->instantiator;
        if ($instantiator === null) {
            $instantiator = new ClassMappingInstantiator();

            foreach ($this->classMappings as $class => $target) {
                if (is_callable($target)) {
                    $instantiator->addCallback($class, $target);
                } else {
                    $instantiator->addMapping($class, $target);
                }
            }
        }

        $loader = new DispatchingLoader();
        $loader
            ->add(new UnionLoader($loader))
            ->add(new MixedLoader())
            ->add(new ScalarLoader())
            ->add(new ArrayLoader($loader))
            ->add(new ObjectLoader($loader, $classMetadataProvider, $instantiator))
            ->add(new DateTimeLoader($this->dateTimeFormat));

        foreach ($this->loaders as $userLoader) {
            $loader->add($userLoader);
        }

        $dumper = new DispatchingDumper($this->dumpMaxDepth);
        $dumper
            ->add(new ScalarDumper())
            ->add(new ArrayDumper($dumper))
            ->add(new ObjectDumper($dumper, $classMetadataProvider))
            ->add(new DateTimeDumper($this->dateTimeFormat));

        foreach ($this->dumpers as $userDumper) {
            $dumper->add($userDumper);
        }

        return new Mapper($loader, $dumper, $callableMetadataProvider);
    }
}
