<?php

declare(strict_types=1);

namespace App\Model;

use App\Helpers\Config;
use DateTime;
use DOMDocument;
use DOMXPath;

class Crawler {

    protected Config $config;
    protected UrlRepository $urlRepo;

    public function __construct()
    {
        $this->config = Config::getInstance();
        $this->urlRepo = new UrlRepository();
    }

    public function crawl($limit = 5): void
    {
        $urls = $this->urlRepo->getAllCUrls($limit);

        if (empty($urls)) {
            $newUrl = new Url();
            $crawlUrl = $this->config->get('crawl_url');
            $newUrl->setUrl($crawlUrl);
            $this->crawlPage($newUrl);
            $newUrl->setCrawledAt(new DateTime());
            $this->urlRepo->save($newUrl);
            return;
        }

        /** @var Url $url */
        foreach ($urls as $url) {
            $this->crawlPage($url);
            //Simple rate limiter
            sleep(1);
        }
    }

    public function crawlPage(Url $urlObj): void
    {
        $domain = parse_url($urlObj->getUrl(), PHP_URL_HOST);
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $urlObj->getUrl());
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        $content = curl_exec($curl);
        if (!$content) {
            return;
        }

        $dom = new DOMDocument();
        libxml_use_internal_errors(true);
        $dom->loadHTML($content);
        libxml_clear_errors();

        $xpath = new DOMXPath($dom);

        $this->processAnchors($xpath, $domain);

        // Check if the page is a car page, otherwise return
        $checkWord = $this->config->get('car_page_check_word');
        if (str_contains($content, $checkWord)) {
            $carRepo = new CarRepository();
            $car = $carRepo->getCarFromUrl($urlObj->getId());
            $data = $this->processCarPage($xpath);
            $car->setData($data);
            $car->setUrlId($urlObj->getId());
            $carRepo->save($car);
        }
        $urlObj->setCrawledAt(new DateTime());
        $this->urlRepo->save($urlObj);
    }

    protected function processCarPage(DOMXPath $xpath): array
    {
        $data = [];
        foreach (Car::XPATH_VARIABLES as $variable => $expr) {
            $value = $this->getValueFromLabel($xpath, $expr[0]);
            if ($value) {
                if (in_array($expr[1], ['int', 'float'])) {
                    //So far only whole numbers seen on pages. Otherwise, we can include dots or commas
                    $value = preg_replace('/\D/', '', $value);
                }
                settype($value, $expr[1]);
                $data[$variable] = $value;
            }
        }
        return $data;
    }

    protected function getValueFromLabel(DOMXPath $xpath, string $searchExp): string
    {
        $nodes = $xpath->query($searchExp);
        if ($nodes && $nodes->length > 0) {
            return trim($nodes->item(0)->textContent);
        }
        return "";
    }

    protected function processAnchors(DOMXPath $xpath, string $domain): void
    {
        $links = $xpath->query('//a[@href]');
        $urlRepo = new UrlRepository();

        foreach ($links as $link) {
            $linkUrl  = $link->getAttribute('href');
            $parsed = parse_url($linkUrl);
            if (!empty($parsed['host']) && strcasecmp($parsed['host'], $domain) === 0) {
                $UrlParts = parse_url($linkUrl);
                $cleanUrl = $UrlParts['scheme'] . '://' . $UrlParts['host'];
                if (!empty($UrlParts['path'])) {
                    $cleanUrl .= $UrlParts['path'];
                }
                $urlObj = $urlRepo->getUrl($cleanUrl);
                $urlRepo->save($urlObj);
            }
        }
    }
}