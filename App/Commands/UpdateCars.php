<?php

namespace App\Commands;

use App\Model\Crawler;
use App\Model\Database;
use App\Model\Url;
use App\Model\UrlRepository;
use DateTime;

class UpdateCars
{

    public function execute(array $args): void
    {
        try {
            echo "Staring updating cars...\n";
            $limit = 5;
            if (!empty($args)) {
                if (!isset($args['limit']) || !is_numeric($args['limit']) || count($args) > 1) {
                    echo "Invalid argument or number of arguments.\n";
                    echo "Available arguments are: \n";
                    echo "--limit=n\n";
                    return;
                }
                $limit = $args['limit'];
            }
            $crawler = new Crawler();
            $urlRepo = new UrlRepository();
            $where = "WHERE id IN (SELECT url_id FROM cars)";
            $urls = $urlRepo->getAllCUrls((int)$limit, $where);
            foreach ($urls as $url) {
                $crawler->crawlPage($url);
                //Simple rate limiter
                sleep(1);
            }
            echo "Finished crawl...\n";
        } catch (\Exception $e) {
            echo "Crawl failed.\n";
            echo $e->getMessage();
        }
    }
}