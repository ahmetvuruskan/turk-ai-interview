<?php

namespace App\Exceptions;

use Exception;
use Symfony\Component\HttpFoundation\Response;

class DomainException extends Exception
{
    protected int $httpStatus = Response::HTTP_UNPROCESSABLE_ENTITY;

    public function __construct(string $message, ?int $code = null)
    {
        parent::__construct(__($message));
        if ($code) {
            $this->httpStatus = $code;
        }
    }

    public function getHttpStatus(): int
    {
        return $this->httpStatus;
    }
}
