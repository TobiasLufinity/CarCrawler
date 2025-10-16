<?php

declare(strict_types=1);

namespace App\Model;

use InvalidArgumentException;
use mysqli;

class CarRepository {

    protected mysqli $connection;

    public function __construct()
    {
        $this->connection = Database::getInstance()->getConnection();
    }

    public function getCar($id): Car
    {
        $statement = $this->connection->prepare("
            SELECT id, url_id, brand, model, description, registration, price, year, mileage, fuel, created_at
            FROM cars WHERE id = ?");
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
        $statement = $this->connection->prepare("
            SELECT id, url_id, brand, model, description, registration, price, year, mileage, fuel, created_at
            FROM cars WHERE url_id = ?");
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
        $query = $this->connection->prepare("
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
        $params = [
            $carObj->getId(),
            $carObj->getUrlId(),
            $carObj->getBrand(),
            $carObj->getModel(),
            $carObj->getDescription(),
            $carObj->getRegistration(),
            $carObj->getPrice(),
            $carObj->getYear(),
            $carObj->getMileage(),
            $carObj->getFuel()
        ];
        $query->bind_param("iissssdiis",...$params);

        $query->execute();
        $query->close();
        return $carObj;
    }

    public function getAllCars(?int $limit = null, ?int $page = null): array
    {
        $query = "SELECT SQL_CALC_FOUND_ROWS * FROM cars";
        if ($limit > 0) {
            $query .= " LIMIT $limit";
            if ($page !== null) {
                $offset = ($page - 1) * $limit;
                $query .= " OFFSET $offset";
            }
        }
        $statement = $this->connection->prepare($query);
        $statement->execute();

        return $this->getDataFromStatement($statement, $limit, $page);
    }

    public function search(string $searchString, ?int $limit = null, ?int $page = null): array
    {
        $connection = Database::getInstance()->getConnection();
        // Simple implementation of search by year
        if (is_numeric($searchString)) {
            $query = "
            SELECT SQL_CALC_FOUND_ROWS 
            id, url_id, brand, model, description, registration, price, year, mileage, fuel, created_at
            FROM cars
            WHERE year = $searchString
            ";
        } else {
            $query = "
            SELECT SQL_CALC_FOUND_ROWS 
            id, url_id, brand, model, description, registration, price, year, mileage, fuel, created_at,
                   MATCH(brand, model, description, registration, fuel)
                   AGAINST (? IN NATURAL LANGUAGE MODE) AS relevance
            FROM cars
            WHERE MATCH(brand, model, description, registration, fuel)
                  AGAINST (? IN NATURAL LANGUAGE MODE)
            ORDER BY relevance DESC
        ";
        }
        if ($limit > 0) {
            $query .= " LIMIT $limit";
            if ($page !== null) {
                $offset = ($page - 1) * $limit;
                $query .= " OFFSET $offset";
            }
        }
        $statement = $connection->prepare($query);
        $statement->bind_param('ss', $searchString, $searchString);
        return $this->getDataFromStatement($statement, $limit, $page);
    }

    protected function getDataFromStatement($statement, ?int $limit = null, ?int $page = null): array
    {
        $statement->execute();
        $result = $statement->get_result();
        $total = $this->connection->query("SELECT FOUND_ROWS()")->fetch_row()[0];
        $data = ['total' => $total,'data' => $this->databaseDataToClass($result)];
        if ($limit > 0) {
            $data['page'] = $page ?? 1;
        }
        return $data;
    }

    protected function databaseDataToClass($result): array
    {
        $cars = [];
        foreach ($result->fetch_all(MYSQLI_ASSOC) as $row) {
            $cars[] = Car::fromArray($row);
        }
        return $cars;
    }
}