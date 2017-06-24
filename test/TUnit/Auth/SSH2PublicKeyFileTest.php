<?php

namespace Dazzle\SSH\Test\TUnit\Auth;

use Dazzle\SSH\Auth\SSH2PublicKeyFile;
use Dazzle\SSH\SSH2AuthInterface;
use Dazzle\SSH\Test\TUnit;

class SSH2PublicKeyFileTest extends TUnit
{
    /**
     *
     */
    public function testConstructor_CreatesProperInstance()
    {
        $user = 'user';
        $publicKey = 'path/public.key';
        $privateKey = 'path/private.key';
        $passPhrase = 'passPhrase';

        $auth = new SSH2PublicKeyFile($user, $publicKey, $privateKey, $passPhrase);

        $this->assertInstanceOf(SSH2AuthInterface::class, $auth);
        $this->assertAttributeEquals($user, 'username', $auth);
        $this->assertAttributeEquals($publicKey, 'publicKeyFile', $auth);
        $this->assertAttributeEquals($privateKey, 'privateKeyFile', $auth);
        $this->assertAttributeEquals($passPhrase, 'passPhrase', $auth);
    }

    /**
     *
     */
    public function testDestructor_DoesNotThrowException()
    {
        $user = 'user';
        $publicKey = 'path/public.key';
        $privateKey = 'path/private.key';
        $passPhrase = 'passPhrase';

        $auth = new SSH2PublicKeyFile($user, $publicKey, $privateKey, $passPhrase);
        unset($auth);
    }
}
 