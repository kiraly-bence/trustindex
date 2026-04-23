<?php

namespace App\Tests\Unit\Form;

use App\Entity\Review;
use App\Form\ReviewType;
use Symfony\Component\Form\Extension\Validator\ValidatorExtension;
use Symfony\Component\Form\Test\TypeTestCase;
use Symfony\Component\Validator\Validation;

#[\PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations]
class ReviewTypeTest extends TypeTestCase
{
    protected function getExtensions(): array
    {
        $validator = Validation::createValidatorBuilder()
            ->enableAttributeMapping()
            ->getValidator();

        return [new ValidatorExtension($validator)];
    }

    private function validData(): array
    {
        return [
            'companyName' => 'Nextrend Zrt.',
            'rating' => 4,
            'reviewText' => 'Kiváló kiszolgálás, mindent ajánlok.',
            'authorEmail' => 'user@example.com',
        ];
    }

    public function testValidDataSubmitsSuccessfully(): void
    {
        $form = $this->factory->create(ReviewType::class);
        $form->submit($this->validData());

        $this->assertTrue($form->isSynchronized());
        $this->assertTrue($form->isValid());

        /** @var Review $review */
        $review = $form->getData();
        $this->assertSame('Nextrend Zrt.', $review->getCompanyName());
        $this->assertSame(4, $review->getRating());
        $this->assertSame('user@example.com', $review->getAuthorEmail());
    }

    public function testBlankCompanyNameFails(): void
    {
        $form = $this->factory->create(ReviewType::class);
        $form->submit(array_merge($this->validData(), ['companyName' => '']));

        $this->assertFalse($form->isValid());
        $this->assertGreaterThan(0, count($form->get('companyName')->getErrors()));
    }

    public function testBlankReviewTextFails(): void
    {
        $form = $this->factory->create(ReviewType::class);
        $form->submit(array_merge($this->validData(), ['reviewText' => '']));

        $this->assertFalse($form->isValid());
        $this->assertGreaterThan(0, count($form->get('reviewText')->getErrors()));
    }

    public function testBlankEmailFails(): void
    {
        $form = $this->factory->create(ReviewType::class);
        $form->submit(array_merge($this->validData(), ['authorEmail' => '']));

        $this->assertFalse($form->isValid());
        $this->assertGreaterThan(0, count($form->get('authorEmail')->getErrors()));
    }

    public function testInvalidEmailFails(): void
    {
        $form = $this->factory->create(ReviewType::class);
        $form->submit(array_merge($this->validData(), ['authorEmail' => 'not-an-email']));

        $this->assertFalse($form->isValid());
        $this->assertGreaterThan(0, count($form->get('authorEmail')->getErrors()));
    }

    public function testRatingZeroFails(): void
    {
        $form = $this->factory->create(ReviewType::class);
        $form->submit(array_merge($this->validData(), ['rating' => 0]));

        $this->assertFalse($form->isValid());
        $this->assertGreaterThan(0, count($form->get('rating')->getErrors()));
    }

    public function testRatingSixFails(): void
    {
        $form = $this->factory->create(ReviewType::class);
        $form->submit(array_merge($this->validData(), ['rating' => 6]));

        $this->assertFalse($form->isValid());
        $this->assertGreaterThan(0, count($form->get('rating')->getErrors()));
    }

    public function testRatingOnePasses(): void
    {
        $form = $this->factory->create(ReviewType::class);
        $form->submit(array_merge($this->validData(), ['rating' => 1]));

        $this->assertTrue($form->isValid());
    }

    public function testRatingFivePasses(): void
    {
        $form = $this->factory->create(ReviewType::class);
        $form->submit(array_merge($this->validData(), ['rating' => 5]));

        $this->assertTrue($form->isValid());
    }
}
