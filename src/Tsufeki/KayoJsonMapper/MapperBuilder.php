<?php declare(strict_types=1);

namespace Tsufeki\KayoJsonMapper;

use Tsufeki\KayoJsonMapper\Context\ContextFactory;
use Tsufeki\KayoJsonMapper\Dumper\ArrayDumper;
use Tsufeki\KayoJsonMapper\Dumper\DateTimeDumper;
use Tsufeki\KayoJsonMapper\Dumper\DispatchingDumper;
use Tsufeki\KayoJsonMapper\Dumper\Dumper;
use Tsufeki\KayoJsonMapper\Dumper\ObjectDumper;
use Tsufeki\KayoJsonMapper\Dumper\ScalarNullDumper;
use Tsufeki\KayoJsonMapper\Loader\ArgumentLoader;
use Tsufeki\KayoJsonMapper\Loader\ArrayLoader;
use Tsufeki\KayoJsonMapper\Loader\DateTimeLoader;
use Tsufeki\KayoJsonMapper\Loader\DispatchingLoader;
use Tsufeki\KayoJsonMapper\Loader\Instantiator\Instantiator;
use Tsufeki\KayoJsonMapper\Loader\Instantiator\NewInstantiator;
use Tsufeki\KayoJsonMapper\Loader\Instantiator\NoConstructorInstantiator;
use Tsufeki\KayoJsonMapper\Loader\Loader;
use Tsufeki\KayoJsonMapper\Loader\MixedLoader;
use Tsufeki\KayoJsonMapper\Loader\NullLoader;
use Tsufeki\KayoJsonMapper\Loader\ObjectLoader;
use Tsufeki\KayoJsonMapper\Loader\ReplacingLoader;
use Tsufeki\KayoJsonMapper\Loader\ScalarLoader;
use Tsufeki\KayoJsonMapper\Loader\UnionLoader;
use Tsufeki\KayoJsonMapper\MetadataProvider\AccessorStrategy\AccessorStrategy;
use Tsufeki\KayoJsonMapper\MetadataProvider\AccessorStrategy\StandardAccessorStrategy;
use Tsufeki\KayoJsonMapper\MetadataProvider\CachedCallableMetadataProvider;
use Tsufeki\KayoJsonMapper\MetadataProvider\CachedClassMetadataProvider;
use Tsufeki\KayoJsonMapper\MetadataProvider\CallableMetadataProvider;
use Tsufeki\KayoJsonMapper\MetadataProvider\ClassMetadataProvider;
use Tsufeki\KayoJsonMapper\MetadataProvider\Phpdoc\PhpdocTypeExtractor;
use Tsufeki\KayoJsonMapper\MetadataProvider\ReflectionCallableMetadataProvider;
use Tsufeki\KayoJsonMapper\MetadataProvider\ReflectionClassMetadataProvider;
use Tsufeki\KayoJsonMapper\NameMangler\CamelCaseToUnderscoreNameMangler;
use Tsufeki\KayoJsonMapper\NameMangler\NameMangler;
use Tsufeki\KayoJsonMapper\PropertyAccess\PrivatePropertyAccess;
use Tsufeki\KayoJsonMapper\PropertyAccess\PublicPropertyAccess;

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
     * @var bool
     */
    private $privatePropertyAccess = false;

    /**
     * @var bool
     */
    private $guessRequiredProperties = true;

    /**
     * @var bool
     */
    private $useRequiredPhpdocTag = true;

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
    private $throwOnUnknownProperty = false;

    /**
     * @var bool
     */
    private $setToNullOnMissingProperty = false;

    /**
     * @var bool
     */
    private $strictNulls = true;

    /**
     * @var bool
     */
    private $acceptArrayAsObject = false;

    /**
     * @var bool
     */
    private $acceptStdClassAsArray = false;

    /**
     * @var bool
     */
    private $dumpNullProperties = true;

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
    public function setNameMangler(NameMangler $nameMangler): self
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
     * Whether mapper should access private properties through reflection.
     *
     * This also enables constructor-bypassing instantiator.
     *
     * Defaults to false.
     *
     * @param bool $privatePropertyAccess
     *
     * @return $this
     */
    public function setPrivatePropertyAccess(bool $privatePropertyAccess): self
    {
        $this->privatePropertyAccess = $privatePropertyAccess;

        return $this;
    }

    /**
     * Whether to guess if a property is required.
     *
     * Properties with null default value and non-nullable type are deemed
     * required.
     *
     * Only matters during loading and with `throwOnMissingProperty` on.
     *
     * Defaults to true.
     *
     * @param bool $guessRequiredProperties
     *
     * @return $this
     */
    public function setGuessRequiredProperties(bool $guessRequiredProperties): self
    {
        $this->guessRequiredProperties = $guessRequiredProperties;

        return $this;
    }

    /**
     * Whether to respect `@required` and `@optional` tags in phpdoc.
     *
     * Only matters during loading and with `throwOnMissingProperty` on.
     *
     * Defaults to true.
     *
     * @param bool $useRequiredPhpdocTag
     *
     * @return $this
     */
    public function setUseRequiredPhpdocTag(bool $useRequiredPhpdocTag): self
    {
        $this->useRequiredPhpdocTag = $useRequiredPhpdocTag;

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
     * Default false.
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
     * If true, properties missing in JSON are always set to null.
     *
     * Default false.
     *
     * @param bool $setToNullOnMissingProperty
     *
     * @return $this
     */
    public function setToNullOnMissingProperty(bool $setToNullOnMissingProperty): self
    {
        $this->setToNullOnMissingProperty = $setToNullOnMissingProperty;

        return $this;
    }

    /**
     * If false, allow loading null value into any type.
     *
     * Default true.
     *
     * @param bool $strictNulls
     *
     * @return $this
     */
    public function setStrictNulls(bool $strictNulls): self
    {
        $this->strictNulls = $strictNulls;

        return $this;
    }

    /**
     * Accept string-keyed array when loading an object.
     *
     * Useful when using `json_decode()` with true as second parameter.
     *
     * Default false.
     *
     * @param bool $acceptArrayAsObject
     *
     * @return $this
     */
    public function acceptArrayAsObject(bool $acceptArrayAsObject): self
    {
        $this->acceptArrayAsObject = $acceptArrayAsObject;

        return $this;
    }

    /**
     * Accept stdClass when loading an array.
     *
     * Useful when using `json_decode()` with false as second parameter and
     * loading arrays with string keys.
     *
     * Default false.
     *
     * @param bool $acceptStdClassAsArray
     *
     * @return $this
     */
    public function acceptStdClassAsArray(bool $acceptStdClassAsArray): self
    {
        $this->acceptStdClassAsArray = $acceptStdClassAsArray;

        return $this;
    }

    /**
     * If false, optional null properties are not dumped to JSON.
     *
     * Default true.
     *
     * @param bool $dumpNullProperties
     *
     * @return $this
     */
    public function setDumpNullProperties(bool $dumpNullProperties): self
    {
        $this->dumpNullProperties = $dumpNullProperties;

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

        if ($this->privatePropertyAccess) {
            $propertyAccess = new PrivatePropertyAccess();
            $instantiator = $this->instantiator ?? new NoConstructorInstantiator();
        } else {
            $propertyAccess = new PublicPropertyAccess();
            $instantiator = $this->instantiator ?? new NewInstantiator();
        }

        $callableMetadataProvider = $this->callableMetadataProvider ?? new CachedCallableMetadataProvider(
            new ReflectionCallableMetadataProvider($phpdocTypeExtractor)
        );

        $classMetadataProvider = $this->classMetadataProvider ?? new CachedClassMetadataProvider(
            new ReflectionClassMetadataProvider(
                $callableMetadataProvider,
                $accessorStrategy,
                $phpdocTypeExtractor,
                $this->guessRequiredProperties,
                $this->useRequiredPhpdocTag
            )
        );

        $loader = new DispatchingLoader();
        $loader
            ->add(new UnionLoader($loader))
            ->add(new MixedLoader())
            ->add(new ScalarLoader())
            ->add(new ArrayLoader(
                $loader,
                $this->acceptStdClassAsArray
            ))
            ->add(new ObjectLoader(
                $loader,
                $classMetadataProvider,
                $instantiator,
                $nameMangler,
                $propertyAccess,
                $this->throwOnUnknownProperty,
                $this->throwOnMissingProperty,
                $this->setToNullOnMissingProperty,
                $this->acceptArrayAsObject
            ))
            ->add(new DateTimeLoader($this->dateTimeFormat))
            ->add(new NullLoader($this->strictNulls));

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

        $argumentLoader = new ArgumentLoader(
            $loader,
            $callableMetadataProvider,
            $nameMangler
        );

        $dumper = new DispatchingDumper(
            $this->throwOnMaxDepthExceeded,
            $this->throwOnInfiniteRecursion
        );
        $dumper
            ->add(new ScalarNullDumper())
            ->add(new ArrayDumper($dumper))
            ->add(new ObjectDumper(
                $dumper,
                $classMetadataProvider,
                $nameMangler,
                $propertyAccess,
                $this->dumpNullProperties
            ))
            ->add(new DateTimeDumper($this->dateTimeFormat));

        foreach ($this->dumpers as $userDumper) {
            $dumper->add($userDumper);
        }

        $contextFactory = new ContextFactory($this->dumpMaxDepth);

        return new Mapper($loader, $dumper, $argumentLoader, $contextFactory);
    }
}
