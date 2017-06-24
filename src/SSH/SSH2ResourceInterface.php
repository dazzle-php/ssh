<?php

namespace Dazzle\SSH;

use Dazzle\Stream\AsyncStreamInterface;

interface SSH2ResourceInterface extends AsyncStreamInterface
{
    /**
     * @return string
     */
    public function getId();
}
