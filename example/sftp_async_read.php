<?php

/**
 * ---------------------------------------------------------------------------------------------------------------------
 * DESCRIPTION
 * ---------------------------------------------------------------------------------------------------------------------
 * This file contains the example of reading data from files.
 * ---------------------------------------------------------------------------------------------------------------------
 */

require __DIR__ . '/../vendor/autoload.php';

use Dazzle\Loop\Model\SelectLoop;
use Dazzle\Loop\Loop;
use Dazzle\SSH\Auth\SSH2Password;
use Dazzle\SSH\SSH2;
use Dazzle\SSH\SSH2Config;
use Dazzle\SSH\SSH2DriverInterface;
use Dazzle\SSH\SSH2Interface;
use Dazzle\SSH\SSH2ResourceInterface;

$user = getenv('TEST_USER') ? getenv('TEST_USER') : 'Dazzle';
$pass = getenv('TEST_PASS') ? getenv('TEST_PASS') : 'Dazzle-1234';

$loop   = new Loop(new SelectLoop);
$auth   = new SSH2Password($user, $pass);
$config = new SSH2Config();
$ssh2   = new SSH2($auth, $config, $loop);

$ssh2->on('connect:sftp', function(SSH2DriverInterface $sftp) use($loop, $ssh2) {
    echo "# CONNECTED SFTP\n";

    $buffer = '';
    $file = $sftp->open(__DIR__ . '/_file_read.txt', 'r+');
    $file->read();
    $file->on('data', function(SSH2ResourceInterface $file, $data) use(&$buffer) {
        $buffer .= $data;
    });
    $file->on('end', function(SSH2ResourceInterface $file) use(&$buffer) {
        echo "# FOLLOWING LINES WERE READ FROM FILE:\n";
        echo $buffer;
        $file->close();
    });
    $file->on('close', function(SSH2ResourceInterface $file) use($sftp) {
        echo "# FILE HAS BEEN CLOSED\n";
        $sftp->disconnect();
    });
});

$ssh2->on('disconnect:sftp', function(SSH2DriverInterface $sftp) use($ssh2) {
    echo "# DISCONNECTED SFTP\n";
    $ssh2->disconnect();
});

$ssh2->on('connect', function(SSH2Interface $ssh2) {
    echo "# CONNECTED\n";
    $ssh2->createDriver(SSH2::DRIVER_SFTP)
         ->connect();
});

$ssh2->on('disconnect', function(SSH2Interface $ssh2) use($loop) {
    echo "# DISCONNECTED\n";
    $loop->stop();
});

$loop->onTick(function() use($ssh2) {
    $ssh2->connect();
});

$loop->start();
