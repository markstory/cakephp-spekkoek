# Spekkoek plugin for CakePHP

[![Build Status](https://travis-ci.org/markstory/cakephp-spekkoek.svg?branch=master)](https://travis-ci.org/markstory/cakephp-spekkoek)
[![codecov.io](https://codecov.io/github/markstory/cakephp-spekkoek/coverage.svg?branch=master)](https://codecov.io/github/markstory/cakephp-spekkoek?branch=master)

This plugin is a prototype for adding PSR7 middleware & request/response object
support to CakePHP. It should be considered experimental and pre-alpha.

:warning: This project is far from complete, and experimental at best :warning:

## Concepts

Spekkoek aims to provide PSR7 middleware for a CakePHP 3.x application. It adds
a few new concepts to a CakePHP application that extend and enhance the existing abstractions.

* `Spekkoek\Application` The Application object provides an object oriented
  approach to bootstrapping. This class is used to load bootstrapping and
  application configuration. It also provides hook methods for configuring
  middleware.
* `Spekkoek\MiddlewareStack` A MiddlewareStack provides an interface for
  building and manipulating the stack of middleware. The `Application` is also
  a middleware object that helps encapsulate the existing CakePHP dispatch
  process.
* `Spekkoek\Server` Is the entry point for a request/response. It consumes an
  application, and returns a response. The server's emit() method can be used
  to emit a response to the webserver SAPI.

There are PSR7 middleware versions of all the CakePHP core DispatchFilters.
These are intended to be long term replacements for the CakePHP dispatch
filters.

## Middleware

Middleware is a closure or callable object that accepts a request/response and
returns a response. Each middleware is also provided the next callable in the chain.
This callable should be invoked if/when you want to delegate the response creation to the
next middleware object.

The last middleware object in Spekkoek stack should always be the `Application`.

:warning: Examples needed.

## Installation

Unlike many other plugins, Spekkoek requires a more setup. Because it needs to augment how
bootstrapping, requests and responses are handled you'll need to modify your `webroot/index.php`

:warning: Installation examples are not done.


## Usage

This plugin fundamentally reworks your application's bootstrap process. It
requires replacing your `webroot/index.php` and implementing an `Application` class.

### Build the Application class

:warning: Not done.

### Update webroot/index.php

With your `Application` defined, you will need to update your `webroot/index.php`.
It should look something like the following:

```php
require dirname(__DIR__) . 'vendor/autoload.php';

use Spekkoek\Server;
use App\Application;

// Bind your application to the server.
$server = new Server(new Application());

// Run the request/response through the application
// and emit the response.
$server->emit($server->run());
```
