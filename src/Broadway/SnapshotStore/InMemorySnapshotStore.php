<?php
namespace Broadway\SnapshotStore;


use Broadway\Snapshot\SnapshotMessage;

class InMemorySnapshotStore implements SnapshotStoreInterface
{
    private $snapshots;

    public function loadLast($aggregateId)
    {
        return is_array($this->snapshots[$aggregateId]) ? end($this->snapshots[$aggregateId]) : null;
    }

    public function append($aggregateId, SnapshotMessage $snapshotMessage)
    {
        $this->snapshots[$aggregateId][] = $snapshotMessage;
    }
}
