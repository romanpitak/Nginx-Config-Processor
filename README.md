# Nginx Configuration Processor

## Features

### Pretty Print
```php
Scope::fromFile('m1.conf')->saveToFile('out.conf');
```

### Config Create
```php
$fileScope = new Scope();
$fileScope->addDirective($serverDirective = new Directive('server'));
$serverDirective->setChildScope($serverScope = new Scope());
$serverScope->addDirective(new Directive('listen', 8080));
$serverScope->addDirective(new Directive('server_name', 'example.net'));
$serverScope->addDirective(new Directive('root', 'C:/www/example_net'));
$serverScope->addDirective($locationVarDirective = new Directive('location', '^~ /var/'));
$locationVarDirective
    ->setChildScope(new Scope())
    ->setCommentText('Deny access for location /var/')
    ->getChildScope()
    ->addDirective(new Directive('deny', 'all'));
$fileScope->saveToFile('example.net');
```
File _example.net_:
>server {
>    listen 8080;
>    server_name example.net;
>    root C:/www/example_net;
>    location ^~ /var/ { # Deny access for location /var/
>        deny all;
>    }
>}
>
