<?php

declare(strict_types=1);

namespace App\Component\Master;

use App\Entity\Master;
use App\Entity\MasterService;
use App\Entity\User;
use DateTime;

class MasterFactory
{
    public function create(User $user, array $data): Master
    {
        $master = new Master();
        $master->setUser($user);
        $master->setFirstName($data['firstName']);
        $master->setLastName($data['lastName']);
        $master->setPhone($data['phone'] ?? null);
        $master->setBio($data['bio'] ?? null);
        $master->setRegionName($data['regionName']);
        $master->setAddress($data['address'] ?? null);
        $master->setLat($data['lat'] ?? null);
        $master->setLng($data['lng'] ?? null);
        $master->setCreatedAt(new DateTime());

        if (isset($data['services'])) {
            foreach ($data['services'] as $serviceData) {
                $service = new MasterService();
                $service->setName($serviceData['name']);
                $service->setPrice($serviceData['price']);
                $service->setDurationMinutes($serviceData['durationMinutes']);
                $service->setCategoryName($serviceData['categoryName'] ?? null);
                $master->addService($service);
            }
        }

        return $master;
    }
}
