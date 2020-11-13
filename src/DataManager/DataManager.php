<?php

namespace App\DataManager;

use App\Repository\EstablishmentRepository;
use Symfony\Component\Cache\Adapter\AdapterInterface;

class DataManager
{
    private EstablishmentRepository $establishmentRepository;
    private AdapterInterface $cachePool;

    function __construct($establishmentRepository, AdapterInterface $cachePool)
    {
        $this->establishmentRepository = $establishmentRepository;
        $this->cachePool = $cachePool;
    }

    function findByCoordinates($coordinates, $radius):array
    {
        $coordinates_near = $coordinates->getByAccuracy(3);
        $item = $this->cachePool->getItem($coordinates_near['latitude'].'*'.$coordinates_near['longitude'].'*'.$radius);

        if (! $item->isHit()) {
            $data = $this->establishmentRepository->findBySquareAndRadius(
                $coordinates->inArray(),
                $coordinates->getSquareCoordinates($radius),
                $radius);

            $item->set(serialize($data));
            $this->cachePool->save($item);
        }else
            $data = unserialize($item->get());

        return $data;
    }
}