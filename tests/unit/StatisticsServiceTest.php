<?php

declare(strict_types = 1);

namespace Tests\unit;

use DateTime;
use PHPUnit\Framework\TestCase;
use Statistics\Service\StatisticsService;
use Statistics\Calculator\Factory\StatisticsCalculatorFactory;
use Statistics\Builder\ParamsBuilder;
use SocialPost\Dto\SocialPostTo;
use SocialPost\Service\SocialPostService;
use Traversable;

use SocialPost\Driver\SocialDriverInterface;
use SocialPost\Dto\FetchParamsTo;
use SocialPost\Hydrator\SocialPostHydratorInterface;
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
        $statsService = new \Statistics\Service\StatisticsService(new StatisticsCalculatorFactory());
        $stats = $statsService->calculateStats($posts, $params);

        $this->assertEquals(4, $stats->getChildren()[2]->value, 'Total posts per week');
        $this->assertEquals(1, $stats->getChildren()[3]->value, 'Average posts per month per user');
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