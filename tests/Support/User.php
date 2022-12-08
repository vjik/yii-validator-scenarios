<?php

declare(strict_types=1);

namespace Vjik\Yii\ValidatorScenarios\Tests\Support;

use Vjik\Yii\ValidatorScenarios\On;
use Yiisoft\Validator\Rule\Email;
use Yiisoft\Validator\Rule\HasLength;
use Yiisoft\Validator\Rule\Required;

final class User
{
    public function __construct(
        #[On(
            'register',
            [
                new Required(),
                new HasLength(min: 7, max: 10),
            ]
        )]
        private string $name,

        #[Required]
        #[Email]
        private string $email,

        #[On(
            ['login', 'register'],
            [
                new Required(),
                new HasLength(min: 8),
            ],
        )]
        private string $password,
    ) {
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public function getEmail(): string
    {
        return $this->email;
    }
}
