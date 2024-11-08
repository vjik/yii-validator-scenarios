<p align="center">
    <img src="https://yiisoft.github.io/docs/images/yii_logo.svg" height="100px">
    <h1 align="center">Yii Validator Scenarios</h1>
    <br>
</p>

[![Latest Stable Version](https://poser.pugx.org/vjik/yii-validator-scenarios/v)](https://packagist.org/packages/vjik/yii-validator-scenarios)
[![Total Downloads](https://poser.pugx.org/vjik/yii-validator-scenarios/downloads)](https://packagist.org/packages/vjik/yii-validator-scenarios)
[![Build status](https://github.com/vjik/yii-validator-scenarios/actions/workflows/build.yml/badge.svg)](https://github.com/vjik/yii-validator-scenarios/actions/workflows/build.yml)
[![Coverage Status](https://coveralls.io/repos/github/vjik/yii-validator-scenarios/badge.svg)](https://coveralls.io/github/vjik/yii-validator-scenarios)
[![Mutation testing badge](https://img.shields.io/endpoint?style=flat&url=https%3A%2F%2Fbadge-api.stryker-mutator.io%2Fgithub.com%2Fvjik%2Fyii-validator-scenarios%2Fmaster)](https://dashboard.stryker-mutator.io/reports/github.com/vjik/yii-validator-scenarios/master)
[![type-coverage](https://shepherd.dev/github/vjik/yii-validator-scenarios/coverage.svg)](https://shepherd.dev/github/vjik/yii-validator-scenarios)
[![static analysis](https://github.com/vjik/yii-validator-scenarios/workflows/static%20analysis/badge.svg)](https://github.com/vjik/yii-validator-scenarios/actions?query=workflow%3A%22static+analysis%22)
[![psalm-level](https://shepherd.dev/github/vjik/yii-validator-scenarios/level.svg)](https://shepherd.dev/github/vjik/yii-validator-scenarios)

The package provides validator rule `On` that implement the scenario feature 
for [Yii Validator](https://github.com/yiisoft/validator).

## Requirements

- PHP 8.1 or higher.

## Installation

The package could be installed with [composer](https://getcomposer.org/download/):

```shell
composer require vjik/yii-validator-scenarios
```

## General usage

The scenario feature implement via the rule `On` and a validation context parameter. 

Configure rules:

```php
use Vjik\Yii\ValidatorScenarios\On;
use Yiisoft\Validator\Rule\Email;
use Yiisoft\Validator\Rule\Length;
use Yiisoft\Validator\Rule\Required;

final class UserDto
{
    public function __construct(
        #[On(
            'register',
            [new Required(), new Length(min: 7, max: 10)]
        )]
        public string $name,

        #[Required]
        #[Email]
        public string $email,

        #[On(
            ['login', 'register'],
            [new Required(), new Length(min: 8)],
        )]
        public string $password,
    ) {
    }
}
```

Or same without attributes:

```php
use Vjik\Yii\ValidatorScenarios\On;
use Yiisoft\Validator\Rule\Email;
use Yiisoft\Validator\Rule\Length;
use Yiisoft\Validator\Rule\Required;
use Yiisoft\Validator\RulesProviderInterface;

final class UserDto implements RulesProviderInterface
{
    public function __construct(
        public string $name,
        public string $email,
        public string $password,
    ) {
    }

    public function getRules(): iterable
    {
        return [
            'name' => new On(
                'register',
                [new Required(), new Length(min: 7, max: 10)],
            ),
            'email' => [new Required(), new Email()],
            'password' => new On(
                ['login', 'register'],
                [new Required(), new Length(min: 8)],
            ),
        ];
    }
}
```

Pass the scenario to the validator through the context:

```php
use Yiisoft\Validator\ValidationContext;
use Yiisoft\Validator\Validator;

$result = (new Validator())->validate(
    $userDto, 
    context: new ValidationContext([
        On::SCENARIO_PARAMETER => $scenario,
    ]),
);
```

Rules that will be applied according to scenarios:

**register**

| Attrubute  | Rules                |
|------------|----------------------|
| `name`     | `Required`, `Length` |
| `email`    | `Required`, `Email`  |
| `password` | `Required`, `Length` |

**login**

| Attrubute  | Rules                |
|------------|----------------------|
| `name`     | —                    |
| `email`    | `Required`, `Email`  |
| `password` | `Required`, `Length` |

**Without scenario**

| Attrubute  | Rules               |
|------------|---------------------|
| `name`     | —                   |
| `email`    | `Required`, `Email` |
| `password` | —                   |

## `On` rule parameters

**$scenario**

The scenario(s) that `$rules` are in. `null` if rules used always. Defaults to `null`.

**$rules**

Rules that will be applied according to `$scenario`. Defaults to empty array.

**$not**

Whether the scenario check should be inverted. When this parameter is set `true`, the validator checks whether
the current scenario is among `$scenario` and if NOT, `$rules` will be applied. Defaults to `false`.

**$skipOnEmpty**

Whether skip `$rules` on empty value or not, and which value consider as empty. Defaults to `null`.

**$skipOnError**

A boolean value where `true` means to skip `$rules` when the previous one errored and `false` — do not skip.
Defaults to `false`.

**$when**

The closure that allow to apply `$rules` under certain conditions only. Defaults to `null`.

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
