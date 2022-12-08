<?php

declare(strict_types=1);

namespace Vjik\Yii\ValidatorScenarios;

use Attribute;
use Closure;
use InvalidArgumentException;
use Stringable;
use Yiisoft\Validator\AfterInitAttributeEventInterface;
use Yiisoft\Validator\Helper\RulesNormalizer;
use Yiisoft\Validator\Rule\Trait\SkipOnEmptyTrait;
use Yiisoft\Validator\Rule\Trait\SkipOnErrorTrait;
use Yiisoft\Validator\Rule\Trait\WhenTrait;
use Yiisoft\Validator\RuleInterface;
use Yiisoft\Validator\RulesDumper;
use Yiisoft\Validator\RuleWithOptionsInterface;
use Yiisoft\Validator\SkipOnEmptyInterface;
use Yiisoft\Validator\SkipOnErrorInterface;
use Yiisoft\Validator\WhenInterface;

/**
 * @psalm-import-type WhenType from WhenInterface
 */
#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_PROPERTY | Attribute::IS_REPEATABLE)]
final class On implements
    RuleWithOptionsInterface,
    SkipOnErrorInterface,
    WhenInterface,
    SkipOnEmptyInterface,
    AfterInitAttributeEventInterface
{
    use SkipOnEmptyTrait;
    use SkipOnErrorTrait;
    use WhenTrait;

    public const SCENARIO_PARAMETER = 'scenario';

    /**
     * @var string[]|null
     */
    private ?array $scenarios;

    /**
     * @var iterable<int, RuleInterface>
     */
    private iterable $rules;

    private ?RulesDumper $rulesDumper = null;

    /**
     * @throws InvalidArgumentException
     */
    public function __construct(
        /**
         * @var string|Stringable|string[]|Stringable[]|null The scenario(s) that rules are in. Null if rules used
         * always.
         */
        string|Stringable|array|null $scenario = null,
        /**
         * @param iterable<callable|RuleInterface>|callable|RuleInterface Rules to apply.
         */
        callable|iterable|object $rules = [],
        private bool $not = false,
        /**
         * @var bool|callable|null
         */
        private $skipOnEmpty = null,
        private bool $skipOnError = false,
        /**
         * @var WhenType
         */
        private Closure|null $when = null,
    ) {
        $this->setScenarios($scenario);
        $this->rules = RulesNormalizer::normalizeList($rules);
    }

    public function getName(): string
    {
        return 'on';
    }

    public function getHandlerClassName(): string
    {
        return OnHandler::class;
    }

    public function getScenarios(): ?array
    {
        return $this->scenarios;
    }

    /**
     * @return iterable<int, RuleInterface>
     */
    public function getRules(): iterable
    {
        return $this->rules;
    }

    public function isNot(): bool
    {
        return $this->not;
    }

    public function getOptions(): array
    {
        return [
            'scenarios' => $this->scenarios,
            'rules' => $this->getRulesDumper()->asArray($this->rules),
            'not' => $this->not,
            'skipOnEmpty' => $this->getSkipOnEmptyOption(),
            'skipOnError' => $this->skipOnError,
        ];
    }

    public function afterInitAttribute(object $object): void
    {
        foreach ($this->rules as $rule) {
            if ($rule instanceof AfterInitAttributeEventInterface) {
                $rule->afterInitAttribute($object);
            }
        }
    }

    private function getRulesDumper(): RulesDumper
    {
        if ($this->rulesDumper === null) {
            $this->rulesDumper = new RulesDumper();
        }

        return $this->rulesDumper;
    }

    /**
     * @throws InvalidArgumentException
     */
    private function setScenarios(mixed $sourceScenario): void
    {
        if ($sourceScenario === null) {
            $this->scenarios = null;
            return;
        }

        $this->scenarios = array_map(
            static function (mixed $scenario): string {
                if (
                    is_string($scenario)
                    || $scenario instanceof Stringable
                ) {
                    return (string) $scenario;
                }

                throw new InvalidArgumentException(
                    sprintf(
                        'Scenario must be null, a string, or an array of strings or an array of "\Stringable", "%s" given.',
                        get_debug_type($scenario)
                    ),
                );
            },
            is_array($sourceScenario) ? $sourceScenario : [$sourceScenario]
        );
    }
}

