<?php

declare(strict_types=1);

namespace App\Model;

use InvalidArgumentException;
use JsonSerializable;

class Car implements JsonSerializable {

    //TODO: Fetch from file?
    const array XPATH_VARIABLES = [
        'brand' => ["//ul//li[h5[normalize-space(text())='Märke']]/p", "string"],
        'model' => ["//ul//li[h5[normalize-space(text())='Modell']]/p", "string"],
        'description' => ["//p[contains(concat(' ', normalize-space(@class), ' '), ' viewDescription ')]", "string"],
        'registration' => ["//ul//li[h5[normalize-space(text())='Regnummer']]/p", "string"],
        'price' => ["//div[contains(@class, 'Grid-cell') and contains(@class, 'u-textRight')]//span[contains(@class, 'viewPrice') and not(contains(@class, 'viewUnderline'))]", "float"],
        'year' => ["//ul//li[h5[normalize-space(text())='Årsmodell']]/p", "int"],
        'mileage' => ["//ul//li[h5[normalize-space(text())='Mil']]/p", "int"],
        'fuel' => ["//ul//li[h5[normalize-space(text())='Drivmedel']]/p", "string"]
    ];

    public function __construct(
        protected ?int $id = null,
        protected ?int $urlId = null,
        protected ?string $brand = "",
        protected ?string $model = "",
        protected ?string $description = "",
        protected ?string $registration = "",
        protected ?float $price = 0,
        protected ?int $year = 0,
        protected ?int $mileage = 0,
        protected ?string $fuel = ""
    ){}

    public static function fromArray(array $data): Car
    {
        return new Car(
            id: (int)($data['id'] ?? 0),
            urlId: (int)($data['url_id'] ?? 0),
            brand: $data['brand'] ?? '',
            model: $data['model'] ?? '',
            description: $data['description'] ?? '',
            registration: $data['registration'] ?? '',
            price: isset($data['price']) ? (float)$data['price'] : 0.0,
            year: isset($data['year']) ? (int)$data['year'] : 0,
            mileage: isset($data['mileage']) ? (int)$data['mileage'] : 0,
            fuel: $data['fuel'] ?? ''
        );
    }

    public function getId(): ?int
    {
        return $this->id ;
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function getUrlId(): int
    {
        return $this->urlId;
    }

    public function setUrlId(int $urlId): void
    {
        $this->urlId = $urlId;
    }


    public function getBrand(): string
    {
        return $this->brand;
    }

    public function setBrand(string $brand): void
    {
        $this->brand = $brand;
    }

    public function getModel(): string
    {
        return $this->model;
    }

    public function setModel(string $model): void
    {
        $this->model = $model;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): void
    {
        $this->description = $description;
    }



    public function getRegistration(): string
    {
        return $this->registration;
    }

    public function setRegistration(string $registration): void
    {
        $this->registration = $registration;
    }

    public function getPrice(): float
    {
        return $this->price;
    }

    public function setPrice(float $price): void
    {
        $this->price = $price;
    }

    public function getYear(): int
    {
        return $this->year;
    }

    public function setYear(int $year): void
    {
        $this->year = $year;
    }

    public function getMileage(): int
    {
        return $this->mileage;
    }

    public function setMileage(int $mileage): void
    {
        $this->mileage = $mileage;
    }

    public function getFuel(): string
    {
        return $this->fuel;
    }

    public function setFuel(string $fuel): void
    {
        $this->fuel = $fuel;
    }

    public function setData(array|string $key, mixed $value = null): void
    {
        if (is_array($key)) {
            foreach ($key as $k => $v) {
                $this->setData($k, $v);
            }
            return;
        }

        // Normalize key name to match property names
        if (!property_exists($this, $key)) {
            throw new InvalidArgumentException("Unknown property: $key");
        }

        $this->$key = $value;
    }

    public function getData(?string $key = null): mixed
    {
        if ($key === null) {
            return [
                'id'           => $this->id,
                'urlId'        => $this->urlId,
                'brand'        => $this->brand,
                'model'        => $this->model,
                'description'  => $this->description,
                'registration' => $this->registration,
                'price'        => $this->price,
                'year'         => $this->year,
                'mileage'      => $this->mileage,
                'fuel'         => $this->fuel,
            ];
        }

        if (!property_exists($this, $key)) {
            throw new InvalidArgumentException("Unknown property: $key");
        }

        return $this->$key;
    }

    public function jsonSerialize(): mixed
    {
        return $this->getData();
    }
}