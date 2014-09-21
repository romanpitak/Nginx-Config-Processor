# Nginx Configuration Processor

## Features

### Pretty Print
```php
Scope::fromFile('m1.conf')->saveToFile('out.conf');
```

### Config Create
```php
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
echo new Comment("This is a simple comment.");
```
output:
```nginx
# This is a simple comment.
```
#### Multi-line comments
```php
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
####

#### Directive with a simple comment
```php
echo Directive::create('deny', 'all')->setCommentText('Directive with a comment');
```
output:
```nginx
deny all; # Directive with a comment
```

#### Directive with a multi-line comment
```php
echo Directive::create('deny', 'all')->setCommentText('Directive
with a multi line comment');
```
output:
```nginx
# Directive
# with a multi line comment
deny all;
```
