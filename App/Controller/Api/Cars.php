<?php

namespace App\Controller\Api;

use App\Model\CarRepository;
use Exception;
use InvalidArgumentException;

class Cars extends Controller
{

    public function search(): string
    {
        try {
            $searchString = $this->getPostParam('search');
            $page = $this->getPostParam('page') ?? null;
            $limit = $this->getPostParam('limit') ?? null;
            $carRepository = new CarRepository();

            $cars = $searchString
                ? $carRepository->search($searchString, $limit, $page)
                : $carRepository->getAllCars($limit, $page);

            return $this->jsonResponse($cars);
        } catch (InvalidArgumentException $e) {
            return $this->jsonError('Bad request', $e, 400);
        } catch (Exception $e) {
            return $this->jsonError('Internal Server Error', $e);
        }
    }

}