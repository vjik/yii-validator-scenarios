<?php

declare(strict_types=1);

namespace Vjik\Yii\ValidatorScenarios;

use InvalidArgumentException;
use Stringable;
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

        /** @var mixed $scenario */
        $scenario = $context->getParameter(On::SCENARIO_PARAMETER);

        try {
            $scenario = $this->prepareScenarioValue($scenario);
        } catch (InvalidArgumentException) {
            return (new Result())
                ->addError(
                    sprintf(
                        'Scenario must be null, a string or "\Stringable" type, "%s" given.',
                        get_debug_type($scenario)
                    )
                );
        }

        return $this->isSatisfied($rule, $scenario)
            ? $context->validate($value, $rule->getRules())
            : new Result();
    }

    /**
     * @throw InvalidArgumentException
     */
    private function prepareScenarioValue(mixed $scenario): ?string
    {
        if ($scenario === null || is_string($scenario)) {
            return $scenario;
        }

        if ($scenario instanceof Stringable) {
            return (string) $scenario;
        }

        throw new InvalidArgumentException();
    }

    private function isSatisfied(On $rule, ?string $scenario): bool
    {
        return $rule->isNot()
            ? $rule->getScenario() !== $scenario
            : ($rule->getScenario() === null || $rule->getScenario() === $scenario);
    }
}
