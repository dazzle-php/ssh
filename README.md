# Dazzle Async SSH

[![Build Status](https://travis-ci.org/dazzle-php/ssh.svg)](https://travis-ci.org/dazzle-php/ssh)
[![Code Coverage](https://scrutinizer-ci.com/g/dazzle-php/ssh/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/dazzle-php/ssh/?branch=master)
[![Code Quality](https://scrutinizer-ci.com/g/dazzle-php/ssh/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/dazzle-php/ssh/?branch=master)
[![Latest Stable Version](https://poser.pugx.org/dazzle-php/ssh/v/stable)](https://packagist.org/packages/dazzle-php/ssh) 
[![Latest Unstable Version](https://poser.pugx.org/dazzle-php/ssh/v/unstable)](https://packagist.org/packages/dazzle-php/ssh) 
[![License](https://poser.pugx.org/dazzle-php/ssh/license)](https://packagist.org/packages/dazzle-php/ssh/license)

<br>
<p align="center">
<img src="https://avatars0.githubusercontent.com/u/29509136?v=3&s=150" />
</p>

## Description

Dazzle SSH is a component that provides consistent interface for PHP SSH2 extension and allows asynchronous writing and reading.

## Feature Highlights

Dazzle SSH features:

* OOP abstraction for PHP SSH2 extension,
* Support for variety of authorization methods,
* Asynchronous SSH2 commands,
* Asynchronous operations on files via SFTP,
* ...and more.

## Examples

This section contains most frequently asked for examples. You can see more in **example directory** or in [official documentation][2].

### Executing commands

```php
$loop   = new Loop(new SelectLoop);
$auth   = new SSH2Password($user, $pass);
$config = new SSH2Config();
$ssh2   = new SSH2($auth, $config, $loop);

$ssh2->on('connect:shell', function(SSH2DriverInterface $shell) use($ssh2, $loop) {
    echo "# CONNECTED SHELL\n";

    $buffer = '';
    $command = $shell->open();
    $command->write('ls -la');
    $command->on('data', function(SSH2ResourceInterface $command, $data) use(&$buffer) {
        $buffer .= $data;
    });
    $command->on('end', function(SSH2ResourceInterface $command) use(&$buffer) {
        echo "# COMMAND RETURNED:\n";
        echo $buffer;
        $command->close();
    });
    $command->on('close', function(SSH2ResourceInterface $command) use($shell) {
        $shell->disconnect();
    });
});

$ssh2->on('disconnect:shell', function(SSH2DriverInterface $shell) use($ssh2) {
    echo "# DISCONNECTED SHELL\n";
    $ssh2->disconnect();
});

$ssh2->on('connect', function(SSH2Interface $ssh2) {
    echo "# CONNECTED\n";
    $ssh2->createDriver(SSH2::DRIVER_SHELL)
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
```

### Writing files

```php
$loop   = new Loop(new SelectLoop);
$auth   = new SSH2Password($user, $pass);
$config = new SSH2Config();
$ssh2   = new SSH2($auth, $config, $loop);

$ssh2->on('connect:sftp', function(SSH2DriverInterface $sftp) use($loop, $ssh2) {
    echo "# CONNECTED SFTP\n";

    $lines = [ "DAZZLE\n", "IS\n", "AWESOME!\n" ];
    $linesPointer = 0;

    $file = $sftp->open(__DIR__ . '/_file_write.txt', 'w+');
    $file->write();
    $file->on('drain', function(SSH2ResourceInterface $file) use(&$lines, &$linesPointer) {
        echo "# PART OF THE DATA HAS BEEN WRITTEN\n";
        if ($linesPointer < count($lines)) {
            $file->write($lines[$linesPointer++]);
        }
    });
    $file->on('finish', function(SSH2ResourceInterface $file) {
        echo "# FINISHED WRITING\n";
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
```

### Reading files

```php
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
```

See more examples in **example directory**.

## Requirements

* PHP-5.6 or PHP-7.0+,
* UNIX or Windows OS,
* PHP SSH2 extension enabled.

## Installation

```
$> composer require dazzle-php/ssh
```

## Tests

```
$> vendor/bin/phpunit -d memory_limit=1024M
```

## Contributing

Thank you for considering contributing to this repository! The contribution guide can be found in the [contribution tips][1].

## License

Dazzle Framework is open-sourced software licensed under the [MIT license][2].

[1]: https://github.com/dazzle-php/ssh/blob/master/CONTRIBUTING.md
[2]: http://opensource.org/licenses/MIT
