<?php

/*
 * This file is part of the broadway/broadway package.
 *
 * (c) Qandidate.com <opensource@qandidate.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Broadway\ReadModel\Testing;

use Broadway\Domain\DateTime;
use Broadway\Domain\DomainMessage;
use Broadway\Domain\Metadata;
use Broadway\ReadModel\InMemory\InMemoryRepository;
use Broadway\ReadModel\Projector;
use PHPUnit_Framework_TestCase;

/**
 * Helper testing scenario to test projects.
 *
 * The scenario will help with testing projectors. A scenario consists of
 * three steps:
 *
 * 1) given(): Lets the projector handle some events
 * 2) when():  When a specific event is handled
 * 3) then():  The repository should contain these read models
 */
class Scenario
{
    private $testCase;
    private $projector;
    private $repository;
    private $playhead;

    public function __construct(
        PHPUnit_Framework_TestCase $testCase,
        InMemoryRepository $repository,
        Projector $projector
    ) {
        $this->testCase   = $testCase;
        $this->repository = $repository;
        $this->projector  = $projector;
        $this->playhead   = -1;
    }

    /**
     * @param array $events
     *
     * @return Scenario
     */
    public function given(array $events = array())
    {
        foreach ($events as $given) {
            $this->projector->handle($this->createDomainMessageForEvent($given));
        }

        return $this;
    }

    /**
     * @param array $event
     *
     * @return Scenario
     */
    public function when($event, DateTime $occurredOn = null)
    {
        $this->projector->handle($this->createDomainMessageForEvent($event, $occurredOn));

        return $this;
    }

    /**
     * @param array $expectedData
     *
     * @return Scenario
     */
    public function then(array $expectedData)
    {
        $this->testCase->assertEquals($expectedData, $this->repository->findAll());

        return $this;
    }

    private function createDomainMessageForEvent($event, DateTime $occurredOn = null)
    {
        $this->playhead++;

        if (null === $occurredOn) {
            $occurredOn = DateTime::now();
        }

        return new DomainMessage(1, $this->playhead, new Metadata(array()), $event, $occurredOn);
    }
}