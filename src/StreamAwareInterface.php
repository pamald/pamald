<?php

declare(strict_types = 1);

namespace Pamald\Pamald;

interface StreamAwareInterface
{

    /**
     * @return resource
     */
    public function getStream();

    /**
     * @param resource $stream
     */
    public function setStream($stream): static;
}
