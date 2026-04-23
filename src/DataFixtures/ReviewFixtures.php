<?php

namespace App\DataFixtures;

use App\Entity\Review;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class ReviewFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create('hu_HU');

        $types = ['Kft.', 'Zrt.', 'Bt.'];
        $companies = array_unique(array_map(
            fn() => ucfirst($faker->word()).' '.$faker->randomElement($types),
            range(1, 40)
        ));
        $companies = array_slice(array_values($companies), 0, 25);

        $reviews = [];

        foreach ($companies as $companyName) {
            $reviewCount = random_int(5, 50);

            for ($i = 0; $i < $reviewCount; ++$i) {
                $review = new Review();
                $review->setCompanyName($companyName);
                $review->setRating(random_int(2, 5));
                $review->setReviewText($faker->paragraph(random_int(1, 4)));
                $review->setAuthorEmail($faker->safeEmail());

                $reviews[] = ['review' => $review, 'date' => $faker->dateTimeBetween('-2 years', 'now')];
            }
        }

        shuffle($reviews);

        foreach ($reviews as $item) {
            $item['review']->setCreatedAt($item['date']);
            $item['review']->setUpdatedAt($item['date']);
            $manager->persist($item['review']);
        }

        $manager->flush();
    }

}
