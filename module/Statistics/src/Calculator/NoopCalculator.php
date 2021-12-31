<?php

declare(strict_types = 1);

namespace Statistics\Calculator;

use SocialPost\Dto\SocialPostTo;
use Statistics\Dto\StatisticsTo;

class NoopCalculator extends AbstractCalculator
{
    protected const UNITS = 'posts';
    
    /**
     * @var array
     */
    private $user_post_stat = [];

    /**
     * @inheritDoc
     */
    protected function doAccumulate(SocialPostTo $postTo): void
    {
        $key = $postTo->getAuthorId();

        $this->user_post_stat[$key] = ($this->user_post_stat[$key] ?? 0) + 1;
    }

    /**
     * @inheritDoc
     */
    protected function doCalculate(): StatisticsTo
    {
        $average = array_sum($this->user_post_stat)/count($this->user_post_stat);
        return (new StatisticsTo())->setValue($average)->setUnits(self::UNITS);
    }
}
