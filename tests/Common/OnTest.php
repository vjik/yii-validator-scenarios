<?php

declare(strict_types=1);

namespace Vjik\Yii\ValidatorScenarios\Tests\Common;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use stdClass;
use Traversable;
use Vjik\Yii\ValidatorScenarios\On;
use Vjik\Yii\ValidatorScenarios\OnHandler;
use Vjik\Yii\ValidatorScenarios\Tests\Support\StringableObject;
use Yiisoft\Validator\Exception\UnexpectedRuleException;
use Yiisoft\Validator\Rule\In;
use Yiisoft\Validator\Rule\Number;
use Yiisoft\Validator\ValidationContext;
use Yiisoft\Validator\Validator;

final class OnTest extends TestCase
{
    public function testDefaults(): void
    {
        $rule = new On();

        $rules = $rule->getRules();

        $this->assertSame('on', $rule->getName());
        $this->assertNull($rule->getScenarios());
        $this->assertInstanceOf(Traversable::class, $rules);
        $this->assertSame([], iterator_to_array($rules));
    }

    public function dataOptions(): array
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
                            'inRange',
                            'values' => [1, 2],
                            'strict' => false,
                            'not' => false,
                            'message' => [
                                'template' => 'This value is not in the list of acceptable values.',
                                'parameters' => [],
                            ],
                            'skipOnEmpty' => false,
                            'skipOnError' => false,
                        ],
                        [
                            'inRange',
                            'values' => [3, 4],
                            'strict' => false,
                            'not' => false,
                            'message' => [
                                'template' => 'This value is not in the list of acceptable values.',
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

    /**
     * @dataProvider dataOptions
     */
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
                    'Value must be no greater than 2.'
                ],
            ],
            $result->getErrorMessagesIndexedByPath(),
        );
    }

    public function dataWithScenario(): array
    {
        return [
            [
                [
                    'a' => [
                        'Value must be no greater than 1.',
                        'Value must be no greater than 2.',
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
                        'Value must be no greater than 1.',
                        'Value must be no greater than 2.',
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
                        'Value must be no greater than 2.',
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
                        'Value must be no greater than 1.',
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
                        'Value must be no greater than 2.',
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
                        'Value must be no greater than 1.',
                        'Value must be no greater than 2.',
                        'Value must be no greater than 3.',
                        'Value must be no greater than 5.',
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

    /**
     * @dataProvider dataWithScenario
     */
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
            'Expected "Vjik\Yii\ValidatorScenarios\On", but "Yiisoft\Validator\Rule\Number" given.'
        );
        $handler->validate(7, $rule, $context);
    }

    public function dataInvalidRuleScenario(): array
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

    /**
     * @dataProvider dataInvalidRuleScenario
     */
    public function testInvalidRuleScenario(string $expectedMessage, mixed $scenario): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage($expectedMessage);
        new On($scenario);
    }
}
