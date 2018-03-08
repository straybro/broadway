<?php

/*
 * This file is part of the broadway/broadway package.
 *
 * (c) Qandidate.com <opensource@qandidate.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Broadway\EventSourcing;

use Assert\Assertion as Assert;
use Broadway\Domain\AggregateRoot;
use Broadway\Domain\DomainEventStream;
use Broadway\EventHandling\EventBus;
use Broadway\EventSourcing\AggregateFactory\AggregateFactory;
use Broadway\EventStore\EventStore;
use Broadway\EventStore\EventStreamNotFoundException;
use Broadway\SnapshotStore\SnapshotStoreInterface;
use Broadway\Repository\AggregateNotFoundException;
use Broadway\Repository\Repository;

/**
 * Naive initial implementation of an event sourced aggregate repository.
 */
class EventSourcingRepository implements Repository
{
    private $eventStore;
    private $eventBus;
    private $aggregateClass;
    private $eventStreamDecorators = [];
    private $aggregateFactory;
    private $snapshotStore;

    /**
     * @param EventStore $eventStore
     * @param EventBus $eventBus
     * @param string $aggregateClass
     * @param AggregateFactory $aggregateFactory
     * @param EventStreamDecorator[] $eventStreamDecorators
     * @param SnapshotStoreInterface $snapshotStore
     */
    public function __construct(
        EventStore $eventStore,
        EventBus $eventBus,
        string $aggregateClass,
        AggregateFactory $aggregateFactory,
        array $eventStreamDecorators = [],
        SnapshotStoreInterface $snapshotStore
    ) {
        $this->assertExtendsEventSourcedAggregateRoot($aggregateClass);

        $this->eventStore = $eventStore;
        $this->eventBus = $eventBus;
        $this->aggregateClass = $aggregateClass;
        $this->aggregateFactory = $aggregateFactory;
        $this->eventStreamDecorators = $eventStreamDecorators;
        $this->snapshotStore         = $snapshotStore;
    }

    /**
     * {@inheritdoc}
     */
    public function load($id): AggregateRoot
    {
        $playhead = -1;
        $snapshot = null;
        try {
            $snapshot = $this->snapshotStore->loadLast($id);
            if ($snapshot !== null) {
                $playhead = $snapshot->getPlayhead();
            }
            $domainEventStream = $this->eventStore->load($id, $playhead +1);

            return $this->aggregateFactory->create($this->aggregateClass, $domainEventStream, $snapshot);
        } catch (EventStreamNotFoundException $e) {
            throw AggregateNotFoundException::create($id, $e);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function save(AggregateRoot $aggregate)
    {
        // maybe we can get generics one day.... ;)
        Assert::isInstanceOf($aggregate, $this->aggregateClass);

        $domainEventStream = $aggregate->getUncommittedEvents();
        $eventStream = $this->decorateForWrite($aggregate, $domainEventStream);
        $this->eventStore->append($aggregate->getAggregateRootId(), $eventStream);
        $this->eventBus->publish($eventStream);
    }

    private function decorateForWrite(AggregateRoot $aggregate, DomainEventStream $eventStream): DomainEventStream
    {
        $aggregateType = $this->getType();
        $aggregateIdentifier = $aggregate->getAggregateRootId();

        foreach ($this->eventStreamDecorators as $eventStreamDecorator) {
            $eventStream = $eventStreamDecorator->decorateForWrite($aggregateType, $aggregateIdentifier, $eventStream);
        }

        return $eventStream;
    }

    private function assertExtendsEventSourcedAggregateRoot(string $class)
    {
        Assert::subclassOf(
            $class,
            EventSourcedAggregateRoot::class,
            sprintf("Class '%s' is not an EventSourcedAggregateRoot.", $class)
        );
    }

    private function getType(): string
    {
        return $this->aggregateClass;
    }
}
