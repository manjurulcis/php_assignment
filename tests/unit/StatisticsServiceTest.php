<?php

declare(strict_types = 1);

namespace Tests\unit;

use DateTime;
use PHPUnit\Framework\TestCase;
use Statistics\Service\StatisticsService;
use Statistics\Calculator\Factory\StatisticsCalculatorFactory;
use Statistics\Builder\ParamsBuilder;
use SocialPost\Dto\SocialPostTo;
use Traversable;
use \ArrayIterator;



/**
 * Class StatisticsServiceTest
 *
 * @package Tests\unit
 */
class StatisticsServiceTest extends TestCase
{
    /**
     * @test calculateStats
     */
    public function testCalculateStats(): void
    {
        $data = json_decode(file_get_contents('tests/data/social-posts-response.json'), true);
        $posts = $this->buildTraversablePosts($data['data']['posts']); 
        $this->assertEquals(true, ($posts instanceof Traversable));

        $date  = DateTime::createFromFormat('F, Y', 'August, 2018');
        $params = ParamsBuilder::reportStatsParams($date);
        $statsService = new StatisticsService(new StatisticsCalculatorFactory());
        $stats = $statsService->calculateStats($posts, $params);

        $childrens = $stats->getChildren();
        $this->assertEquals(4, $childrens[2]->getChildren()[0]->getValue(), 'Total posts per week');
        $this->assertEquals(1, $childrens[3]->getValue(), 'Total Average posts per month per user');
    }

    /**
     * @param array $posts
     *
     * @return Traversable
     */
    private function buildTraversablePosts($posts): Traversable
    {
        $traversable_posts = [];
        foreach ($posts as $postData) {
            $traversable_posts[] = $this->hydrate($postData);
        } 

        $posts =  new ArrayIterator($traversable_posts, 0);

        return $posts;
    }

    /**
     * @param array $postData
     *
     * @return SocialPostTo
     */
    private function hydrate(array $postData): SocialPostTo
    {
        $socialTo = new SocialPostTo();
        $socialTo->setId($postData['id'])
        ->setAuthorName($postData['from_name'])
        ->setAuthorId($postData['from_id'])
        ->setText($postData['message'])
        ->setType($postData['type'])
        ->setDate($this->hydrateDate($postData['created_time'] ?? null));;
        return $socialTo;
    }

    /**
     * @param string|null $date
     *
     * @return DateTime|null
     */
    private function hydrateDate(?string $date): ?DateTime
    {
        $date = DateTime::createFromFormat(
            DateTime::ATOM,
            $date
        );

        return false === $date ? null : $date;
    }
}
