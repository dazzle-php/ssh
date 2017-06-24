<?php

namespace Dazzle\SSH;

interface SSH2AuthInterface
{
    /**
     * Authenticates the given SSH session
     *
     * @param resource $conn
     * @return bool
     */
    function authenticate($conn);
}
