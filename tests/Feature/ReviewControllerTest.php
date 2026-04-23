<?php

namespace App\Tests\Feature;

use App\Entity\Review;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ReviewControllerTest extends WebTestCase
{
    private KernelBrowser $client;
    private EntityManagerInterface $em;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->em = static::getContainer()->get('doctrine.orm.entity_manager');

        $schemaTool = new SchemaTool($this->em);
        $schemaTool->dropSchema($this->em->getMetadataFactory()->getAllMetadata());
        $schemaTool->createSchema($this->em->getMetadataFactory()->getAllMetadata());
    }

    protected function tearDown(): void
    {
        (new SchemaTool($this->em))->dropSchema($this->em->getMetadataFactory()->getAllMetadata());
        parent::tearDown();
    }

    private function createReview(array $overrides = []): Review
    {
        $review = new Review();
        $review->setCompanyName($overrides['companyName'] ?? 'Test Company');
        $review->setRating($overrides['rating'] ?? 4);
        $review->setReviewText($overrides['reviewText'] ?? 'Ez egy tesztvélemény.');
        $review->setAuthorEmail($overrides['authorEmail'] ?? 'test@example.com');
        $review->setCreatedAt(new \DateTime($overrides['createdAt'] ?? 'now'));
        $review->setUpdatedAt(new \DateTime());

        $this->em->persist($review);
        $this->em->flush();

        return $review;
    }

    private function responseContent(): string
    {
        return $this->client->getResponse()->getContent();
    }

    // --- Index page ---

    public function testIndexPageLoads(): void
    {
        $this->client->request('GET', '/');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('form');
        $this->assertSelectorTextContains('h2', 'Vélemény beküldése');
    }

    public function testIndexPageShowsExistingReviews(): void
    {
        $this->createReview(['companyName' => 'Veloria Kft.']);

        $this->client->request('GET', '/');

        $this->assertResponseIsSuccessful();
        $this->assertStringContainsString('Veloria Kft.', $this->responseContent());
    }

    public function testIndexPageShowsEmptyStateWhenNoReviews(): void
    {
        $this->client->request('GET', '/');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('p', 'Még nincs vélemény.');
    }

    // --- Submit review ---

    public function testValidSubmitRedirectsAndShowsFlash(): void
    {
        $crawler = $this->client->request('GET', '/');
        $form = $crawler->selectButton('Beküldés')->form([
            'review[companyName]' => 'Nextrend Zrt.',
            'review[rating]' => 4,
            'review[reviewText]' => 'Kiváló szolgáltatás, mindenkinek ajánlom.',
            'review[authorEmail]' => 'user@example.com',
        ]);

        $this->client->submit($form);

        $this->assertResponseRedirects('/');
        $this->client->followRedirect();
        $this->assertSelectorTextContains('.alert-success', 'Köszönjük a véleményed!');
    }

    public function testValidSubmitPersistsReview(): void
    {
        $crawler = $this->client->request('GET', '/');
        $form = $crawler->selectButton('Beküldés')->form([
            'review[companyName]' => 'Saved Company',
            'review[rating]' => 5,
            'review[reviewText]' => 'Tökéletes!',
            'review[authorEmail]' => 'saved@example.com',
        ]);

        $this->client->submit($form);
        $this->client->followRedirect();

        $this->assertStringContainsString('Saved Company', $this->responseContent());
    }

    public function testInvalidSubmitReturnsUnprocessable(): void
    {
        $crawler = $this->client->request('GET', '/');
        $form = $crawler->selectButton('Beküldés')->form([
            'review[companyName]' => '',
            'review[rating]' => 4,
            'review[reviewText]' => '',
            'review[authorEmail]' => 'not-an-email',
        ]);

        $this->client->submit($form);

        $this->assertResponseStatusCodeSame(422);
        $this->assertStringContainsString('Vélemény beküldése', $this->responseContent());
    }

    // --- Show page ---

    public function testShowPageDisplaysFullReview(): void
    {
        $review = $this->createReview([
            'companyName' => 'Brightora Zrt.',
            'reviewText' => 'Hosszú vélemény szövege itt.',
            'authorEmail' => 'author@example.com',
        ]);

        $this->client->request('GET', '/review/'.$review->getId());

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h2', 'Brightora Zrt.');
        $this->assertStringContainsString('Hosszú vélemény szövege itt.', $this->responseContent());
        $this->assertStringContainsString('author@example.com', $this->responseContent());
    }

    public function testShowPageReturns404ForMissingReview(): void
    {
        $this->client->request('GET', '/review/99999');

        $this->assertResponseStatusCodeSame(404);
    }

    // --- Companies page ---

    public function testCompaniesPageLoads(): void
    {
        $this->createReview(['companyName' => 'Alpha Kft.']);

        $this->client->request('GET', '/companies');

        $this->assertResponseIsSuccessful();
        $this->assertStringContainsString('Alpha Kft.', $this->responseContent());
    }

    public function testCompaniesPageShowsAggregatedStats(): void
    {
        $this->createReview(['companyName' => 'Stats Co', 'rating' => 4]);
        $this->createReview(['companyName' => 'Stats Co', 'rating' => 2]);

        $this->client->request('GET', '/companies');

        $this->assertResponseIsSuccessful();
        $this->assertStringContainsString('Stats Co', $this->responseContent());
        $this->assertStringContainsString('3.0', $this->responseContent());
    }

    public function testCompaniesAverageRatingCalculation(): void
    {
        $this->createReview(['companyName' => 'Avg Co', 'rating' => 5]);
        $this->createReview(['companyName' => 'Avg Co', 'rating' => 5]);
        $this->createReview(['companyName' => 'Avg Co', 'rating' => 2]);

        $this->client->request('GET', '/companies');

        // (5 + 5 + 2) / 3 = 4.0
        $this->assertStringContainsString('4.0', $this->responseContent());
    }

    public function testCompaniesSortedByAverageRatingDescending(): void
    {
        $this->createReview(['companyName' => 'Low Rated',  'rating' => 2]);
        $this->createReview(['companyName' => 'High Rated', 'rating' => 5]);
        $this->createReview(['companyName' => 'Mid Rated',  'rating' => 3]);

        $this->client->request('GET', '/companies');

        $content = $this->responseContent();
        $posHigh = strpos($content, 'High Rated');
        $posMid = strpos($content, 'Mid Rated');
        $posLow = strpos($content, 'Low Rated');

        $this->assertLessThan($posMid, $posHigh, 'High Rated should appear before Mid Rated');
        $this->assertLessThan($posLow, $posMid, 'Mid Rated should appear before Low Rated');
    }

    public function testCompaniesSearchFiltersResults(): void
    {
        $this->createReview(['companyName' => 'Alpha Kft.']);
        $this->createReview(['companyName' => 'Beta Zrt.']);

        $this->client->request('GET', '/companies?search=Alpha');

        $this->assertResponseIsSuccessful();
        $this->assertStringContainsString('Alpha Kft.', $this->responseContent());
        $this->assertStringNotContainsString('Beta Zrt.', $this->responseContent());
    }

    public function testCompaniesSearchWithNoResultsShowsEmptyState(): void
    {
        $this->createReview(['companyName' => 'Alpha Kft.']);

        $this->client->request('GET', '/companies?search=nonexistent');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('p', 'Még nincs értékelt cég.');
    }

    // --- Pagination ---

    public function testPaginationAppearsWithMoreThan10Reviews(): void
    {
        for ($i = 1; $i <= 11; ++$i) {
            $this->createReview(['companyName' => "Company $i"]);
        }

        $this->client->request('GET', '/');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('.pagination');
    }

    public function testSecondPageLoads(): void
    {
        for ($i = 1; $i <= 11; ++$i) {
            $this->createReview(['companyName' => "Company $i"]);
        }

        $this->client->request('GET', '/?page=2');

        $this->assertResponseIsSuccessful();
    }
}
