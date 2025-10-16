<?php

namespace App\Commands;

use App\Model\Crawler;
use App\Model\UrlRepository;
use Exception;

class UpdateCars
{

    public function execute(array $args): void
    {
        try {
            echo "Starting updating cars...\n";
            $limit = 5;
            if (!empty($args)) {
                if (count($args) !== 1 || !isset($args['limit']) || !is_numeric($args['limit'])) {
                    echo "Invalid argument or number of arguments.\n";
                    echo "Available arguments are: \n";
                    echo "--limit=n\n";
                    return;
                }
                $limit = (int)$args['limit'];
            }
            $crawler = new Crawler();
            $urlRepo = new UrlRepository();
            $where = "WHERE id IN (SELECT url_id FROM cars)";
            $urls = $urlRepo->getAllUrls($limit, $where);
            foreach ($urls as $url) {
                $crawler->crawlPage($url);
                //Simple rate limiter
                sleep(1);
            }
            echo "Finished crawl...\n";
        } catch (Exception $e) {
            echo "Crawl failed.\n";
            echo $e->getMessage();
            exit(1);
        }
    }
}