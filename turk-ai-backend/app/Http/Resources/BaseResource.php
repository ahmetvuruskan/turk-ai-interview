<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Collection;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

abstract class BaseResource extends JsonResource
{
    protected int $code = ResponseAlias::HTTP_OK;
    protected ?string $message = null;

    public function withMessage(?string $message): static
    {
        $this->message = __($message);
        return $this;
    }

    public function withCode(int $code): static
    {
        $this->code = $code;
        return $this;
    }

    abstract public function data($request): array;

    public function meta(): array
    {
        return [

        ];
    }

    public function toArray($request): array
    {
        return [
            'code' => $this->code,
            'message' => $this->message,
            'data' => $this->data($request),
            'meta' => $this->meta($request)
        ];
    }
}
