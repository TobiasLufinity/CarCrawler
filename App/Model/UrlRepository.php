<?php

declare(strict_types=1);

namespace App\Model;

class UrlRepository {


    public function getAllUrls(int $limit = 10, string $where = ""): array
    {
        $connection = Database::getInstance()->getConnection();
        $query = "SELECT id, url, crawled_at, updated_at FROM urls";
        if ($where) {
            $query .= " $where";
        }
        $query .=  " ORDER BY crawled_at ASC LIMIT ?";
        $statement = $connection->prepare($query);
        $statement->bind_param("i", $limit);
        $statement->execute();
        $result = $statement->get_result();
        $urls = [];

        foreach ($result->fetch_all(MYSQLI_ASSOC) as $row) {
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
        $statement = $connection->prepare("SELECT id, url, crawled_at, updated_at FROM urls where url = ?");
        $statement->bind_param("s", $url);
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