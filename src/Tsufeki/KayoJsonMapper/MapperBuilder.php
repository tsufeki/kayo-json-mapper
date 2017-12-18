<?php declare(strict_types=1);

namespace Tsufeki\KayoJsonMapper;

use Tsufeki\KayoJsonMapper\Context\ContextFactory;
use Tsufeki\KayoJsonMapper\Dumper\ArrayDumper;
use Tsufeki\KayoJsonMapper\Dumper\DateTimeDumper;
use Tsufeki\KayoJsonMapper\Dumper\DispatchingDumper;
use Tsufeki\KayoJsonMapper\Dumper\Dumper;
use Tsufeki\KayoJsonMapper\Dumper\ObjectDumper;
use Tsufeki\KayoJsonMapper\Dumper\ScalarDumper;
use Tsufeki\KayoJsonMapper\Loader\ArrayLoader;
use Tsufeki\KayoJsonMapper\Loader\DateTimeLoader;
use Tsufeki\KayoJsonMapper\Loader\DispatchingLoader;
use Tsufeki\KayoJsonMapper\Loader\Instantiator\Instantiator;
use Tsufeki\KayoJsonMapper\Loader\Instantiator\NewInstantiator;
use Tsufeki\KayoJsonMapper\Loader\Loader;
use Tsufeki\KayoJsonMapper\Loader\MixedLoader;
use Tsufeki\KayoJsonMapper\Loader\ObjectLoader;
use Tsufeki\KayoJsonMapper\Loader\ReplacingLoader;
use Tsufeki\KayoJsonMapper\Loader\ScalarLoader;
use Tsufeki\KayoJsonMapper\Loader\UnionLoader;
use Tsufeki\KayoJsonMapper\MetadataProvider\AccessorStrategy\AccessorStrategy;
use Tsufeki\KayoJsonMapper\MetadataProvider\AccessorStrategy\StandardAccessorStrategy;
use Tsufeki\KayoJsonMapper\MetadataProvider\CachedClassMetadataProvider;
use Tsufeki\KayoJsonMapper\MetadataProvider\CallableMetadataProvider;
use Tsufeki\KayoJsonMapper\MetadataProvider\ClassMetadataProvider;
use Tsufeki\KayoJsonMapper\MetadataProvider\ManglingClassMetadataProvider;
use Tsufeki\KayoJsonMapper\MetadataProvider\NameMangler\CamelCaseToUnderscoreNameMangler;
use Tsufeki\KayoJsonMapper\MetadataProvider\NameMangler\NameMangler;
use Tsufeki\KayoJsonMapper\MetadataProvider\Phpdoc\PhpdocTypeExtractor;
use Tsufeki\KayoJsonMapper\MetadataProvider\ReflectionCallableMetadataProvider;
use Tsufeki\KayoJsonMapper\MetadataProvider\ReflectionClassMetadataProvider;

/**
 * Configure and build Mapper object.
 */
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
     * @var NameMangler
     */
    private $nameMangler;

    /**
     * @var Instantiator|null
     */
    private $instantiator;

    /**
     * @var array<string,string>
     */
    private $typeReplacements = [];

    /**
     * @var array<string,callable>
     */
    private $typeReplacementCallbacks = [];

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

    /**
     * @var bool
     */
    private $throwOnInfiniteRecursion = false;

    /**
     * @var bool
     */
    private $throwOnMaxDepthExceeded = false;

    /**
     * @var bool
     */
    private $throwOnMissingProperty = false;

    /**
     * @var bool
     */
    private $throwOnUnknownProperty = true;

    public static function create(): self
    {
        return new static();
    }

    /**
     * Set accessor strategy for finding getters/setters of private properties.
     *
     * Default one checks getProp(), isProp() and prop() for getters, setProp()
     * for setter.
     *
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
     * Set metadata provider for callables.
     *
     * Default one uses reflection and doc comments.
     *
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
     * Set metadata provider for classes.
     *
     * Default one uses reflection and doc comments.
     *
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
     * Set property name mangler.
     *
     * Default one changes camel case to underscores (fooBar -> foo_bar).
     *
     * @param NameMangler $nameMangler
     *
     * @return $this
     */
    public function setNameMangler(NameMangler $nameMangler)
    {
        $this->nameMangler = $nameMangler;

        return $this;
    }

    /**
     * Set factory of new objects.
     *
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
     * Substitute loaded type with another.
     *
     * Useful when properties are typed with abstract class/interface, e.g.
     * DateTimeInterface => DateTime.
     *
     * Accepts any phpdoc-style types, but doesn't any special normalization
     * when matching. Works only during loading.
     *
     * @param string $replacedType
     * @param string $replacingType
     *
     * @return $this
     */
    public function replaceType(string $replacedType, string $replacingType): self
    {
        $this->typeReplacements[$replacedType] = $replacingType;

        return $this;
    }

    /**
     * Substitute loaded type with another.
     *
     * Same as `replaceType()` but with callback which will receive loaded data
     * and replaced type and should return replacing type as string.
     *
     * Accepts any phpdoc-style types, but doesn't any special normalization
     * when matching. Works only during loading.
     *
     * @param string   $replacedType
     * @param callable $replacingTypeCallback ($data, string $type) -> string $replacingType
     *
     * @return $this
     */
    public function replaceTypeCallback(string $replacedType, callable $replacingTypeCallback): self
    {
        $this->typeReplacementCallbacks[$replacedType] = $replacingTypeCallback;

        return $this;
    }

    /**
     * Add custom loader.
     *
     * Loader added last has the highest priority.
     *
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
     * Add custom dumper.
     *
     * Dumper added last has the highest priority.
     *
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
     * Set date format for loading\dumping DateTime objects.
     *
     * The same format as for `date()`. Default is \DateTime::RFC3339.
     *
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
     * Set max recursion depth for dumping.
     *
     * Default is no limit.
     *
     * @param int $dumpMaxDepth
     *
     * @return $this
     */
    public function setDumpMaxDepth(int $dumpMaxDepth): self
    {
        $this->dumpMaxDepth = $dumpMaxDepth;

        return $this;
    }

    /**
     * Whether to throw exception or return null when infinite recursion is detected.
     *
     * Default false.
     *
     * @param bool $throwOnInfiniteRecursion
     *
     * @return $this
     */
    public function throwOnInfiniteRecursion(bool $throwOnInfiniteRecursion): self
    {
        $this->throwOnInfiniteRecursion = $throwOnInfiniteRecursion;

        return $this;
    }

    /**
     * Whether to throw exception or return null when max depth is exceeded.
     *
     * Default false.
     *
     * @param bool $throwOnMaxDepthExceeded
     *
     * @return $this
     */
    public function throwOnMaxDepthExceeded(bool $throwOnMaxDepthExceeded): self
    {
        $this->throwOnMaxDepthExceeded = $throwOnMaxDepthExceeded;

        return $this;
    }

    /**
     * Whether to throw exception when a property is missing from loaded data.
     *
     * Default false.
     *
     * @param bool $throwOnMissingProperty
     *
     * @return $this
     */
    public function throwOnMissingProperty(bool $throwOnMissingProperty): self
    {
        $this->throwOnMissingProperty = $throwOnMissingProperty;

        return $this;
    }

    /**
     * Whether to throw exception when unknown property is encountered in loaded data.
     *
     * Default true.
     *
     * @param bool $throwOnUnknownProperty
     *
     * @return $this
     */
    public function throwOnUnknownProperty(bool $throwOnUnknownProperty): self
    {
        $this->throwOnUnknownProperty = $throwOnUnknownProperty;

        return $this;
    }

    /**
     * Build Mapper.
     */
    public function getMapper(): Mapper
    {
        $phpdocTypeExtractor = new PhpdocTypeExtractor();
        $accessorStrategy = $this->accessorStrategy ?? new StandardAccessorStrategy();
        $nameMangler = $this->nameMangler ?? new CamelCaseToUnderscoreNameMangler();
        $callableMetadataProvider = $this->callableMetadataProvider ?? new ReflectionCallableMetadataProvider($phpdocTypeExtractor);

        $classMetadataProvider = $this->classMetadataProvider ?? new CachedClassMetadataProvider(
            new ManglingClassMetadataProvider(
                new ReflectionClassMetadataProvider(
                    $callableMetadataProvider,
                    $accessorStrategy,
                    $phpdocTypeExtractor
                ),
                $nameMangler
            )
        );

        $instantiator = $this->instantiator ?? new NewInstantiator();

        $loader = new DispatchingLoader();
        $loader
            ->add(new UnionLoader($loader))
            ->add(new MixedLoader())
            ->add(new ScalarLoader())
            ->add(new ArrayLoader($loader))
            ->add(new ObjectLoader(
                $loader,
                $classMetadataProvider,
                $instantiator,
                $this->throwOnUnknownProperty,
                $this->throwOnMissingProperty
            ))
            ->add(new DateTimeLoader($this->dateTimeFormat));

        foreach ($this->loaders as $userLoader) {
            $loader->add($userLoader);
        }

        if (!empty($this->typeReplacements) || !empty($this->typeReplacementCallbacks)) {
            $replacingLoader = new ReplacingLoader($loader);

            foreach ($this->typeReplacements as $replacedType => $replacingType) {
                $replacingLoader->replaceType($replacedType, $replacingType);
            }
            foreach ($this->typeReplacementCallbacks as $replacedType => $replacingTypeCallback) {
                $replacingLoader->replaceTypeCallback($replacedType, $replacingTypeCallback);
            }

            $loader->add($replacingLoader);
        }

        $dumper = new DispatchingDumper(
            $this->throwOnMaxDepthExceeded,
            $this->throwOnInfiniteRecursion
        );
        $dumper
            ->add(new ScalarDumper())
            ->add(new ArrayDumper($dumper))
            ->add(new ObjectDumper($dumper, $classMetadataProvider))
            ->add(new DateTimeDumper($this->dateTimeFormat));

        foreach ($this->dumpers as $userDumper) {
            $dumper->add($userDumper);
        }

        $contextFactory = new ContextFactory($this->dumpMaxDepth);

        return new Mapper($loader, $dumper, $contextFactory, $callableMetadataProvider);
    }
}
