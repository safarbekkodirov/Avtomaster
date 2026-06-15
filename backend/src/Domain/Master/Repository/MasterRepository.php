<?php
// src/Domain/Master/Repository/MasterRepository.php

namespace App\Domain\Master\Repository;

use App\Domain\Master\DTO\MasterResponseDTO;
use App\Domain\Master\DTO\MasterSearchRequestDTO;
use App\Domain\Master\DTO\MasterServiceDTO;
use App\Domain\Master\DTO\SearchResultDTO;
use App\Domain\Master\Entity\Master;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\ParameterType;
use Doctrine\Persistence\ManagerRegistry;

class MasterRepository extends ServiceEntityRepository implements MasterSearchRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Master::class);
    }

    /**
     * Поиск через нативный SQL — Haversine + фильтры + пагинация.
     * Doctrine DQL не поддерживает ACOS/RADIANS нативно без кастомных функций.
     * Используем нативный запрос только здесь, изолированно.
     */
    public function search(MasterSearchRequestDTO $dto): SearchResultDTO
    {
        $conditions = ['m.is_active = 1'];
        $params     = [];

        // Фильтр по региону
        if ($dto->regionSlug !== null) {
            $conditions[] = 'r.slug = :regionSlug';
            $params['regionSlug'] = $dto->regionSlug;
        }

        // Геопоиск Haversine
        $distanceSelect = 'NULL as distance_km';
        if ($dto->lat !== null && $dto->lng !== null) {
            $distanceSelect = '(6371 * ACOS(
                COS(RADIANS(:lat)) * COS(RADIANS(m.lat)) *
                COS(RADIANS(m.lng) - RADIANS(:lng)) +
                SIN(RADIANS(:lat)) * SIN(RADIANS(m.lat))
            )) as distance_km';
            $params['lat'] = $dto->lat;
            $params['lng'] = $dto->lng;

            $conditions[] = '(6371 * ACOS(
                COS(RADIANS(:lat)) * COS(RADIANS(m.lat)) *
                COS(RADIANS(m.lng) - RADIANS(:lng)) +
                SIN(RADIANS(:lat)) * SIN(RADIANS(m.lat))
            )) <= :radius';
            $params['radius'] = $dto->radiusKm ?? 10;
        }

        // Фильтр по категории услуги
        if ($dto->categoryId !== null) {
            $conditions[] = 'EXISTS (
                SELECT 1 FROM master_services ms2
                WHERE ms2.master_id = m.id
                  AND ms2.category_id = :categoryId
                  AND ms2.is_active = 1
            )';
            $params['categoryId'] = $dto->categoryId;
        }

        // Фильтр по максимальной цене
        if ($dto->maxPrice !== null) {
            $conditions[] = 'EXISTS (
                SELECT 1 FROM master_services ms3
                WHERE ms3.master_id = m.id
                  AND ms3.price <= :maxPrice
                  AND ms3.is_active = 1
            )';
            $params['maxPrice'] = $dto->maxPrice;
        }

        // Фильтр по минимальному рейтингу
        if ($dto->minRating !== null) {
            $conditions[] = 'm.rating >= :minRating';
            $params['minRating'] = $dto->minRating;
        }

        $where  = implode(' AND ', $conditions);
        $offset = ($dto->page - 1) * $dto->perPage;

        $orderBy = match ($dto->sortBy) {
            'distance' => ($dto->lat !== null ? 'distance_km ASC' : 'm.rating DESC'),
            'price'    => 'min_price ASC',
            default    => 'm.rating DESC',
        };

        // Получаем ID мастеров с пагинацией (избегаем N+1 через отдельный запрос на услуги)
        $sql = "
            SELECT
                m.id,
                up.first_name,
                up.last_name,
                up.avatar,
                r.name        AS region_name,
                m.address,
                m.rating,
                m.reviews_count,
                m.is_verified,
                {$distanceSelect},
                MIN(ms.price) AS min_price
            FROM masters m
            INNER JOIN user_profiles up ON up.user_id = m.user_id
            INNER JOIN regions r        ON r.id = m.region_id
            LEFT  JOIN master_services ms ON ms.master_id = m.id AND ms.is_active = 1
            WHERE {$where}
            GROUP BY m.id, up.first_name, up.last_name, up.avatar,
                     r.name, m.address, m.rating, m.reviews_count, m.is_verified,
                     m.lat, m.lng
            ORDER BY {$orderBy}
            LIMIT :limit OFFSET :offset
        ";

        $countSql = "
            SELECT COUNT(DISTINCT m.id)
            FROM masters m
            INNER JOIN regions r ON r.id = m.region_id
            WHERE {$where}
        ";

        $conn = $this->getEntityManager()->getConnection();

        // Считаем total отдельным запросом
        $countStmt = $conn->prepare($countSql);
        foreach ($params as $key => $value) {
            $countStmt->bindValue($key, $value);
        }
        $total = (int) $countStmt->executeQuery()->fetchOne();

        // Основной запрос
        $stmt = $conn->prepare($sql);
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->bindValue('limit',  $dto->perPage, ParameterType::INTEGER);
        $stmt->bindValue('offset', $offset,       ParameterType::INTEGER);

        $rows      = $stmt->executeQuery()->fetchAllAssociative();
        $masterIds = array_column($rows, 'id');

        // Загружаем услуги для найденных мастеров одним запросом (не N+1)
        $servicesByMaster = $this->fetchServicesByMasterIds($masterIds);

        $items = array_map(
            fn(array $row) => new MasterResponseDTO(
                id:           (int)   $row['id'],
                firstName:            $row['first_name'],
                lastName:             $row['last_name'],
                avatar:               $row['avatar'],
                regionName:           $row['region_name'],
                address:              $row['address'],
                rating:       (float) $row['rating'],
                reviewsCount: (int)   $row['reviews_count'],
                isVerified:   (bool)  $row['is_verified'],
                distanceKm:   $row['distance_km'] !== null ? (float) $row['distance_km'] : null,
                services:             $servicesByMaster[(int) $row['id']] ?? [],
            ),
            $rows
        );

        return new SearchResultDTO(
            items:   $items,
            total:   $total,
            page:    $dto->page,
            perPage: $dto->perPage,
        );
    }

    /**
     * Загрузка услуг батчем — один запрос на все найденные мастера
     *
     * @param int[] $masterIds
     * @return array<int, MasterServiceDTO[]>
     */
    private function fetchServicesByMasterIds(array $masterIds): array
    {
        if (empty($masterIds)) {
            return [];
        }

        $placeholders = implode(',', array_fill(0, count($masterIds), '?'));

        $sql = "
            SELECT
                ms.id,
                ms.master_id,
                ms.name,
                ms.price,
                ms.duration,
                ms.category_id
            FROM master_services ms
            WHERE ms.master_id IN ({$placeholders})
              AND ms.is_active = 1
            ORDER BY ms.price ASC
        ";

        $rows = $this->getEntityManager()
            ->getConnection()
            ->executeQuery($sql, array_values($masterIds))
            ->fetchAllAssociative();

        $result = [];
        foreach ($rows as $row) {
            $result[(int) $row['master_id']][] = new MasterServiceDTO(
                id:              (int)   $row['id'],
                name:                    $row['name'],
                price:           (float) $row['price'],
                durationMinutes: (int)   $row['duration'],
                categoryName:            $row['category_id'] !== null ? (string) $row['category_id'] : '',
            );
        }

        return $result;
    }

    /**
     * Публичный профиль мастера с услугами
     */
    public function findPublicProfile(int $masterId): ?Master
    {
        return $this->createQueryBuilder('m')
            ->innerJoin('m.user', 'u')
            ->innerJoin('u.profile', 'up')
            ->innerJoin('m.region', 'r')
            ->leftJoin('m.services', 's', 'WITH', 's.isActive = true')
            ->addSelect('u', 'up', 'r', 's')
            ->where('m.id = :id')
            ->andWhere('m.isActive = true')
            ->setParameter('id', $masterId)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findByUser(int $userId): ?Master
    {
        return $this->createQueryBuilder('m')
            ->where('m.user = :userId')
            ->setParameter('userId', $userId)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
