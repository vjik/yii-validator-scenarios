<?php

declare(strict_types=1);

namespace Vjik\Yii\ValidatorScenarios\Tests\Php81;

use PHPUnit\Framework\TestCase;
use Vjik\Yii\ValidatorScenarios\On;
use Vjik\Yii\ValidatorScenarios\Tests\Support\ClassAttribute;
use Yiisoft\Validator\ValidationContext;
use Yiisoft\Validator\Validator;

final class OnTest extends TestCase
{
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
