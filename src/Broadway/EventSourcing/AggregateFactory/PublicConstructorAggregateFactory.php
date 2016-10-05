<?php

namespace Broadway\EventSourcing\AggregateFactory;

use Broadway\Domain\DomainEventStreamInterface;
use Broadway\Snapshot\SnapshotMessage;
use Broadway\Snapshot\SnapshotMessageInterface;

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
    public function create($aggregateClass, DomainEventStreamInterface $domainEventStream, SnapshotMessage $snapshotMessage = null)
    {
        $aggregate = new $aggregateClass();
        $aggregate->initializeState($domainEventStream, $snapshotMessage);

        return $aggregate;
    }
}
