<?php

namespace App\Exceptions;

use Exception;
use GraphQL\Error\ClientAware;
use GraphQL\Error\ProvidesExtensions;

final class CustomException extends Exception implements ClientAware, ProvidesExtensions
{
    protected $reason;

    public function __construct(string $message, string $reason = '')
    {
        parent::__construct($message);
        $this->reason = $reason;
    }

    public function isClientSafe(): bool
    {
        return true;
    }

    public function getExtensions(): array
    {
        return [
            'reason' => $this->reason,
        ];
    }
}