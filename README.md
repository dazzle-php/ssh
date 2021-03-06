# Dazzle Async SSH

[![Build Status](https://travis-ci.org/dazzle-php/ssh.svg)](https://travis-ci.org/dazzle-php/ssh)
[![Code Coverage](https://scrutinizer-ci.com/g/dazzle-php/ssh/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/dazzle-php/ssh/?branch=master)
[![Code Quality](https://scrutinizer-ci.com/g/dazzle-php/ssh/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/dazzle-php/ssh/?branch=master)
[![Latest Stable Version](https://poser.pugx.org/dazzle-php/ssh/v/stable)](https://packagist.org/packages/dazzle-php/ssh) 
[![Latest Unstable Version](https://poser.pugx.org/dazzle-php/ssh/v/unstable)](https://packagist.org/packages/dazzle-php/ssh) 
[![License](https://poser.pugx.org/dazzle-php/ssh/license)](https://packagist.org/packages/dazzle-php/ssh/license)

> **Note:** This repository is part of [Dazzle Project](https://github.com/dazzle-php/dazzle) - the next-gen library for PHP. The project's purpose is to provide PHP developers with a set of complete tools to build functional async applications. Please, make sure you read the attached README carefully and it is guaranteed you will be surprised how easy to use and powerful it is. In the meantime, you might want to check out the rest of our async libraries in [Dazzle repository](https://github.com/dazzle-php) for the full extent of Dazzle experience.

<br>
<p align="center">
<img src="https://raw.githubusercontent.com/dazzle-php/dazzle/master/media/dazzle-x125.png" />
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

## Provided Example(s)

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

Dazzle SSH requires:

* PHP-5.6 or PHP-7.0+,
* UNIX or Windows OS,
* PHP SSH2 extension enabled.

## Installation

To install this library make sure you have [composer](https://getcomposer.org/) installed, then run following command:

```
$> composer require dazzle-php/ssh
```

## Tests

Tests can be run via:

```
$> vendor/bin/phpunit -d memory_limit=1024M
```

## Versioning

Versioning of Dazzle libraries is being shared between all packages included in [Dazzle Project](https://github.com/dazzle-php/dazzle). That means the releases are being made concurrently for all of them. On one hand this might lead to "empty" releases for some packages at times, but don't worry. In the end it is far much easier for contributors to maintain and -- what's the most important -- much more straight-forward for users to understand the compatibility and inter-operability of the packages.

## Contributing

Thank you for considering contributing to this repository! 

- The contribution guide can be found in the [contribution tips](https://github.com/dazzle-php/ssh/blob/master/CONTRIBUTING.md). 
- Open tickets can be found in [issues section](https://github.com/dazzle-php/ssh/issues). 
- Current contributors are listed in [graphs section](https://github.com/dazzle-php/ssh/graphs/contributors)
- To contact the author(s) see the information attached in [composer.json](https://github.com/dazzle-php/ssh/blob/master/composer.json) file.

## License

Dazzle SSH is open-sourced software licensed under the [MIT license](http://opensource.org/licenses/MIT).

<hr>
<p align="center">
<i>"Everything is possible. The impossible just takes longer."</i> ― Dan Brown
</p>
