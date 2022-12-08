<?php

declare(strict_types=1);

namespace Vjik\Yii\ValidatorScenarios\Tests\Support;

use Vjik\Yii\ValidatorScenarios\On;
use Yiisoft\Validator\Result;
use Yiisoft\Validator\Rule\Callback;
use Yiisoft\Validator\Rule\Nested;
use Yiisoft\Validator\Rule\Number;

#[On('test', [
    new Nested([
        'a' => new Number(min: 5),
    ]),
    new Callback(method: 'validate'),
])]
final class ClassAttribute
{
    private int $a = 3;

    private function validate(): Result
    {
        return (new Result())->addError('test error');
    }
}
