<?php declare(strict_types=1);

namespace Tsufeki\KayoJsonMapper\MetadataProvider\Phpdoc;

use phpDocumentor\Reflection\DocBlock\Tag;
use phpDocumentor\Reflection\DocBlock\Tags\Param;
use phpDocumentor\Reflection\DocBlock\Tags\Return_;
use phpDocumentor\Reflection\DocBlock\Tags\Throws;
use phpDocumentor\Reflection\DocBlock\Tags\Var_;
use phpDocumentor\Reflection\DocBlockFactory;
use phpDocumentor\Reflection\Fqsen;
use phpDocumentor\Reflection\Type;
use phpDocumentor\Reflection\Types\ContextFactory;
use phpDocumentor\Reflection\Types\Object_;
use phpDocumentor\Reflection\Types\Parent_;
use phpDocumentor\Reflection\Types\Self_;
use phpDocumentor\Reflection\Types\Static_;
use phpDocumentor\Reflection\Types\This;

class PhpdocTypeExtractor
{
    /**
     * @var DocBlockFactory
     */
    private $docBlockFactory;

    public function __construct()
    {
        $this->docBlockFactory = DocBlockFactory::createInstance();
    }

    /**
     * @param mixed  $reflection
     * @param string $tagName
     *
     * @return Type|null
     */
    public function getPhpdocType($reflection, string $tagName)
    {
        /** @var Return_[]|Throws[] $tags */
        $tags = $this->getTags($reflection, $tagName);
        $type = $tags ? array_pop($tags)->getType() : null;

        return $this->resolve($reflection, $type);
    }

    /**
     * @param mixed  $reflection
     * @param string $tagName
     *
     * @return Type[]
     */
    public function getPhpdocTypesByVar($reflection, string $tagName): array
    {
        $types = [];

        /** @var Param|Var_ $tag */
        foreach ($this->getTags($reflection, $tagName) as $tag) {
            $type = $this->resolve($reflection, $tag->getType());
            if ($type !== null) {
                $types[$tag->getVariableName() ?? ''] = $type;
            }
        }

        return $types;
    }

    /**
     * @param mixed  $reflection
     * @param string $tagName
     *
     * @return Tag[]
     */
    private function getTags($reflection, string $tagName): array
    {
        if (!$reflection->getDocComment() || !trim($reflection->getDocComment())) {
            return [];
        }

        $context = (new ContextFactory())->createFromReflector($reflection);
        $docBlock = $this->docBlockFactory->create($reflection, $context);

        return $docBlock->getTagsByName($tagName);
    }

    /**
     * @param mixed     $reflection
     * @param Type|null $type
     *
     * @return Type|null
     */
    private function resolve($reflection, Type $type = null)
    {
        if ($type === null) {
            return null;
        }

        if (
            method_exists($reflection, 'getDeclaringClass') && (
                $type instanceof This ||
                $type instanceof Self_ ||
                $type instanceof Static_ ||
                $type instanceof Parent_
            )
        ) {
            $class = $reflection->getDeclaringClass()->getName();

            if ($type instanceof Parent_) {
                $class = get_parent_class($class) ?: $class;
            }

            return new Object_(new Fqsen('\\' . $class));
        }

        return $type;
    }
}
