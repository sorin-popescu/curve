[![StyleCI](https://styleci.io/repos/123552459/shield?style=flat-square&branch=master)](https://styleci.io/repos/123552459)
[![Travis](https://img.shields.io/travis/sorin-popescu/curve.svg?style=flat-square&label=TravisCI)](https://github.com/sorin-popescu/curve)
[![Code Coverage](https://img.shields.io/codecov/c/github/sorin-popescu/curve.svg?style=flat-square&label=Coverage)](https://codecov.io/gh/sorin-popescu/curve)

# Prepaid Card

## Dependencies

This project has the following system dependencies:

* PHP 7.0
* Redis Server ([Quick Start Guide](https://redis.io/topics/quickstart))

It also relies 3 external PHP libraries

* [predis/predis](https://github.com/nrk/predis)
* [slim/slim](https://github.com/slimphp/Slim)

## Project setup

```
cd curve
make deploy
```

## Running the application

You can find a [Postman collection](Curve.postman_collection.json) that can be used to run the application

## Running tests

```
make test
```

## Running tests individually

### PHP Unit
```
make phpunit
```

### Behat
```
make behat
```

## Code Standards / Quality

PSR-2

### Ensuring code standards and quality

####PHP Code Sniffer

Fixes most issues in the code code for PSR-2 coding standards

```
make phpcs
```

or
```
make fix
```

#### PHP Stan

```
make phpstan
```
#### PHP Parallel Lint

```
make lint
```
#### Test Coverage

```
make coverage
```
