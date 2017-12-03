<?php declare(strict_types=1);

namespace Tsufeki\KayoJsonMapper\Loader;

use phpDocumentor\Reflection\Type;
use phpDocumentor\Reflection\Types;
use Tsufeki\KayoJsonMapper\Context;
use Tsufeki\KayoJsonMapper\Exception\TypeMismatchException;
use Tsufeki\KayoJsonMapper\Exception\UnsupportedTypeException;

class UnionLoader implements Loader
{
    /**
     * @var Loader
     */
    private $dispatchingLoader;

    public function __construct(Loader $dispatchingLoader)
    {
        $this->dispatchingLoader = $dispatchingLoader;
    }

    public function load($data, Type $type, Context $context)
    {
        $types = [];
        if ($type instanceof Types\Nullable) {
            $types = [$type->getActualType(), new Types\Null_()];
        } elseif ($type instanceof Types\Compound) {
            $types = $type->getIterator();
        } else {
            throw new UnsupportedTypeException();
        }

        // Pop the context to avoid InfiniteRecursionException as we are dispatching
        // the same object for a second time.
        $context->pop();

        /** @var Type $altType */
        foreach ($types as $altType) {
            try {
                return $this->dispatchingLoader->load($data, $altType, $context);
            } catch (TypeMismatchException $e) {
            } catch (UnsupportedTypeException $e) {
            }
        }

        throw new TypeMismatchException((string)$type, $data);
    }
}
