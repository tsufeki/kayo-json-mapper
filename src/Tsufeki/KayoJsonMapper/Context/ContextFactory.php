<?php declare(strict_types=1);

namespace Tsufeki\KayoJsonMapper\Context;

class ContextFactory
{
    /**
     * @var int|float
     */
    private $dumpMaxDepth;

    /**
     * @param int|float $dumpMaxDepth
     */
    public function __construct($dumpMaxDepth = INF)
    {
        $this->dumpMaxDepth = $dumpMaxDepth;
    }

    public function createDumpContext(): Context
    {
        return new Context($this->dumpMaxDepth);
    }

    public function createLoadContext(): Context
    {
        return new Context();
    }
}
