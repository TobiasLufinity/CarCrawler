<?php

declare(strict_types=1);

namespace App\Model;

class UrlRepository {


    public function getAllCUrls(int $limit = 10, string $where = ""): array
    {
        $connection = Database::getInstance()->getConnection();
        $query = "SELECT * FROM urls";
        if ($where) {
            $query .= " $where";
        }
        $query .=  " ORDER BY crawled_at ASC LIMIT ?";
        $statement = $connection->prepare($query);
        $statement->bind_param("i", $limit);
        $statement->execute();
        $result = $statement->get_result();
        $urls = [];

        while ($row = $result->fetch_assoc()) {
            $urls[] = Url::fromArray($row);
        }

        $statement->close();
        return $urls;
    }

    public function save(Url $urlObj): Url
    {
        $connection = Database::getInstance()->getConnection();
        $query = $connection->prepare("
            INSERT INTO urls (url, crawled_at)
            VALUES (?, ?)
            ON DUPLICATE KEY UPDATE
                crawled_at = VALUES(crawled_at),
                updated_at = CURRENT_TIMESTAMP
            ");
        $url = $urlObj->getUrl();
        $crawledAt = $urlObj->getCrawledAt() ? $urlObj->getCrawledAt()->format('Y-m-d H:i:s') : null;
        $query->bind_param("ss",
            $url,
            $crawledAt
        );

        $query->execute();
        $query->close();
        return $urlObj;
    }

    public function getUrl(string $url): Url
    {
        $connection = Database::getInstance()->getConnection();
        $query = "SELECT * FROM urls where url = \"$url\"";
        $statement = $connection->prepare($query);
        $statement->execute();
        $result = $statement->get_result();

        $data = $result->fetch_assoc();
        if (!$data) {
            $urlObj = new Url();
            return $urlObj->setUrl($url);
        }
        return Url::fromArray($data);
    }
}