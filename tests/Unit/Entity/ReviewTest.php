<?php

namespace App\Tests\Unit\Entity;

use App\Entity\Review;
use PHPUnit\Framework\TestCase;

class ReviewTest extends TestCase
{
    public function testGettersAndSetters(): void
    {
        $review = new Review();

        $review->setCompanyName('Veloria Kft.');
        $this->assertSame('Veloria Kft.', $review->getCompanyName());

        $review->setRating(4);
        $this->assertSame(4, $review->getRating());

        $review->setReviewText('Kiváló szolgáltatás.');
        $this->assertSame('Kiváló szolgáltatás.', $review->getReviewText());

        $review->setAuthorEmail('test@example.com');
        $this->assertSame('test@example.com', $review->getAuthorEmail());

        $date = new \DateTime('2024-06-01 12:00:00');
        $review->setCreatedAt($date);
        $this->assertSame($date, $review->getCreatedAt());

        $review->setUpdatedAt($date);
        $this->assertSame($date, $review->getUpdatedAt());

        $this->assertNull($review->getId());
    }

    public function testOnPrePersistSetsTimestamps(): void
    {
        $review = new Review();
        $before = new \DateTime();

        $review->onPrePersist();

        $this->assertGreaterThanOrEqual($before, $review->getCreatedAt());
        $this->assertGreaterThanOrEqual($before, $review->getUpdatedAt());
    }

    public function testOnPrePersistDoesNotOverrideExistingTimestamps(): void
    {
        $review = new Review();
        $existingDate = new \DateTime('2020-01-01');
        $review->setCreatedAt($existingDate);
        $review->setUpdatedAt($existingDate);

        $review->onPrePersist();

        $this->assertSame($existingDate, $review->getCreatedAt());
        $this->assertSame($existingDate, $review->getUpdatedAt());
    }

    public function testOnPreUpdateSetsUpdatedAt(): void
    {
        $review = new Review();
        $oldDate = new \DateTime('2020-01-01');
        $review->setCreatedAt($oldDate);
        $review->setUpdatedAt($oldDate);

        $before = new \DateTime();
        $review->onPreUpdate();

        $this->assertGreaterThanOrEqual($before, $review->getUpdatedAt());
        $this->assertSame($oldDate, $review->getCreatedAt());
    }

    public function testSettersReturnStaticForChaining(): void
    {
        $review = new Review();

        $result = $review
            ->setCompanyName('Test')
            ->setRating(3)
            ->setReviewText('OK')
            ->setAuthorEmail('a@b.com')
            ->setCreatedAt(new \DateTime())
            ->setUpdatedAt(new \DateTime());

        $this->assertSame($review, $result);
    }
}
