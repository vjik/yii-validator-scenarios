<?php

declare(strict_types=1);

namespace Vjik\Yii\ValidatorScenarios\Tests\Php81;

use PHPUnit\Framework\TestCase;
use Vjik\Yii\ValidatorScenarios\On;
use Vjik\Yii\ValidatorScenarios\Tests\Support\ClassAttribute;
use Vjik\Yii\ValidatorScenarios\Tests\Support\User;
use Yiisoft\Validator\ValidationContext;
use Yiisoft\Validator\Validator;

final class OnTest extends TestCase
{
    public function dataBase(): array
    {
        return [
            'without scenario' => [
                [
                    'email' => ['This value is not a valid email address.'],
                ],
                null,
            ],
            'login' => [
                [
                    'email' => ['This value is not a valid email address.'],
                    'password' => [ 'This value must contain at least 8 characters.'],
                ],
                'login',
            ],
            'register' => [
                [
                    'name' => ['This value must contain at least 7 characters.'],
                    'email' => ['This value is not a valid email address.'],
                    'password' => [ 'This value must contain at least 8 characters.'],
                ],
                'register',
            ],
        ];
    }

    /**
     * @dataProvider dataBase
     */
    public function testBase(array $expectedMessage, ?string $scenario): void
    {
        $user = new User(
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
                'a' => ['Value must be no less than 5.'],
                '' => ['test error'],
            ],
            $result->getErrorMessagesIndexedByPath(),
        );
    }
}
