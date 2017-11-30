<?php declare(strict_types=1);

namespace Tsufeki\KayoJsonMapper;

use Tsufeki\KayoJsonMapper\MetadataProvider\AccessorStrategy;
use Tsufeki\KayoJsonMapper\MetadataProvider\CachedClassMetadataProvider;
use Tsufeki\KayoJsonMapper\MetadataProvider\PhpdocTypeExtractor;
use Tsufeki\KayoJsonMapper\MetadataProvider\ReflectionCallableMetadataProvider;
use Tsufeki\KayoJsonMapper\MetadataProvider\ReflectionClassMetadataProvider;
use Tsufeki\KayoJsonMapper\MetadataProvider\StandardAccessorStrategy;

class MapperBuilder
{
    /**
     * @var AccessorStrategy
     */
    private $accessorStrategy;

    /**
     * @var CallableMetadataProvider
     */
    private $callableMetadataProvider;

    /**
     * @var ClassMetadataProvider
     */
    private $classMetadataProvider;

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

        $loader = new Loader\DispatchingLoader();
        $loader
            ->add(new Loader\UnionLoader($loader))
            ->add(new Loader\MixedLoader())
            ->add(new Loader\ScalarLoader())
            ->add(new Loader\ArrayLoader($loader))
            ->add(new Loader\ObjectLoader($loader, $classMetadataProvider))
            ->add(new Loader\DateTimeLoader($this->dateTimeFormat));

        foreach ($this->loaders as $userLoader) {
            $loader->add($userLoader);
        }

        $dumper = new Dumper\DispatchingDumper();
        $dumper
            ->add(new Dumper\ScalarDumper())
            ->add(new Dumper\ArrayDumper($dumper))
            ->add(new Dumper\ObjectDumper($dumper, $classMetadataProvider))
            ->add(new Dumper\DateTimeDumper($this->dateTimeFormat));

        foreach ($this->dumpers as $userDumper) {
            $dumper->add($userDumper);
        }

        return new Mapper($loader, $dumper, $callableMetadataProvider);
    }
}
