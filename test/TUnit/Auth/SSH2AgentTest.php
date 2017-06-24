<?php

namespace Dazzle\SSH\Test\TUnit\Auth;

use Dazzle\SSH\Auth\SSH2Agent;
use Dazzle\SSH\SSH2AuthInterface;
use Dazzle\SSH\Test\TUnit;

class SSH2AgentTest extends TUnit
{
    /**
     *
     */
    public function testConstructor_CreatesProperInstance()
    {
        $user = 'user';
        $auth = new SSH2Agent($user);

        $this->assertInstanceOf(SSH2AuthInterface::class, $auth);
        $this->assertAttributeEquals($user, 'username', $auth);
    }

    /**
     *
     */
    public function testDestructor_DoesNotThrowException()
    {
        $user = 'user';
        $auth = new SSH2Agent($user);
        unset($auth);
    }
}
