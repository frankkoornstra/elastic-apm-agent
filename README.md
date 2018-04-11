# Elastic APM agent for PHP

Release 6.2 of the Elastic stack saw to the the dawn of [Application Performance Management](https://www.elastic.co/guide/en/apm/server/current/overview.html). APM agents in several languages were released with it but _somehow_ PHP was not amongst them ¯\_(ツ)_/¯ This library adds that APM agent so we can happily ship our application performance measurements to the Elastic APM Server.

**ATTENTION: Work in progress**

This readme functions both as documentation and as a means to communicate the goals for this library. Since the library is still very much **alpha, so work in progress** not all goals have been met. In the list below you can find the progress:

- [x] Setup package
- [x] Setup tooling around tests an code quality
- [x] Setup acceptance tests
- [x] Create messages and requests
- [x] Create interface for synchronous and asynchronous clients
- [x] Implement asynchronous client
- [x] Create PSR-15 middleware
- [ ] Create a PSR-6 cache wrapper
- [ ] Create a PSR-18 HTTP client wrapper
- [ ] Create a PSR-18 HTTP request factory wrapper
- [ ] Create a PHP-HTTP plugin to [automatically wrap HTTP clients](http://docs.php-http.org/en/latest/components/client-common.html)

## Installation

Add the library to your application with Composer:

```bash
composer require techdeco/elastic-apm-agent
```

### HTTP client dependency

To send data to the APM server, the APM agent in this library needs an HTTP client. To not force you into a choice for a specific HTTP client, the library depends on an implementation of `php-http/async-client-implementation`. Your project needs to provide that implementation, for which you can find possible candidates at [the PHP-HTTP site](http://docs.php-http.org/en/latest/clients.html).

### HTTP message factory dependency

The same goes for not forcing you into a choice for a specific HTTP message factory, which leaves your project to provide an implementation of `php-http/message-factory-implementation`, for which you can find possible candidates at [the PHP-HTTP site](http://docs.php-http.org/en/latest/message/message-factory.html)

## Configuration

To tell the library how to connect to the APM Server, initialize a `ClientConfiguration` object and give it to the `HttplugAsyncClient`
```php
$config         = (new ClientConfiguration('http://foo.bar'))->authenticatedByToken('alloy');
$httpClient     = ... # Implementation of php-http/async-client-implementation
$requestFactory = ... # implementation of php-http/message-factory-implementation 
$client         = new HttplugAsyncClient($config, $httpClient, $requestFactory);
```

## Usage

Elastic did a really great job integrating seamlessly with lots of languages and frameworks. This library hopes to provide that same service to the PHP community. You can either choose to use the building blocks provided by the library in a do-it-yourself solution or use the higher level components, like the middleware.

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

To measure the response time and report errors (by catching exceptions), you can hook up the [PSR-15](https://www.php-fig.org/psr/psr-15/) compliant middleware to your application.

#### Transaction

The `TransactionMiddleware` will inject an `OpenTransaction` into the forwarded request under the attribute name `apm-transaction` (but you better reference to it using the constant `TransactionMiddleware::TRANSACTION_ATTRIBUTE`). 

You can add `Span`s and mark events to the `OpenTransaction` and when you give a response, the middleware will pick them up and send them along with the transaction to the APM server. This is one of the view objects that _is mutable_ so you don't have to replace the request attribute every time you change something.

#### Error

If you want to catch and report `Throwable`s to APM, also include the `ErrorMiddleware` in your middleware stack. It will catch any `Throwable` and try to make as much sense of it as possible. To give the error even more context, you can on inject `Context` object on instantiation of the middleware. It will use that as the base for building context.

#### Combination

Obviously the two middleware can be combined. The recommended way to do this is to wrap the `ErrorMiddlware` _inside_ the `TransactionMiddleware`. That way the error will correlated to the transaction and the transaction duration will be as realistic as possible, including the time it takes to report the error to the APM server for example.

### Caching

**DRAFT, IN PROGRESS**

To monitor your caching calls, wrap your caching implementing library in the [PSR-6](https://www.php-fig.org/psr/psr-6/) compliant caching layer. It will create a [span](https://www.elastic.co/guide/en/apm/server/current/spans.html) for each call.

### HTTP client

**DRAFT, IN PROGRESS**

To monitor your calls to external HTTP services, wrap your HTTP library in the [PSR-18](https://github.com/php-fig/fig-standards/tree/master/proposed/http-client/) (although the PSR is still in DRAFT) compliant library. It will create a [span](https://www.elastic.co/guide/en/apm/server/current/spans.html) for each call that you make.

If you also want to able to track the incoming http request over several services, make sure you create the request through the [PSR-19](https://github.com/php-fig/fig-standards/tree/master/proposed/http-factory/) (also still in DRAFT) request factory that can wrap your own implementation. The library will add the necessary headers that the called http services can pick up to correlate the request.

If you use the PHP-HTTP [Discovery](http://docs.php-http.org/en/latest/discovery.html) functionality to locate your HTTP client and request factory before injection into your own components, the library will automatically wrap them for your convenience.
