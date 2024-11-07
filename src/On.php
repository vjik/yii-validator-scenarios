<?php

declare(strict_types=1);

namespace Vjik\Yii\ValidatorScenarios;

use Attribute;
use Closure;
use InvalidArgumentException;
use Stringable;
use Yiisoft\Validator\AfterInitAttributeEventInterface;
use Yiisoft\Validator\Helper\RulesDumper;
use Yiisoft\Validator\Helper\RulesNormalizer;
use Yiisoft\Validator\Rule\Trait\SkipOnEmptyTrait;
use Yiisoft\Validator\Rule\Trait\SkipOnErrorTrait;
use Yiisoft\Validator\Rule\Trait\WhenTrait;
use Yiisoft\Validator\RuleInterface;
use Yiisoft\Validator\RuleWithOptionsInterface;
use Yiisoft\Validator\SkipOnEmptyInterface;
use Yiisoft\Validator\SkipOnErrorInterface;
use Yiisoft\Validator\WhenInterface;

/**
 * The rule implement the scenario feature.
 *
 * Example:
 *
 * ```php
 * final class UserDto
 * {
 *   public function __construct(
 *     #[On(
 *       'register',
 *       [new Required(), new Length(min: 7, max: 10)]
 *     )]
 *     public string $name,
 *
 *     #[Required]
 *     #[Email]
 *     public string $email,
 *
 *     #[On(
 *       ['login', 'register'],
 *       [new Required(), new Length(min: 8)],
 *     )]
 *     public string $password,
 *     ) {
 *     }
 * }
 * ```
 *
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

    /**
     * @var bool|callable|null
     */
    private $skipOnEmpty;

    private ?RulesDumper $rulesDumper = null;

    /**
     * @param string|Stringable|string[]|Stringable[]|null $scenario The scenario(s) that rules are in. `null` if rules
     * used always.
     * @param iterable<callable|RuleInterface>|callable|RuleInterface $rules Rules that will be applied according
     * to `$scenario`.
     * @param bool $not Whether the scenario check should be inverted. When this parameter is set `true`, the validator
     * checks whether the current scenario is among `$scenario` and if NOT, `$rules` will be applied.
     * @param bool|callable|null $skipOnEmpty Whether skip `$rules` on empty value or not, and which value consider as
     * empty. More details in {@see SkipOnEmptyInterface}.
     * @param bool $skipOnError A boolean value where `true` means to skip `$rules` when the previous one errored
     * and `false` - do not skip.
     * @param Closure|null $when The closure that allow to apply `$rules` under certain conditions only. More details
     * in {@see SkipOnErrorInterface}.
     *
     * @psalm-param WhenType $when
     *
     * @throws InvalidArgumentException
     */
    public function __construct(
        string|Stringable|array|null $scenario = null,
        callable|iterable|RuleInterface $rules = [],
        private bool $not = false,
        bool|callable|null $skipOnEmpty = null,
        private bool $skipOnError = false,
        private Closure|null $when = null,
    ) {
        $this->setScenarios($scenario);
        $this->rules = RulesNormalizer::normalizeList($rules);
        $this->skipOnEmpty = $skipOnEmpty;
    }

    public function getName(): string
    {
        return 'on';
    }

    public function getHandler(): string
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
                        get_debug_type($scenario),
                    ),
                );
            },
            is_array($sourceScenario) ? $sourceScenario : [$sourceScenario],
        );
    }
}
