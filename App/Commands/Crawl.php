<?php

declare(strict_types=1);

namespace App\Commands;

use App\Model\Crawler;

class Crawl
{

    public function execute(array $args): void
    {
        try {
            $limit = 5;
            echo "Staring crawl...\n";
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
            $crawler->crawl((int)$limit);
            echo "Finished crawl...\n";
        } catch (\Exception $e) {
            echo "Crawl failed.\n";
            echo $e->getMessage();
        }
    }
}