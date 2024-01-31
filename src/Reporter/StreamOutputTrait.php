<?php

declare(strict_types = 1);

namespace Pamald\Pamald\Reporter;

trait StreamOutputTrait
{

    /**
     * @var resource
     */
    protected $stream = \STDOUT;

    /**
     * @return resource
     */
    public function getStream()
    {
        return $this->stream;
    }

    /**
     * @param resource $stream
     */
    public function setStream($stream): static
    {
        $this->stream = $stream;

        return $this;
    }
}
