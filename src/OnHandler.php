<?php

declare(strict_types=1);

namespace Vjik\Yii\ValidatorScenarios;

use Yiisoft\Validator\Exception\UnexpectedRuleException;
use Yiisoft\Validator\Result;
use Yiisoft\Validator\RuleHandlerInterface;
use Yiisoft\Validator\ValidationContext;

final class OnHandler implements RuleHandlerInterface
{
    public function validate(mixed $value, object $rule, ValidationContext $context): Result
    {
        if (!$rule instanceof On) {
            throw new UnexpectedRuleException(On::class, $rule);
        }

        if (
            $rule->getScenario() === $context->getParameter(On::SCENARIO_PARAMETER)
            || $rule->getScenario() === null
        ) {
            return $context->validate($value, $rule->getRules());
        }

        return new Result();
    }
}
