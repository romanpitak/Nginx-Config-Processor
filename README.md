# Nginx Configuration Processor

[![Latest Stable Version](https://img.shields.io/packagist/v/romanpitak/nginx-config-processor.svg)](https://packagist.org/packages/romanpitak/nginx-config-processor)
[![Total Downloads](https://img.shields.io/packagist/dt/romanpitak/nginx-config-processor.svg)](https://packagist.org/packages/romanpitak/nginx-config-processor)
[![License](https://img.shields.io/packagist/l/romanpitak/nginx-config-processor.svg)](https://packagist.org/packages/romanpitak/nginx-config-processor)
[![Build Status](https://travis-ci.org/romanpitak/Nginx-Config-Processor.svg?branch=master)](https://travis-ci.org/romanpitak/Nginx-Config-Processor)
[![Code Climate](https://codeclimate.com/github/romanpitak/Nginx-Config-Processor/badges/gpa.svg)](https://codeclimate.com/github/romanpitak/Nginx-Config-Processor)
[![Test Coverage](https://codeclimate.com/github/romanpitak/Nginx-Config-Processor/badges/coverage.svg)](https://codeclimate.com/github/romanpitak/Nginx-Config-Processor/coverage)
[![Codacy Badge](https://www.codacy.com/project/badge/bf83cd710c374869a96cd5d5e44e0329)](https://www.codacy.com/public/roman/Nginx-Config-Processor)
[![Codacy Badge](https://api.codacy.com/project/badge/coverage/bf83cd710c374869a96cd5d5e44e0329)](https://www.codacy.com/app/roman/Nginx-Config-Processor)

(c) 2014-2016 [Roman Piták](http://pitak.net) <roman@pitak.net>

PHP Nginx configuration files processor (parser, creator).

## Installation

The best way to install is to use the [Composer](https://getcomposer.org/) dependency manager.

```
php composer.phar require romanpitak/nginx-config-processor
```

## Features

### Pretty Print

```php
<?php Scope::fromFile('m1.conf')->saveToFile('out.conf');
```

### Config Create

```php
<?php
Scope::create()
    ->addDirective(Directive::create('server')
        ->setChildScope(Scope::create()
            ->addDirective(Directive::create('listen', 8080))
            ->addDirective(Directive::create('server_name', 'example.net'))
            ->addDirective(Directive::create('root', 'C:/www/example_net'))
            ->addDirective(Directive::create('location', '^~ /var/', Scope::create()
                    ->addDirective(Directive::create('deny', 'all'))
                )->setCommentText('Deny access for location /var/')
            )
        )
    )
    ->saveToFile('example.net');
```

File _example.net_:

```nginx
server {
    listen 8080;
    server_name example.net;
    root C:/www/example_net;
    location ^~ /var/ { # Deny access for location /var/
        deny all;
    }
}
```

### Comments handling

#### Simple comments

```php
<?php echo new Comment("This is a simple comment.");
```

output:

```nginx
# This is a simple comment.
```

#### Multi-line comments

```php
<?php
echo new Comment("This \nis \r\na multi
line " . PHP_EOL . "comment.");
```

output:

```nginx
# This
# is
# a multi
# line
# comment.
```

#### Directive with a simple comment

```php
<?php echo Directive::create('deny', 'all')->setCommentText('Directive with a comment');
```

output:

```nginx
deny all; # Directive with a comment
```

#### Directive with a multi-line comment

```php
<?php echo Directive::create('deny', 'all')->setCommentText('Directive
with a multi line comment');
```

output:

```nginx
# Directive
# with a multi line comment
deny all;
```
