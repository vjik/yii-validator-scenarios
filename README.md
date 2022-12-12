<p align="center">
    <a href="https://github.com/yiisoft" target="_blank">
        <img src="https://yiisoft.github.io/docs/images/yii_logo.svg" height="100px">
    </a>
    <h1 align="center">Yii Validator Scenarios</h1>
    <br>
</p>

[![Latest Stable Version](https://poser.pugx.org/vjik/yii-validator-scenarios/v/stable.png)](https://packagist.org/packages/vjik/yii-validator-scenarios)
[![Total Downloads](https://poser.pugx.org/vjik/yii-validator-scenarios/downloads.png)](https://packagist.org/packages/vjik/yii-validator-scenarios)
[![Build status](https://github.com/vjik/yii-validator-scenarios/workflows/build/badge.svg)](https://github.com/vjik/yii-validator-scenarios/actions?query=workflow%3Abuild)
[![Mutation testing badge](https://img.shields.io/endpoint?style=flat&url=https%3A%2F%2Fbadge-api.stryker-mutator.io%2Fgithub.com%2Fvjik%2Fyii-validator-scenarios%2Fmaster)](https://dashboard.stryker-mutator.io/reports/github.com/vjik/yii-validator-scenarios/master)
[![type-coverage](https://shepherd.dev/github/vjik/yii-validator-scenarios/coverage.svg)](https://shepherd.dev/github/vjik/yii-validator-scenarios)
[![static analysis](https://github.com/vjik/yii-validator-scenarios/workflows/static%20analysis/badge.svg)](https://github.com/vjik/yii-validator-scenarios/actions?query=workflow%3A%22static+analysis%22)
[![psalm-level](https://shepherd.dev/github/vjik/yii-validator-scenarios/level.svg)](https://shepherd.dev/github/vjik/yii-validator-scenarios)

The package provides scenarios implementation for [Yii Validator](https://github.com/yiisoft/validator).

## Requirements

- PHP 8.0 or higher.

## Installation

The package could be installed with [composer](https://getcomposer.org/download/):

```shell
composer require vjik/yii-validator-scenarios
```

## General usage

## Testing

### Unit testing

The package is tested with [PHPUnit](https://phpunit.de/). To run tests:

```shell
./vendor/bin/phpunit
```

### Mutation testing

The package tests are checked with [Infection](https://infection.github.io/) mutation framework with
[Infection Static Analysis Plugin](https://github.com/Roave/infection-static-analysis-plugin). To run it:

```shell
./vendor/bin/roave-infection-static-analysis-plugin
```

### Static analysis

The code is statically analyzed with [Psalm](https://psalm.dev/). To run static analysis:

```shell
./vendor/bin/psalm
```

## License

The Yii Validator Scenarios is free software. It is released under the terms of the BSD License.
Please see [`LICENSE`](./LICENSE.md) for more information.
