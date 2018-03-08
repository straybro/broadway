<?php

/*
 * This file is part of the broadway/broadway package.
 *
 * (c) Qandidate.com <opensource@qandidate.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Broadway\SnapshotStore;


use Broadway\Snapshot\SnapshotMessage;

interface SnapshotStoreInterface
{
    /**
     * @param $aggregateId
     * @return SnapshotMessage
     */
    public function loadLast($aggregateId);

    public function append($aggregateId, SnapshotMessage $snapshotMessage);
}
