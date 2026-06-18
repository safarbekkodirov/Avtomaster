<?php

declare(strict_types=1);

namespace App\Command;

use App\Entity\Master;
use App\Entity\MasterService;
use App\Entity\Region;
use App\Entity\User;
use App\Repository\RegionRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsCommand(
    name: 'app:seed-test-data',
    description: 'Seeds the database with test regions, users, masters and services',
)]
class SeedTestDataCommand extends Command
{
    public function __construct(
        private EntityManagerInterface $em,
        private UserRepository $userRepository,
        private RegionRepository $regionRepository,
        private UserPasswordHasherInterface $passwordEncoder,
        ?string $name = null
    ) {
        parent::__construct($name);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('Seeding test data...');

        $this->seedRegions($io);
        $this->seedUsersAndMasters($io);

        $io->success('Done! Test data seeded successfully.');
        $io->text('Emails: admin@avtomaster.kg, timur@avtomaster.kg, akyl@avtomaster.kg, nursultan@avtomaster.kg, ermek@avtomaster.kg, client@avtomaster.kg');
        $io->text('Password: password123');

        return Command::SUCCESS;
    }

    private function seedRegions(SymfonyStyle $io): void
    {
        $regions = [
            ['name' => 'Бишкек',      'slug' => 'bishkek',    'lat' => '42.8746212', 'lng' => '74.5698303'],
            ['name' => 'Ош',          'slug' => 'osh',        'lat' => '40.5282849', 'lng' => '72.7985091'],
            ['name' => 'Каракол',     'slug' => 'karakol',    'lat' => '42.4906937', 'lng' => '78.3936316'],
            ['name' => 'Токмок',      'slug' => 'tokmok',     'lat' => '42.8283333', 'lng' => '75.3013889'],
            ['name' => 'Джалал-Абад', 'slug' => 'jalal-abad', 'lat' => '40.9333333', 'lng' => '73.0000000'],
            ['name' => 'Нарын',       'slug' => 'naryn',      'lat' => '41.4286333', 'lng' => '75.9911111'],
            ['name' => 'Талас',       'slug' => 'talas',      'lat' => '42.5227778', 'lng' => '72.2427778'],
        ];

        $count = 0;
        foreach ($regions as $data) {
            if ($this->regionRepository->findOneBySlug($data['slug'])) {
                continue;
            }
            $region = new Region();
            $region->setName($data['name']);
            $region->setSlug($data['slug']);
            $region->setLat($data['lat']);
            $region->setLng($data['lng']);
            $this->em->persist($region);
            $count++;
        }
        $this->em->flush();
        $io->text("Regions seeded: {$count}");
    }

    private function seedUsersAndMasters(SymfonyStyle $io): void
    {
        $testPassword = 'password123';

        $mastersData = [
            [
                'email'     => 'admin@avtomaster.kg',
                'firstName' => 'Админ',
                'lastName'  => 'Система',
                'phone'     => '+996555000000',
                'bio'       => 'Администратор системы',
                'regionName'=> 'Бишкек',
                'address'   => 'ул. Манаса 45',
                'lat'       => '42.8746212',
                'lng'       => '74.5698303',
                'roles'     => ['ROLE_ADMIN', 'ROLE_MASTER'],
                'services'  => [],
            ],
            [
                'email'     => 'timur@avtomaster.kg',
                'firstName' => 'Тимур',
                'lastName'  => 'Жумабеков',
                'phone'     => '+996700123456',
                'bio'       => '10 жылдан ашык тажрыйбасы бар автомеханик. Двигатель, коробка передач, ходовая часть боюнча адис.',
                'regionName'=> 'Бишкек',
                'address'   => 'ул. Чуй 100, Микрорайон 6',
                'lat'       => '42.8916667',
                'lng'       => '74.5444444',
                'roles'     => ['ROLE_MASTER'],
                'services'  => [
                    ['name' => 'Двигательди оңдоо',       'price' => '15000', 'durationMinutes' => 120, 'categoryName' => 'Двигатель'],
                    ['name' => 'Май алмаштыруу',           'price' => '1500',  'durationMinutes' => 30,  'categoryName' => 'Техосмотр'],
                    ['name' => 'Коробка передач оңдоо',     'price' => '20000', 'durationMinutes' => 180, 'categoryName' => 'Коробка'],
                    ['name' => 'Тормоз системасын оңдоо',   'price' => '8000',  'durationMinutes' => 60,  'categoryName' => 'Ходовая часть'],
                ],
            ],
            [
                'email'     => 'akyl@avtomaster.kg',
                'firstName' => 'Акыл',
                'lastName'  => 'Сатыбалдиев',
                'phone'     => '+996777654321',
                'bio'       => 'Электрика жана диагностика боюнча адис. Авто электр жабдыктарын оңдоо.',
                'regionName'=> 'Бишкек',
                'address'   => 'пр. Манаса 32',
                'lat'       => '42.8683333',
                'lng'       => '74.5916667',
                'roles'     => ['ROLE_MASTER'],
                'services'  => [
                    ['name' => 'Авто диагностикадан өткөрүү', 'price' => '3000',  'durationMinutes' => 60,  'categoryName' => 'Диагностика'],
                    ['name' => 'Электр жабдыктарын оңдоо',   'price' => '7000',  'durationMinutes' => 90,  'categoryName' => 'Электрика'],
                    ['name' => 'Стартер оңдоо',              'price' => '5000',  'durationMinutes' => 45,  'categoryName' => 'Электрика'],
                ],
            ],
            [
                'email'     => 'nursultan@avtomaster.kg',
                'firstName' => 'Нурсултан',
                'lastName'  => 'Абдырахманов',
                'phone'     => '+996500111222',
                'bio'       => 'Кузов жана жасалма боюнча адис. Кырдык, сыдырмалоо, боёо иштери.',
                'regionName'=> 'Ош',
                'address'   => 'ул. Ленина 78',
                'lat'       => '40.5282849',
                'lng'       => '72.7985091',
                'roles'     => ['ROLE_MASTER'],
                'services'  => [
                    ['name' => 'Кузов оңдоо',              'price' => '25000', 'durationMinutes' => 240, 'categoryName' => 'Кузов'],
                    ['name' => 'Боёо иштери',               'price' => '30000', 'durationMinutes' => 300, 'categoryName' => 'Кузов'],
                    ['name' => 'Полировка',                  'price' => '5000',  'durationMinutes' => 90,  'categoryName' => 'Кузов'],
                    ['name' => 'Жасалма (царапина) оңдоо',   'price' => '3000',  'durationMinutes' => 60,  'categoryName' => 'Кузов'],
                ],
            ],
            [
                'email'     => 'ermek@avtomaster.kg',
                'firstName' => 'Эрмек',
                'lastName'  => 'Токтосунов',
                'phone'     => '+996701999888',
                'bio'       => 'Кондиционер жана муздатуу системасын оңдоо боюнча адис.',
                'regionName'=> 'Каракол',
                'address'   => 'ул. Абдрахманова 15',
                'lat'       => '42.4906937',
                'lng'       => '78.3936316',
                'roles'     => ['ROLE_MASTER'],
                'services'  => [
                    ['name' => 'Кондиционерди толтуруу', 'price' => '4000',  'durationMinutes' => 30,  'categoryName' => 'Климат'],
                    ['name' => 'Кондиционер оңдоо',       'price' => '12000', 'durationMinutes' => 120, 'categoryName' => 'Климат'],
                    ['name' => 'Радиатор оңдоо',          'price' => '8000',  'durationMinutes' => 90,  'categoryName' => 'Суу системасы'],
                ],
            ],
            [
                'email'     => 'client@avtomaster.kg',
                'firstName' => 'Бакыт',
                'lastName'  => 'Исаков',
                'phone'     => '+996777000111',
                'bio'       => null,
                'regionName'=> 'Бишкек',
                'address'   => null,
                'lat'       => null,
                'lng'       => null,
                'roles'     => ['ROLE_USER'],
                'services'  => [],
            ],
        ];

        $now = new \DateTime();
        $count = 0;

        foreach ($mastersData as $data) {
            if ($this->userRepository->findOneByEmail($data['email'])) {
                $io->text("Skipped (already exists): {$data['email']}");
                continue;
            }

            $user = new User();
            $user->setEmail($data['email']);
            $user->setPassword($this->passwordEncoder->hashPassword($user, $testPassword));
            $user->setCreatedAt($now);
            $user->setRoles($data['roles']);

            $this->em->persist($user);
            $this->em->flush();

            $userId = $user->getId();
            $io->text("Created user #{$userId}: {$data['email']}");

            if (in_array('ROLE_MASTER', $data['roles'], true) && !empty($data['services'])) {
                $master = new Master();
                $master->setUser($user);
                $master->setFirstName($data['firstName']);
                $master->setLastName($data['lastName']);
                $master->setPhone($data['phone']);
                $master->setBio($data['bio']);
                $master->setRegionName($data['regionName']);
                $master->setAddress($data['address']);
                $master->setLat($data['lat']);
                $master->setLng($data['lng']);
                $master->setCreatedAt($now);

                foreach ($data['services'] as $svc) {
                    $service = new MasterService();
                    $service->setName($svc['name']);
                    $service->setPrice($svc['price']);
                    $service->setDurationMinutes($svc['durationMinutes']);
                    $service->setCategoryName($svc['categoryName']);
                    $master->addService($service);
                }

                $this->em->persist($master);
                $this->em->flush();

                $io->text("  -> Master #{$master->getId()} with " . count($data['services']) . " services");
            }

            $count++;
        }

        $io->text("Users + Masters seeded: {$count}");
    }
}
