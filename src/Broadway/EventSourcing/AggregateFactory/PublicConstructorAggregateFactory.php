<?php

namespace Broadway\EventSourcing\AggregateFactory;

use Broadway\Domain\DomainEventStreamInterface;
use Broadway\Snapshot\SnapshotInterface;

/**
 * Creates aggregates by instantiating the aggregateClass and then
 * passing a DomainEventStream to the public initializeState() method.
 * E.g. (new \Vendor\AggregateRoot)->initializeState($domainEventStream);
 */
class PublicConstructorAggregateFactory implements AggregateFactoryInterface
{
    /**
     * {@inheritDoc}
     */
    public function create($aggregateClass, DomainEventStreamInterface $domainEventStream, SnapshotInterface $snapshot = null)
    {
        $aggregate = new $aggregateClass();
        $aggregate->initializeState($domainEventStream);

        return $aggregate;
    }
}
