<?php

declare(strict_types=1);

namespace App\Model;

use DateTime;
use Exception;

class Url
{

    public function __construct(
        protected ?int $id = null,
        protected ?string  $url = null,
        protected ?string $crawledAt = null,
        protected ?string $updatedAt = null,
    ) {}

    public static function fromArray(array $data): Url
    {
        return new Url(
            id: $data['id'] ?? null,
            url: $data['url'] ?? null,
            crawledAt: $data['crawled_at'] ?? null,
            updatedAt: $data['updated_at'] ?? null
        );

    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(?int $id): Url
    {
        $this->id = $id;
        return $this;
    }


    public function getUrl(): ?string
    {
        return $this->url;
    }

    public function setUrl(string $url): Url
    {
        $this->url = $url;
        return $this;
    }

    public function getCrawledAt(): ?DateTime
    {
        try {
            return $this->crawledAt ? new DateTime($this->crawledAt) : null;
        } catch (Exception) {
            return null;
        }
    }

    public function setCrawledAt(?DateTime $crawledAt): Url
    {
        $this->crawledAt = $crawledAt?->format('Y-m-d H:i:s');
        return $this;
    }

    public function getUpdatedAt(): ?DateTime
    {
        try {
            return $this->updatedAt ? new DateTime($this->updatedAt) : null;
        } catch (Exception) {
            return null;
        }
    }

    public function setUpdatedAt(?DateTime $updatedAt): Url
    {
        $this->updatedAt = $updatedAt?->format('Y-m-d H:i:s');
        return $this;
    }

}