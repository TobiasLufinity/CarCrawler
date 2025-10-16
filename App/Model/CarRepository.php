<?php

declare(strict_types=1);

namespace App\Model;

use http\Exception\InvalidArgumentException;

class CarRepository {

    public function getCar($id): Car
    {
        $connection = Database::getInstance()->getConnection();
        $statement = $connection->prepare("SELECT * FROM cars WHERE id = ?");
        $statement->bind_param("i", $id);
        $statement->execute();
        $result = $statement->get_result();

        $data = $result->fetch_assoc();
        if (!$data) {
            throw new InvalidArgumentException("Car not found");
        }
        return Car::fromArray($data);
    }

    public function getCarFromUrl(int $urlId): Car
    {
        $connection = Database::getInstance()->getConnection();
        $statement = $connection->prepare("SELECT * FROM cars WHERE url_id = ?");
        $statement->bind_param("i", $urlId);
        $statement->execute();
        $result = $statement->get_result();

        $data = $result->fetch_assoc();
        if (!$data) {
            return new Car();
        }
        return Car::fromArray($data);
    }


    public function save(Car $carObj): Car
    {
        $connection = Database::getInstance()->getConnection();

        $query = $connection->prepare("
            INSERT INTO cars (id, url_id, brand, model, description, registration, price, year, mileage, fuel)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE 
                brand = VALUES(brand),
                model = VALUES(model),
                description = VALUES(description),
                registration = VALUES(registration),
                price = VALUES(price),
                year = VALUES(year),
                mileage = VALUES(mileage),
            fuel = VALUES(fuel)
            ");
        $id = $carObj->getId();
        $urlId = $carObj->getUrlId();
        $brand = $carObj->getBrand();
        $model = $carObj->getModel();
        $description = $carObj->getDescription();
        $reg = $carObj->getRegistration();
        $price = $carObj->getPrice();
        $year = $carObj->getYear();
        $mileage = $carObj->getMileage();
        $fuel = $carObj->getFuel();
        $query->bind_param("iissssdiis",
            $id,
            $urlId,
            $brand,
            $model,
            $description,
            $reg,
            $price,
            $year,
            $mileage,
            $fuel
        );

        $query->execute();
        $query->close();
        return $carObj;
    }

    public function getAllCars(?int $limit = null, ?int $page = null): array
    {
        $connection = Database::getInstance()->getConnection();
        $query = "SELECT SQL_CALC_FOUND_ROWS * FROM cars";
        if ($limit > 0) {
            $query .= " LIMIT {$limit}";
            if ($page !== null) {
                $offset = ($page - 1) * $limit;
                $query .= " OFFSET $offset";
            }
        }
        $statement = $connection->prepare($query);
        $statement->execute();
        $result = $statement->get_result();
        $total = $connection->query("SELECT FOUND_ROWS()")->fetch_row()[0];
        $data = ['total' => $total,'data' => $this->databaseDataToClass($result)];
        if ($limit > 0) {
            $data['page'] = $page ?? 1;
        }
        return $data;
    }

    public function search(string $searchString, ?int $limit = null, ?int $page = null): array
    {
        $connection = Database::getInstance()->getConnection();
        // Simple implementation of search by year
        if (is_numeric($searchString)) {
            $query = "
            SELECT SQL_CALC_FOUND_ROWS *
            FROM cars
            WHERE year = $searchString
            ";
        } else {
            $query = "
            SELECT SQL_CALC_FOUND_ROWS *,
                   MATCH(brand, model, description, registration, fuel)
                   AGAINST (? IN NATURAL LANGUAGE MODE) AS relevance
            FROM cars
            WHERE MATCH(brand, model, description, registration, fuel)
                  AGAINST (? IN NATURAL LANGUAGE MODE)
            ORDER BY relevance DESC
        ";
        }
        if ($limit > 0) {
            $query .= " LIMIT {$limit}";
            if ($page !== null) {
                $offset = ($page - 1) * $limit;
                $query .= " OFFSET $offset";
            }
        }
        $stmt = $connection->prepare($query);
        $stmt->bind_param('ss', $searchString, $searchString);

        $stmt->execute();
        $result = $stmt->get_result();
        $total = $connection->query("SELECT FOUND_ROWS()")->fetch_row()[0];
        $data = ['total' => $total,'data' => $this->databaseDataToClass($result)];
        if ($limit > 0) {
            $data['page'] = $page ?? 1;
        }
        return $data;
    }

    protected function databaseDataToClass($result): array
    {
        $cars = [];
        while ($row = $result->fetch_assoc()) {
            $cars[] = Car::fromArray($row);
        }
        return $cars;
    }
}