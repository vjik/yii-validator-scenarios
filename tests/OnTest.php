<?php

declare(strict_types=1);

namespace Vjik\Yii\ValidatorScenarios\Tests;

use InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use stdClass;
use Traversable;
use Vjik\Yii\ValidatorScenarios\On;
use Vjik\Yii\ValidatorScenarios\OnHandler;
use Vjik\Yii\ValidatorScenarios\Tests\Support\ClassAttribute;
use Vjik\Yii\ValidatorScenarios\Tests\Support\StringableObject;
use Vjik\Yii\ValidatorScenarios\Tests\Support\UserDto;
use Yiisoft\Validator\Exception\UnexpectedRuleException;
use Yiisoft\Validator\Rule\In;
use Yiisoft\Validator\Rule\Number;
use Yiisoft\Validator\ValidationContext;
use Yiisoft\Validator\Validator;

final class OnTest extends TestCase
{
    public static function dataBase(): array
    {
        return [
            'without scenario' => [
                [
                    'email' => ['Email is not a valid email address.'],
                ],
                null,
            ],
            'login' => [
                [
                    'email' => ['Email is not a valid email address.'],
                    'password' => ['Password must contain at least 8 characters.'],
                ],
                'login',
            ],
            'register' => [
                [
                    'name' => ['Name must contain at least 7 characters.'],
                    'email' => ['Email is not a valid email address.'],
                    'password' => ['Password must contain at least 8 characters.'],
                ],
                'register',
            ],
        ];
    }

    #[DataProvider('dataBase')]
    public function testBase(array $expectedMessage, ?string $scenario): void
    {
        $user = new UserDto(
            name: 'bob',
            email: 'hello.ru',
            password: 'qwerty',
        );

        $result = (new Validator())->validate(
            $user,
            context: new ValidationContext([
                On::SCENARIO_PARAMETER => $scenario,
            ]),
        );

        $this->assertSame($expectedMessage, $result->getErrorMessagesIndexedByPath());
    }

    public function testClassAttribute(): void
    {
        $result = (new Validator())->validate(
            new ClassAttribute(),
            context: new ValidationContext([
                On::SCENARIO_PARAMETER => 'test',
            ]),
        );

        $this->assertSame(
            [
                'a' => ['A must be no less than 5.'],
                '' => ['test error'],
            ],
            $result->getErrorMessagesIndexedByPath(),
        );
    }

    public function testDefaults(): void
    {
        $rule = new On();

        $rules = $rule->getRules();

        $this->assertSame(On::class, $rule->getName());
        $this->assertNull($rule->getScenarios());
        $this->assertInstanceOf(Traversable::class, $rules);
        $this->assertSame([], iterator_to_array($rules));
    }

    public static function dataOptions(): array
    {
        return [
            [
                [
                    'scenarios' => null,
                    'rules' => [],
                    'not' => false,
                    'skipOnEmpty' => false,
                    'skipOnError' => false,
                ],
                new On(),
            ],
            [
                [
                    'scenarios' => ['register', 'login'],
                    'rules' => [],
                    'not' => false,
                    'skipOnEmpty' => false,
                    'skipOnError' => false,
                ],
                new On(['register', new StringableObject('login')]),
            ],
            [
                [
                    'scenarios' => ['test'],
                    'rules' => [
                        [
                            In::class,
                            'values' => [1, 2],
                            'strict' => false,
                            'not' => false,
                            'message' => [
                                'template' => '{Property} is not in the list of acceptable values.',
                                'parameters' => [],
                            ],
                            'skipOnEmpty' => false,
                            'skipOnError' => false,
                        ],
                        [
                            In::class,
                            'values' => [3, 4],
                            'strict' => false,
                            'not' => false,
                            'message' => [
                                'template' => '{Property} is not in the list of acceptable values.',
                                'parameters' => [],
                            ],
                            'skipOnEmpty' => false,
                            'skipOnError' => false,
                        ],
                    ],
                    'not' => true,
                    'skipOnEmpty' => false,
                    'skipOnError' => false,
                ],
                new On(
                    'test',
                    [
                        new In([1, 2]),
                        new In([3, 4]),
                    ],
                    true,
                ),
            ],
        ];
    }

    #[DataProvider('dataOptions')]
    public function testOptions(array $expected, On $rule): void
    {
        $this->assertSame($expected, $rule->getOptions());
    }

    public function testWithoutScenario(): void
    {
        $validator = new Validator();

        $result = $validator->validate(
            ['a' => 7],
            [
                'a' => [
                    new On('test', new Number(max: 1)),
                    new On(null, new Number(max: 2)),
                ],
            ],
        );

        $this->assertSame(
            [
                'a' => [
                    'A must be no greater than 2.',
                ],
            ],
            $result->getErrorMessagesIndexedByPath(),
        );
    }

    public static function dataWithScenario(): array
    {
        return [
            [
                [
                    'a' => [
                        'A must be no greater than 1.',
                        'A must be no greater than 2.',
                    ],
                ],
                ['a' => 7],
                [
                    'a' => [
                        new On('test', new Number(max: 1)),
                        new On(null, new Number(max: 2)),
                    ],
                ],
                'test',
            ],
            [
                [
                    'a' => [
                        'A must be no greater than 1.',
                        'A must be no greater than 2.',
                    ],
                ],
                ['a' => 7],
                [
                    'a' => [
                        new On('test', new Number(max: 1)),
                        new On(null, new Number(max: 2)),
                    ],
                ],
                new StringableObject('test'),
            ],
            [
                [
                    'a' => [
                        'A must be no greater than 2.',
                    ],
                ],
                ['a' => 7],
                [
                    'a' => [
                        new On('test', new Number(max: 1)),
                        new On(null, new Number(max: 2)),
                    ],
                ],
                null,
            ],
            [
                [
                    'a' => [
                        'A must be no greater than 1.',
                    ],
                ],
                ['a' => 7],
                [
                    'a' => [
                        new On('test', new Number(max: 1), true),
                        new On(null, new Number(max: 2), true),
                    ],
                ],
                null,
            ],
            [
                [
                    'a' => [
                        'A must be no greater than 2.',
                    ],
                ],
                ['a' => 7],
                [
                    'a' => [
                        new On('test', new Number(max: 1), true),
                        new On(null, new Number(max: 2), true),
                    ],
                ],
                'test',
            ],
            [
                [
                    '' => [
                        'Scenario must be null, a string or "\Stringable" type, "stdClass" given.',
                    ],
                ],
                7,
                [new On()],
                new stdClass(),
            ],
            [
                [
                    'a' => [
                        'A must be no greater than 1.',
                        'A must be no greater than 2.',
                        'A must be no greater than 3.',
                        'A must be no greater than 5.',
                    ],
                ],
                ['a' => 7],
                [
                    'a' => [
                        new On([new StringableObject('x'), 'y'], new Number(max: 1)),
                        new On(null, new Number(max: 2)),
                        new On(['x'], new Number(max: 3)),
                        new On(['y'], new Number(max: 4)),
                        new On([new StringableObject('y'), 'x'], new Number(max: 5)),
                    ],
                ],
                'x',
            ],
        ];
    }

    #[DataProvider('dataWithScenario')]
    public function testWithScenario(array $expectedMessages, mixed $data, array $rules, mixed $scenario): void
    {
        $result = (new Validator())->validate(
            $data,
            $rules,
            new ValidationContext([
                On::SCENARIO_PARAMETER => $scenario,
            ]),
        );

        $this->assertSame(
            $expectedMessages,
            $result->getErrorMessagesIndexedByPath(),
        );
    }

    public function testInvalidRule(): void
    {
        $handler = new OnHandler();
        $rule = new Number();
        $context = new ValidationContext();

        $this->expectException(UnexpectedRuleException::class);
        $this->expectExceptionMessage(
            'Expected "Vjik\Yii\ValidatorScenarios\On", but "Yiisoft\Validator\Rule\Number" given.',
        );
        $handler->validate(7, $rule, $context);
    }

    public static function dataInvalidRuleScenario(): array
    {
        return [
            'null' => [
                'Scenario must be null, a string, or an array of strings or an array of "\Stringable", "null" given.',
                [null],
            ],
            'object' => [
                'Scenario must be null, a string, or an array of strings or an array of "\Stringable", "stdClass" given.',
                [new stdClass()],
            ],
        ];
    }

    #[DataProvider('dataInvalidRuleScenario')]
    public function testInvalidRuleScenario(string $expectedMessage, mixed $scenario): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage($expectedMessage);
        new On($scenario);
    }
}
