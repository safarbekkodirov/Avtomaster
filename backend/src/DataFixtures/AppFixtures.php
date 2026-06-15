<?php

namespace App\DataFixtures;

use App\Domain\Master\Entity\Master;
use App\Domain\Master\Entity\MasterService;
use App\Domain\Master\Entity\MasterSlot;
use App\Domain\Region\Entity\Region;
use App\Domain\User\Entity\User;
use App\Domain\User\Entity\UserProfile;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    public function __construct(
        private readonly UserPasswordHasherInterface $passwordHasher,
    ) {}

    public function load(ObjectManager $manager): void
    {
        $regions = $this->createRegions($manager);
        $users   = $this->createUsers($manager);
        $masters = $this->createMasters($manager, $users, $regions);
        $this->createMasterServices($manager, $masters);
        $this->createMasterSlots($manager, $masters);

        $manager->flush();
    }

    /** @return Region[] */
    private function createRegions(ObjectManager $m): array
    {
        $data = [
            ['Бишкек', 'bishkek', 'Кыргызстан'],
            ['Ош', 'osh', 'Кыргызстан'],
            ['Каракол', 'karakol', 'Кыргызстан'],
            ['Токмок', 'tokmok', 'Кыргызстан'],
            ['Балыкчы', 'balychy', 'Кыргызстан'],
            ['Нарын', 'naryn', 'Кыргызстан'],
            ['Талас', 'talas', 'Кыргызстан'],
            ['Джалал-Абад', 'jalal-abad', 'Кыргызстан'],
            ['Кант', 'kant', 'Кыргызстан'],
            ['Кара-Балта', 'kara-balta', 'Кыргызстан'],
            ['Майлуу-Суу', 'mailuu-suu', 'Кыргызстан'],
            ['Таш-Көмүр', 'tash-komur', 'Кыргызстан'],
            ['Кызыл-Кия', 'kyzyl-kiya', 'Кыргызстан'],
            ['Узген', 'uzgen', 'Кыргызстан'],
            ['Кара-Суу', 'kara-su', 'Кыргызстан'],
            ['Чолпон-Ата', 'cholpon-ata', 'Кыргызстан'],
            ['Иссык-Көл', 'issyk-kol', 'Кыргызстан'],
            ['Кемин', 'kemin', 'Кыргызстан'],
            ['Сүлүктү', 'suluktu', 'Кыргызстан'],
            ['Баткен', 'batken', 'Кыргызстан'],
        ];

        $regions = [];
        foreach ($data as [$name, $slug, $country]) {
            $region = new Region($name, $slug, $country);
            $m->persist($region);
            $regions[$slug] = $region;
        }

        return $regions;
    }

    /** @return User[] */
    private function createUsers(ObjectManager $m): array
    {
        $users = [];

        // Админ
        $admin = $this->createUserWithProfile(
            $m,
            'admin@avtomaster.kg',
            'admin123',
            'Админ',
            'Системалык',
            ['ROLE_ADMIN'],
        );
        $users['admin'] = $admin;

        // Мастерлар
        $mastersData = [
            // Бишкек
            ['master1@avtomaster.kg', 'Азамат', 'Жумабеков', '+77001112233'],
            ['master2@avtomaster.kg', 'Бекболот', 'Турсунов', '+77002223344'],
            ['master3@avtomaster.kg', 'Эрлан', 'Сатыбалдиев', '+77003334455'],
            ['master4@avtomaster.kg', 'Данияр', 'Абдырахманов', '+77004445566'],
            ['master5@avtomaster.kg', 'Кубат', 'Мамбетов', '+77005556677'],
            ['master6@avtomaster.kg', 'Арген', 'Касенов', '+77006667788'],
            ['master7@avtomaster.kg', 'Нурбек', 'Абдыкадыров', '+77007778899'],
            ['master8@avtomaster.kg', 'Айбек', 'Токтосунов', '+77008889900'],
            ['master9@avtomaster.kg', 'Мирлан', 'Асанов', '+77009990011'],
            ['master10@avtomaster.kg', 'Тимур', 'Исаков', '+77001001122'],
            // Ош
            ['master11@avtomaster.kg', 'Шерзод', 'Рахимов', '+77001112200'],
            ['master12@avtomaster.kg', 'Фарход', 'Маматов', '+77002223300'],
            ['master13@avtomaster.kg', 'Жалолиддин', 'Абдурахманов', '+77003334400'],
            // Каракол
            ['master14@avtomaster.kg', 'Адилет', 'Жолдошев', '+77004445500'],
            ['master15@avtomaster.kg', 'Бакыт', 'Мамытов', '+77005556600'],
            // Токмок
            ['master16@avtomaster.kg', 'Эркин', 'Сыдыков', '+77006667700'],
            // Балыкчы
            ['master17@avtomaster.kg', 'Азиз', 'Калыков', '+77007778800'],
            // Нарын
            ['master18@avtomaster.kg', 'Чынгыз', 'Абылкасымов', '+77008889900'],
            // Талас
            ['master19@avtomaster.kg', 'Улан', 'Жээнбеков', '+77009990000'],
            // Джалал-Абад
            ['master20@avtomaster.kg', 'Байэл', 'Турдалиев', '+77001001100'],
            // Кант
            ['master21@avtomaster.kg', 'Дилшод', 'Кадыров', '+77001112211'],
            // Кара-Балта
            ['master22@avtomaster.kg', 'Жибек', 'Турсунова', '+77002223322'],
            // Узген
            ['master23@avtomaster.kg', 'Орозбай', 'Сатыбалдиев', '+77003334433'],
            // Баткен
            ['master24@avtomaster.kg', 'Алмаз', 'Бакиров', '+77004445544'],
        ];

        foreach ($mastersData as [$email, $first, $last, $phone]) {
            $users[$email] = $this->createUserWithProfile(
                $m, $email, 'master123', $first, $last, ['ROLE_MASTER'], $phone,
            );
        }

        // Клиенттер
        $clientsData = [
            ['client1@avtomaster.kg', 'Айбек', 'Токтосунов', '+77001111111'],
            ['client2@avtomaster.kg', 'Айгуль', 'Каримова', '+77002222222'],
            ['client3@avtomaster.kg', 'Мирлан', 'Асанов', '+77003333333'],
            ['client4@avtomaster.kg', 'Нурзат', 'Жолдошева', '+77004444444'],
            ['client5@avtomaster.kg', 'Тимур', 'Исаков', '+77005555555'],
            ['client6@avtomaster.kg', 'Асель', 'Мамытова', '+77006666666'],
            ['client7@avtomaster.kg', 'Жаныбек', 'Козубаев', '+77007777777'],
            ['client8@avtomaster.kg', 'Бегимай', 'Абдырахманова', '+77008888888'],
            ['client9@avtomaster.kg', 'Эрлан', 'Жумабаев', '+77009999999'],
            ['client10@avtomaster.kg', 'Айпери', 'Токтогулова', '+77001010101'],
        ];

        foreach ($clientsData as [$email, $first, $last, $phone]) {
            $users[$email] = $this->createUserWithProfile(
                $m, $email, 'client123', $first, $last, ['ROLE_CLIENT'], $phone,
            );
        }

        return $users;
    }

    private function createUserWithProfile(
        ObjectManager $m,
        string $email,
        string $password,
        string $firstName,
        string $lastName,
        array  $roles = ['ROLE_CLIENT'],
        ?string $phone = null,
    ): User {
        $user = new User($email, $roles);
        $user->setPasswordHash($this->passwordHasher->hashPassword($user, $password));

        $profile = new UserProfile($user, $firstName, $lastName);
        if ($phone !== null) {
            $profile->setPhone($phone);
        }
        $user->setProfile($profile);

        $m->persist($user);
        $m->persist($profile);

        return $user;
    }

    /** @param User[] $users @return Master[] */
    private function createMasters(ObjectManager $m, array $users, array $regions): array
    {
        $mastersData = [
            // Бишкек — 10 мастер
            'master1@avtomaster.kg' => [
                $regions['bishkek'], 'ул. Манаса 45, Бишкек', '42.8746', '74.5698',
                'Квалификацияланган автоочерүүчү. 10 жылдан ашык тажрыйба. Япония жана корей автоунаалары боюнча адис.',
                4.8, 24, true,
            ],
            'master2@avtomaster.kg' => [
                $regions['bishkek'], 'пр. Чуй 123, Бишкек', '42.8500', '74.5900',
                'Жогорку даражадагы авто дарылоо. BMW, Mercedes, Audi адистешкен. Европа автоунаалары боюнча адис.',
                4.6, 18, true,
            ],
            'master3@avtomaster.kg' => [
                $regions['bishkek'], 'ул. Ленина 78, Бишкек', '42.8600', '74.5800',
                'Жалпы авто тейлөө. Бардык автоунаа түрлөрүнө тейлөө көрсөтөбүз. Тез жана сапаттуу.',
                4.5, 12, true,
            ],
            'master4@avtomaster.kg' => [
                $regions['bishkek'], 'ул. Тынчтыкова 90, Бишкек', '42.8800', '74.5800',
                'Электр жана электроника боюнча адис. Диагностика, оңдоо, жаңылоо.',
                4.7, 15, true,
            ],
            'master5@avtomaster.kg' => [
                $regions['bishkek'], 'ул. Курманжан Датки 156, Бишкек', '42.8450', '74.5750',
                'Кузов оңдоо жана бояо жумуштары. Реставрация жана түс алмаштыруу.',
                4.4, 10, true,
            ],
            'master6@avtomaster.kg' => [
                $regions['bishkek'], 'ул. Абдрахманова 200, Бишкек', '42.8550', '74.5650',
                'Двигатель жана коробка оңдоо. Капиталдык ремонт.',
                4.3, 8, false,
            ],
            'master7@avtomaster.kg' => [
                $regions['bishkek'], 'ул. Исанова 45, Бишкек', '42.8650', '74.5850',
                'Кондиционер тейлөө жана климат контроль. Фреон充填уу.',
                4.5, 14, true,
            ],
            'master8@avtomaster.kg' => [
                $regions['bishkek'], 'ул. Жибек Жолу 67, Бишкек', '42.8700', '74.5950',
                'Шина алмаштыруу жана балансировка. Бардык өлчөмдөрдө.',
                4.2, 9, true,
            ],
            'master9@avtomaster.kg' => [
                $regions['bishkek'], 'ул. Масалиева 34, Бишкек', '42.8400', '74.5550',
                'Тормоз системасын оңдоо. Тормоз колодкалары, диски, шлангы.',
                4.6, 20, true,
            ],
            'master10@avtomaster.kg' => [
                $regions['bishkek'], 'ул. Раззакова 89, Бишкек', '42.8520', '74.5700',
                'Подвеска жана рулевое управление. Стойки, сайлентблоки, наконечники.',
                4.4, 11, true,
            ],
            // Ош — 3 мастер
            'master11@avtomaster.kg' => [
                $regions['osh'], 'ул. Ленина 78, Ош', '40.5283', '72.7985',
                'Ош шаарындагы мыкты автоочерүүчү. 15 жылдык тажрыйба. Бардык автоунаа түрлөрү.',
                4.7, 32, true,
            ],
            'master12@avtomaster.kg' => [
                $regions['osh'], 'ул. Курманжан Датки 12, Ош', '40.5300', '72.8000',
                'Жапон автоунаалары боюнча адис. Toyota, Nissan, Honda.',
                4.5, 18, true,
            ],
            'master13@avtomaster.kg' => [
                $regions['osh'], 'пр. Навои 45, Ош', '40.5250', '72.7950',
                'Электр оңдоо. Генератор, стартер, аккумулятор.',
                4.3, 8, false,
            ],
            // Каракол — 2 мастер
            'master14@avtomaster.kg' => [
                $regions['karakol'], 'ул. Абдырахманова 15, Каракол', '42.4907', '78.3937',
                'Каракол шаарынын мыкты автоочерүүчүсү. Жөнөкөй жана ишенимдүү.',
                4.6, 15, true,
            ],
            'master15@avtomaster.kg' => [
                $regions['karakol'], 'ул. Курманжан Датки 8, Каракол', '42.4880', '78.3900',
                'Кышкы жана жайкы дөңгөлөктөр. Шина алмаштыруу жана сактоо.',
                4.4, 10, true,
            ],
            // Токмок — 1 мастер
            'master16@avtomaster.kg' => [
                $regions['tokmok'], 'ул. Ленина 23, Токмок', '42.7630', '75.3020',
                'Токмок шаарындагы жалпы авто тейлөө. Капиталдык ремонт.',
                4.5, 12, true,
            ],
            // Балыкчы — 1 мастер
            'master17@avtomaster.kg' => [
                $regions['balychy'], 'ул. Абдырахманова 5, Балыкчы', '42.4600', '76.1800',
                'Балыкчы шаарынын автоочерүүчүсү. Иссык-Көл облусу боюнча.',
                4.3, 7, true,
            ],
            // Нарын — 1 мастер
            'master18@avtomaster.kg' => [
                $regions['naryn'], 'ул. Курманжан Датки 34, Нарын', '41.4287', '75.9911',
                'Нарын облусунун мыкты автоочерүүчүсү. Тоолуу аймакта иштөө тажрыйбасы.',
                4.4, 9, true,
            ],
            // Талас — 1 мастер
            'master19@avtomaster.kg' => [
                $regions['talas'], 'ул. Ленина 12, Талас', '42.5228', '72.2428',
                'Талас облусунун автоочерүүчүсү. Сапаттуу кызмат.',
                4.2, 6, true,
            ],
            // Джалал-Абад — 1 мастер
            'master20@avtomaster.kg' => [
                $regions['jalal-abad'], 'ул. Абдырахманова 67, Джалал-Абад', '40.9333', '73.0000',
                'Джалал-Абад облусунун мыкты автоочерүүчүсү. Тез жана ишенимдүү.',
                4.5, 11, true,
            ],
            // Кант — 1 мастер
            'master21@avtomaster.kg' => [
                $regions['kant'], 'ул. Ленина 8, Кант', '42.8900', '74.8500',
                'Кант шаарынын автоочерүүчүсү. Бишкекке жакын.',
                4.3, 8, true,
            ],
            // Кара-Балта — 1 мастер
            'master22@avtomaster.kg' => [
                $regions['kara-balta'], 'ул. Манаса 15, Кара-Балта', '42.8130', '73.8500',
                'Кара-Балта шаарынын автоочерүүчүсү. Жөнөкөй жана ыңгайлуу.',
                4.1, 5, false,
            ],
            // Узген — 1 мастер
            'master23@avtomaster.kg' => [
                $regions['uzgen'], 'ул. Ленина 22, Узген', '40.7700', '73.3000',
                'Узген шаарынын автоочерүүчүсү. Ош облусу боюнча.',
                4.4, 10, true,
            ],
            // Баткен — 1 мастер
            'master24@avtomaster.kg' => [
                $regions['batken'], 'ул. Абдырахманова 10, Баткен', '40.0625', '70.8194',
                'Баткен облусунун мыкты автоочерүүчүсү. Чек ара аймагы.',
                4.6, 14, true,
            ],
        ];

        $masters = [];
        foreach ($mastersData as $email => [$region, $address, $lat, $lng, $bio, $rating, $reviewsCount, $verified]) {
            $user = $users[$email];
            $master = new Master();
            $master->setUser($user);
            $master->setRegion($region);
            $master->setAddress($address);
            $master->setLat($lat);
            $master->setLng($lng);
            $master->setBio($bio);
            $master->setRating((string) $rating);
            $master->setReviewsCount($reviewsCount);
            $master->setIsVerified($verified);
            $master->setIsActive(true);

            $m->persist($master);
            $masters[$email] = $master;
        }

        return $masters;
    }

    /** @param Master[] $masters */
    private function createMasterServices(ObjectManager $m, array $masters): void
    {
        $servicesByMaster = [
            // Бишкек
            'master1@avtomaster.kg' => [
                ['Компьютердик диагностика', '2500', 30, 1],
                ['Май алмаштыруу', '3500', 45, 1],
                ['Фильтр алмаштыруу', '1500', 15, 1],
                ['Тормоз системасын оңдоо', '5000', 60, 2],
                ['Сүзүүчүнү алмаштыруу', '1500', 20, 2],
                ['Подвеска оңдоо', '7000', 90, 2],
            ],
            'master2@avtomaster.kg' => [
                ['Компьютердик диагностика', '3000', 30, 1],
                ['Двигательди оңдоо', '15000', 180, 3],
                ['Коробка оңдоо', '12000', 120, 3],
                ['Ходовая часть', '8000', 90, 2],
                ['Кондиционер тейлөө', '4000', 40, 5],
            ],
            'master3@avtomaster.kg' => [
                ['Жалпы диагностика', '2000', 30, 1],
                ['Май алмаштыруу', '3000', 40, 1],
                ['Шина алмаштыруу', '800', 20, 6],
                ['Батарея алмаштыруу', '1500', 10, 5],
                ['Суу алмаштыруу', '1200', 20, 1],
            ],
            'master4@avtomaster.kg' => [
                ['Электр диагностика', '3000', 45, 5],
                ['Генератор оңдоо', '6000', 60, 5],
                ['Стартер оңдоо', '5000', 45, 5],
                ['Ойнок оңдоо', '2500', 30, 5],
                ['ABS оңдоо', '8000', 90, 5],
                ['ESP оңдоо', '10000', 120, 5],
            ],
            'master5@avtomaster.kg' => [
                ['Кузов оңдоо', '12000', 120, 4],
                ['Бояо жумуштары', '8000', 80, 4],
                ['Реставрация', '15000', 180, 4],
                ['Түс алмаштыруу', '20000', 240, 4],
                ['Полировка', '3000', 40, 4],
            ],
            'master6@avtomaster.kg' => [
                ['Двигатель капиталдык ремонт', '25000', 360, 3],
                ['Капот алмаштыруу', '8000', 90, 3],
                ['Турбина оңдоо', '15000', 120, 3],
                ['Инжектор тазалоо', '4000', 60, 1],
            ],
            'master7@avtomaster.kg' => [
                ['Кондиционер тейлөө', '4000', 40, 5],
                ['Фреон充填уу', '3000', 30, 5],
                ['Климат контроль оңдоо', '8000', 90, 5],
                ['Радиатор оңдоо', '5000', 60, 2],
            ],
            'master8@avtomaster.kg' => [
                ['Шина алмаштыруу', '600', 15, 6],
                ['Балансировка', '400', 10, 6],
                ['Шина сактоо', '500', 10, 6],
                ['Диск тазалоо', '800', 20, 6],
            ],
            'master9@avtomaster.kg' => [
                ['Тормоз колодкалары алмаштыруу', '3000', 30, 2],
                ['Тормоз диски алмаштыруу', '5000', 40, 2],
                ['Тормоз шлангы алмаштыруу', '2500', 30, 2],
                ['Тормоз системасын прокачкалоо', '1500', 20, 2],
                ['Стояночный тормоз оңдоо', '4000', 45, 2],
            ],
            'master10@avtomaster.kg' => [
                ['Стойки алмаштыруу', '4000', 40, 2],
                ['Сайлентблок алмаштыруу', '3000', 35, 2],
                ['Наконечник алмаштыруу', '2500', 30, 2],
                ['Рулевая тяга оңдоо', '3500', 40, 2],
                ['Амортизатор алмаштыруу', '5000', 50, 2],
            ],
            // Ош
            'master11@avtomaster.kg' => [
                ['Жалпы диагностика', '2000', 30, 1],
                ['Май алмаштыруу', '3000', 40, 1],
                ['Фильтр алмаштыруу', '1200', 15, 1],
                ['Тормоз оңдоо', '4000', 50, 2],
                ['Подвеска оңдоо', '6000', 70, 2],
                ['Двигатель оңдоо', '12000', 150, 3],
            ],
            'master12@avtomaster.kg' => [
                ['Компьютердик диагностика', '2500', 30, 1],
                ['Тойота тейлөө', '3500', 45, 1],
                ['Ниссан тейлөө', '3500', 45, 1],
                ['Хонда тейлөө', '3500', 45, 1],
                ['Двигатель оңдоо', '10000', 120, 3],
            ],
            'master13@avtomaster.kg' => [
                ['Генератор оңдоо', '5000', 60, 5],
                ['Стартер оңдоо', '4000', 45, 5],
                ['Аккумулятор алмаштыруу', '3000', 10, 5],
                ['Электр проводка оңдоо', '6000', 80, 5],
            ],
            // Каракол
            'master14@avtomaster.kg' => [
                ['Жалпы диагностика', '2000', 30, 1],
                ['Май алмаштыруу', '3000', 40, 1],
                ['Шина алмаштыруу', '800', 20, 6],
                ['Тормоз оңдоо', '4000', 50, 2],
                ['Подвеска оңдоо', '6000', 70, 2],
            ],
            'master15@avtomaster.kg' => [
                ['Шина алмаштыруу', '600', 15, 6],
                ['Балансировка', '400', 10, 6],
                ['Шина сактоо', '400', 10, 6],
                ['Кышкы шина алмаштыруу', '800', 20, 6],
                ['Жайкы шина алмаштыруу', '800', 20, 6],
            ],
            // Токмок
            'master16@avtomaster.kg' => [
                ['Жалпы диагностика', '1800', 30, 1],
                ['Май алмаштыруу', '2800', 40, 1],
                ['Тормоз оңдоо', '3500', 50, 2],
                ['Подвеска оңдоо', '5500', 70, 2],
                ['Двигатель оңдоо', '10000', 120, 3],
            ],
            // Балыкчы
            'master17@avtomaster.kg' => [
                ['Жалпы диагностика', '1500', 30, 1],
                ['Май алмаштыруу', '2500', 40, 1],
                ['Шина алмаштыруу', '700', 20, 6],
                ['Тормоз оңдоо', '3000', 50, 2],
            ],
            // Нарын
            'master18@avtomaster.kg' => [
                ['Жалпы диагностика', '1800', 30, 1],
                ['Май алмаштыруу', '2800', 40, 1],
                ['Тормоз оңдоо', '3500', 50, 2],
                ['Подвеска оңдоо', '5500', 70, 2],
                ['Двигатель оңдоо', '10000', 120, 3],
            ],
            // Талас
            'master19@avtomaster.kg' => [
                ['Жалпы диагностика', '1500', 30, 1],
                ['Май алмаштыруу', '2500', 40, 1],
                ['Шина алмаштыруу', '700', 20, 6],
                ['Тормоз оңдоо', '3000', 50, 2],
            ],
            // Джалал-Абад
            'master20@avtomaster.kg' => [
                ['Жалпы диагностика', '1800', 30, 1],
                ['Май алмаштыруу', '2800', 40, 1],
                ['Тормоз оңдоо', '3500', 50, 2],
                ['Подвеска оңдоо', '5500', 70, 2],
                ['Двигатель оңдоо', '10000', 120, 3],
                ['Электр оңдоо', '4000', 50, 5],
            ],
            // Кант
            'master21@avtomaster.kg' => [
                ['Жалпы диагностика', '1800', 30, 1],
                ['Май алмаштыруу', '2800', 40, 1],
                ['Тормоз оңдоо', '3500', 50, 2],
                ['Шина алмаштыруу', '700', 20, 6],
            ],
            // Кара-Балта
            'master22@avtomaster.kg' => [
                ['Жалпы диагностика', '1500', 30, 1],
                ['Май алмаштыруу', '2500', 40, 1],
                ['Шина алмаштыруу', '600', 15, 6],
            ],
            // Узген
            'master23@avtomaster.kg' => [
                ['Жалпы диагностика', '1800', 30, 1],
                ['Май алмаштыруу', '2800', 40, 1],
                ['Тормоз оңдоо', '3500', 50, 2],
                ['Подвеска оңдоо', '5500', 70, 2],
                ['Электр оңдоо', '4000', 50, 5],
            ],
            // Баткен
            'master24@avtomaster.kg' => [
                ['Жалпы диагностика', '2000', 30, 1],
                ['Май алмаштыруу', '3000', 40, 1],
                ['Тормоз оңдоо', '4000', 50, 2],
                ['Подвеска оңдоо', '6000', 70, 2],
                ['Двигатель оңдоо', '12000', 150, 3],
                ['Электр оңдоо', '5000', 60, 5],
            ],
        ];

        foreach ($servicesByMaster as $email => $services) {
            $master = $masters[$email];
            foreach ($services as [$name, $price, $duration, $categoryId]) {
                $service = new MasterService($master, $name, $price, $duration);
                $service->setCategoryId($categoryId);
                $m->persist($service);
            }
        }
    }

    /** @param Master[] $masters */
    private function createMasterSlots(ObjectManager $m, array $masters): void
    {
        $today = new \DateTimeImmutable('today');

        foreach ($masters as $master) {
            for ($day = 1; $day <= 7; $day++) {
                $date = $today->modify("+{$day} days");

                $startHour = 9;
                while ($startHour < 18) {
                    $endHour = $startHour + 2;

                    $start = \DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $date->format('Y-m-d') . sprintf('%02d:00:00', $startHour));
                    $end   = \DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $date->format('Y-m-d') . sprintf('%02d:00:00', $endHour));

                    $slot = new MasterSlot($master, $date, $start, $end);
                    $m->persist($slot);

                    $startHour += 2;
                }
            }
        }
    }
}
