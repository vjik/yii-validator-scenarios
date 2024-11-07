<?php

declare(strict_types=1);

namespace Vjik\Yii\ValidatorScenarios\Tests\Support;

use Vjik\Yii\ValidatorScenarios\On;
use Yiisoft\Validator\Rule\Email;
use Yiisoft\Validator\Rule\Length;
use Yiisoft\Validator\Rule\Required;

final class UserDto
{
    public function __construct(
        #[On(
            'register',
            [new Required(), new Length(min: 7, max: 10)],
        )]
        public string $name,
        #[Required]
        #[Email]
        public string $email,
        #[On(
            ['login', 'register'],
            [new Required(), new Length(min: 8)],
        )]
        public string $password,
    ) {}
}
