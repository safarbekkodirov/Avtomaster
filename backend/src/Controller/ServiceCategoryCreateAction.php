<?php

declare(strict_types=1);

namespace App\Controller;

use App\Controller\Base\AbstractController;
use App\Entity\ServiceCategory;
use App\Repository\ServiceCategoryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class ServiceCategoryCreateAction extends AbstractController
{
    public function __invoke(
        Request $request,
        ServiceCategoryRepository $categoryRepository,
        EntityManagerInterface $em,
    ): ServiceCategory {
        $data = json_decode($request->getContent(), true);

        $name = $data['name'] ?? '';
        if (!$name) {
            throw new BadRequestHttpException('name is required');
        }

        $category = new ServiceCategory();
        $category->setName($name);
        $category->setSlug($data['slug'] ?? $this->generateSlug($name, $categoryRepository));
        $category->setDescription($data['description'] ?? null);
        $category->setIcon($data['icon'] ?? null);

        $em->persist($category);
        $em->flush();

        return $category;
    }

    private function generateSlug(string $name, ServiceCategoryRepository $repository): string
    {
        $translit = [
            'а'=>'a','б'=>'b','в'=>'v','г'=>'g','д'=>'d','е'=>'e','ё'=>'yo',
            'ж'=>'zh','з'=>'z','и'=>'i','й'=>'y','к'=>'k','л'=>'l','м'=>'m',
            'н'=>'n','о'=>'o','п'=>'p','р'=>'r','с'=>'s','т'=>'t','у'=>'u',
            'ф'=>'f','х'=>'kh','ц'=>'ts','ч'=>'ch','ш'=>'sh','щ'=>'shch',
            'ъ'=>'','ы'=>'y','ь'=>'','э'=>'e','ю'=>'yu','я'=>'ya',
        ];

        $slug = mb_strtolower($name, 'UTF-8');
        $slug = strtr($slug, $translit);
        $slug = preg_replace('/[^a-z0-9]+/', '-', $slug);
        $slug = trim($slug, '-');

        if (empty($slug)) {
            $slug = 'category';
        }

        $original = $slug;
        $counter = 1;
        while ($repository->findOneBySlug($slug)) {
            $slug = $original . '-' . $counter++;
        }

        return $slug;
    }
}
