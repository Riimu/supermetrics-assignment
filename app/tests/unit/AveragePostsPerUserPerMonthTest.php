<?php

declare(strict_types = 1);

namespace Tests\unit;

use PHPUnit\Framework\TestCase;
use SocialPost\Hydrator\FictionalPostHydrator;
use Statistics\Calculator\AveragePostsPerUserPerMonth;
use Statistics\Dto\ParamsTo;

/**
 * Class AveragePostsPerUserPerMonthTest
 *
 * @package Tests\unit
 */
class AveragePostsPerUserPerMonthTest extends TestCase
{
    /**
     * @test
     */
    public function testSimpleCalculation(): void
    {
        $this->assertPostStatistics(
            [
                [
                    'period' => 'August, 2018',
                    'value' => 1.0,
                ],
            ],
            __DIR__ . '/../data/social-posts-response.json',
            new \DateTime('2018-08-01'),
            new \DateTime('2018-08-31')
        );
    }

    /**
     * @test
     */
    public function testComplexCalculation(): void
    {
        $this->assertPostStatistics(
            [
                [
                    'period' => 'January, 2023',
                    'value' => 1.5,
                ],
                [
                    'period' => 'December, 2022',
                    'value' => 4.1,
                ],
            ],
            __DIR__ . '/../data/social-posts-response-2.json',
            new \DateTime('2022-12-01'),
            new \DateTime('2023-01-31')
        );
    }

    public function assertPostStatistics(array $expected, string $filename, \DateTime $start, \DateTime $end): void
    {
        $parameters = new ParamsTo();
        $parameters->setStartDate($start);
        $parameters->setEndDate($end);
        $parameters->setStatName('Statistics');

        $calculator = new AveragePostsPerUserPerMonth();
        $calculator->setParameters($parameters);

        $hydrator = new FictionalPostHydrator();
        $posts = json_decode(file_get_contents($filename), true);

        foreach ($posts['data']['posts'] as $post) {
            $calculator->accumulateData($hydrator->hydrate($post));
        }

        $results = $calculator->calculate();
        $children = $results->getChildren();

        $this->assertNotNull($children);
        $this->assertCount(\count($expected), $children);

        foreach ($children as $child) {
            $expectedChild = array_shift($expected);

            $this->assertSame($expectedChild['period'], $child->getSplitPeriod());
            $this->assertSame($expectedChild['value'], $child->getValue());
        }
    }
}
