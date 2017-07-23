<?php

namespace Dazzle\SSH;

use Dazzle\Stream\StreamInterface;

interface SSH2ResourceInterface extends StreamInterface
{
    /**
     * @return string
     */
    public function getId();
}
