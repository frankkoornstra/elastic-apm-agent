# Elastic APM agent for PHP

[![CircleCI](https://img.shields.io/circleci/project/github/frankkoornstra/elastic-apm-agent.svg)](https://circleci.com/gh/frankkoornstra/elastic-apm-agent/tree/master)
[![Scrutinizer](https://img.shields.io/scrutinizer/g/frankkoornstra/elastic-apm-agent.svg)](https://scrutinizer-ci.com/g/frankkoornstra/elastic-apm-agent/?branch=master)
[![Scrutinizer Coverage](https://img.shields.io/scrutinizer/coverage/g/frankkoornstra/elastic-apm-agent.svg)](https://scrutinizer-ci.com/g/frankkoornstra/elastic-apm-agent/?branch=master)
[![Packagist](https://img.shields.io/packagist/v/techdeco/elastic-apm-agent.svg)](https://packagist.org/packages/techdeco/elastic-apm-agent)
![Packagist](https://img.shields.io/packagist/dt/techdeco/elastic-apm-agent.svg)
![PHP from Packagist](https://img.shields.io/packagist/php-v/techdeco/elastic-apm-agent.svg)

Release 6.2 of the Elastic stack saw to the the dawn of [Application Performance Monitoring](https://www.elastic.co/guide/en/apm/server/current/overview.html). APM agents in several languages were released with it but _somehow_ PHP was not amongst them ¯\_(ツ)_/¯ This library adds that APM agent so we can happily ship our application performance measurements to the Elastic APM Server.

**ATTENTION: Alpha**

Currently the library is in the alpha phase, meaning the interface might still change and it is not ready for production use. I'd highly appreciate it if you try out the current version and [report any issues you come across](https://github.com/frankkoornstra/elastic-apm-agent/issues).

## Installation

Add the library to your application with Composer:

```bash
composer require techdeco/elastic-apm-agent
```

This will, however, only work if you have an implementations of the following virtual packages installed (more info on each of them below):
- [php-http/async-client-implementation](https://packagist.org/providers/php-http/async-client-implementation)
- [php-http/message-factory-implementation](https://packagist.org/providers/php-http/message-factory-implementation)
- [psr/log-implementation](https://packagist.org/providers/psr/log-implementation)

This seems like a bit of a hassle for you, maybe even a bit lazy from our side - why not let this package just choose one? - but it provides you with the most flexibility and a minimal chance of conflicts with packages already used in your project.

Let's say you have none of these implementations in your project and you choose respectively Guzzle 6, PHP HTTP's Message and Monolog; you can install this library with:

```bash
composer require techdeco/elastic-apm-agent \
                 php-http/guzzle6-adapter \
                 php-http/message \
                 monolog/monolog
```


### HTTP client dependency

To send data to the APM server, the APM agent in this library needs an HTTP client. To not force you into a choice for a specific HTTP client, the library depends on an implementation of `php-http/async-client-implementation`. Your project needs to provide that implementation, for which you can find possible candidates at [the PHP-HTTP site](http://docs.php-http.org/en/latest/clients.html).

### HTTP message factory dependency

The same goes for not forcing you into a choice for a specific HTTP message factory, which leaves your project to provide an implementation of `php-http/message-factory-implementation`, for which you can find possible candidates at [the PHP-HTTP site](http://docs.php-http.org/en/latest/message/message-factory.html)

### PSR logger dependency

And also for logging, this package needs an implementation so that the client can notify you when things go sideways. You can find all implementation for `psr/log-implemtation` at [Packagist](https://packagist.org/providers/psr/log-implementation). If you don't know which one to pick, [Monolog](https://packagist.org/packages/monolog/monolog) is an excellent one.

## Configuration

To tell the library how to connect to the APM Server, initialize at least an implementation of `LoggerInterface` and a `ClientConfiguration` object and give it to the `HttplugAsyncClient`. The HTTP client and message factory are optional; if they're not injected, the client will [try to discover them](http://docs.php-http.org/en/latest/discovery.html).
```php
$config         = (new ClientConfiguration('http://foo.bar'))->authenticatedByToken('alloy');
$httpClient     = ... # Implementation of php-http/async-client-implementation
$requestFactory = ... # implementation of php-http/message-factory-implementation 
$logger         = ... # implementation of psr/log-implementation
$client         = new HttplugAsyncClient($logger, $config, $httpClient, $requestFactory);
```

## Usage

Elastic did a really great job integrating seamlessly with lots of languages and frameworks. This library hopes to provide that same service to the PHP community. You can either choose to use the building blocks provided by the library in a do-it-yourself solution or use the higher level convenience components, like the middleware.

### DIY

Elastic's APM Server ingests two types of events: transactions and errors. Once you created the client you can send respectively a `TechDeCo\ElasticApmAgent\Request\Transaction` or `TechDeCo\ElasticApmAgent\Request\Error` with that client to the APM server. To create these two types of requests, check out the `\TechDeCo\ElasticApmAgent\Message` namespace for all the needed components.

**Be aware that all request and message objects are immutable** so every call to a method will return a **new instance** with a mutated property. Be sure to work with that new instance instead of the one you performed the call on.

```php
# Bad example
$error = new Error(...);
$error->onSystem(...); // New instance is in the wind
$error->inProcess(...) // New instance is in the wind

# Good example
$error = new Error(...);
$error = $error->onSystem(...)->inProcess(...); // Got it!
```

### Middleware

To measure the response time and report errors (by catching exceptions), you can hook up the [PSR-15](https://www.php-fig.org/psr/psr-15/) compliant middleware to your application. If you want to enrich the transaction that you send to the APM server based on the request or response, implement the `OpenTransactionRequestEnricher` or `OpenTransactionResponseEnricher` and hook up the middleware. 

#### Transaction

The `TransactionMiddleware` will inject an `OpenTransaction` into the forwarded request under the attribute name `apm-transaction` (but you better reference to it using the constant `TransactionMiddleware::TRANSACTION_ATTRIBUTE`). 

You can add `Span`s and mark events to the `OpenTransaction` and when you give a response, the middleware will pick them up and send them along with the transaction to the APM server. This is one of the view objects that _is mutable_ so you don't have to replace the request attribute every time you change something.

#### Error

If you want to catch and report `Throwable`s to APM, also include the `ErrorMiddleware` in your middleware stack. It will catch any `Throwable` and try to make as much sense of it as possible. To give the error even more context, you can on inject `Context` object on instantiation of the middleware. It will use that as the base for building context.

#### Enrichment

Sometimes you want to enrich the transaction with data from the request or response. To make this possible, hook up the `OpenTransactionRequestEnrichmentMiddleware` and `OpenTransactionRequestEnrichmentMiddleware` and inject your implementations of respectively `OpenTransactionRequestEnricher`s and `OpenTransactionResponseEnricher`s to add information to the `OpenTransaction`

There are a few enricher implementations included in this library as well:
- `RequestHeaderBlacklistEnricher`: adds headers to the `Request` in the `Context` except when their name is in a blacklist.
- `ResponseHeaderBlacklistEnricher`: adds headers to the `Response` in the `Context` except when their name is in a blacklist.

#### Combination

Obviously the two middleware can be combined. The recommended way to do this is to hook up `TransactionMiddleware` first, then the `ErrorMiddleware` and finally the `OpenTransactionRequestEnrichmentMiddleware` and `OpenTransactionRequestEnrichmentMiddleware`. That way the transaction will be enriched, any error will correlated to the transaction and the transaction duration will be as realistic as possible.

### Caching

To monitor your caching calls, wrap your caching implementing library in the [PSR-6](https://www.php-fig.org/psr/psr-6/) compliant caching layer. It will create a [span](https://www.elastic.co/guide/en/apm/server/current/spans.html) for each call.

Before calling any methods of the `CacheItemPoolInterface`, make sure you **inject an `OpenTransaction`**, see the Open Transaction section below.

### HTTPlug client

To monitor your calls to external HTTP services, wrap your [HTTPlug Client](http://docs.php-http.org/en/latest/httplug/introduction.html) in the `HttpClientWrapper`. It will create a [span](https://www.elastic.co/guide/en/apm/server/current/spans.html) for each http request that you send.

The wrapper will add the [`X-Correlation-ID`](https://en.wikipedia.org/wiki/List_of_HTTP_header_fields#Common_non-standard_request_fields) header to the request before it forwards it. The header will contain a UUID and it gives you the opportunity to correlate cascading http requests throughout your infrastructure. The middleware in this library will pick up the header and will add the correlation id to the tags of the context of the transaction. If no correlation id can be found in the header, a new one will be created.

Before calling any methods of the `HttpClientInterface`, make sure you **inject an `OpenTransaction`**, see the Open Transaction section below.

### Open transaction

Several of the convenience classes in this library need an `OpenTransaction` before they can function. All these classes will implement the `OpenTransactionEnricher` interface. It was a deliberate choice to use a setter instead of requiring the `OpenTransaction` in the constructor, simply because it does not exist at that time, it needs runtime information to be created.

You can get the `OpenTransaction` either get it from a request attribute if you use the middleware in this library, or you can create your own and convert it later on to a `Transaction` that you can send to the APM server via the `Client`.
