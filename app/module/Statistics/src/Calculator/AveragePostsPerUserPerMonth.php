<?php

namespace Statistics\Calculator;

use SocialPost\Dto\SocialPostTo;
use Statistics\Dto\StatisticsTo;

/**
 * Class AveragePostsPerUserPerMonth
 *
 * @package Statistics\Calculator
 */
class AveragePostsPerUserPerMonth extends AbstractCalculator
{

    protected const UNITS = 'posts';

    /**
     * @var array
     */
    private $posts = [];

    /**
     * @var array
     */
    private $users = [];

    /**
     * @param SocialPostTo $postTo
     */
    protected function doAccumulate(SocialPostTo $postTo): void
    {
        $key = $postTo->getDate()->format('F, Y');

        $this->posts[$key] = ($this->posts[$key] ?? 0) + 1;
        $this->users[$key][$postTo->getAuthorId()] = true;
    }

    /**
     * @return StatisticsTo
     */
    protected function doCalculate(): StatisticsTo
    {
        $stats = new StatisticsTo();
        foreach ($this->posts as $splitPeriod => $total) {
            $child = (new StatisticsTo())
                ->setName($this->parameters->getStatName())
                ->setSplitPeriod($splitPeriod)
                ->setValue(round($total / \count($this->users[$splitPeriod]), 2))
                ->setUnits(self::UNITS);

            $stats->addChild($child);
        }

        return $stats;
    }
}
