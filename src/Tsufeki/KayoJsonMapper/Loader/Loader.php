<?php declare(strict_types=1);

namespace Tsufeki\KayoJsonMapper\Loader;

use phpDocumentor\Reflection\Type;
use Tsufeki\KayoJsonMapper\Context\Context;
use Tsufeki\KayoJsonMapper\Exception\TypeMismatchException;
use Tsufeki\KayoJsonMapper\Exception\UnsupportedTypeException;

interface Loader
{
    /**
     * @param mixed   $data    As returned by `json_decode()` i.e only stdClass,
     *                         arrays and scalars.
     * @param Type    $type    Expected type.
     * @param Context $context
     *
     * @return mixed Unserialized value.
     *
     * @throws UnsupportedTypeException
     * @throws TypeMismatchException
     */
    public function load($data, Type $type, Context $context);

    /**
     * List of supported types.
     *
     * Type object is considered matching if its string representation is the
     * list, or 'any' is in the list. In addition, array, object and compound
     * (i.e. union) types are matching if 'array', 'object' or 'union',
     * respectively, is present.
     *
     * See Type::__toString() in its implementors. Class names should be fully
     * qualified and begin with a backslash '\\'.
     *
     * @return string[]
     */
    public function getSupportedTypes(): array;
}
