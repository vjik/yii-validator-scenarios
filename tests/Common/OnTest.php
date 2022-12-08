<?php

declare(strict_types=1);

namespace Vjik\Yii\ValidatorScenarios\Tests\Common;

use PHPUnit\Framework\TestCase;
use Traversable;
use Vjik\Yii\ValidatorScenarios\On;
use Vjik\Yii\ValidatorScenarios\OnHandler;
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
        $this->assertNull($rule->getScenario());
        $this->assertInstanceOf(Traversable::class, $rules);
        $this->assertSame([], iterator_to_array($rules));
    }

    public function dataOptions(): array
    {
        return [
            [
                [
                    'scenario' => null,
                    'rules' => [],
                    'skipOnEmpty' => false,
                    'skipOnError' => false,
                ],
                new On(),
            ],
            [
                [
                    'scenario' => 'test',
                    'rules' => [
                        [
                            'inRange',
                            'values' => [1, 2],
                            'strict' => false,
                            'not' => false,
                            'message' => [
                                'template' => 'This value is invalid.',
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
                                'template' => 'This value is invalid.',
                                'parameters' => [],
                            ],
                            'skipOnEmpty' => false,
                            'skipOnError' => false,
                        ],
                    ],
                    'skipOnEmpty' => false,
                    'skipOnError' => false,
                ],
                new On(
                    'test',
                    [
                        new In([1, 2]),
                        new In([3, 4]),
                    ],
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

    public function testWithScenario(): void
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
            new ValidationContext([
                On::SCENARIO_PARAMETER => 'test',
            ]),
        );

        $this->assertSame(
            [
                'a' => [
                    'Value must be no greater than 1.',
                    'Value must be no greater than 2.'
                ],
            ],
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
}
