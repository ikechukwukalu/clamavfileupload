<?php

namespace Ikechukwukalu\Clamavfileupload\Trait;

use Ikechukwukalu\Clamavfileupload\Events\QueuedDeleteAll;
use Ikechukwukalu\Clamavfileupload\Events\QueuedDeleteMultiple;
use Ikechukwukalu\Clamavfileupload\Events\QueuedDeleteOne;
use Ikechukwukalu\Clamavfileupload\Events\QueuedForceDeleteAll;
use Ikechukwukalu\Clamavfileupload\Events\QueuedForceDeleteMultiple;
use Ikechukwukalu\Clamavfileupload\Events\QueuedForceDeleteOne;

trait QueuedDelete {

    /**
     * Soft delete all files from database by ref.
     *
     * @param string $ref
     * @return bool
     */
    public function deleteAll(string $ref): bool
    {
        QueuedDeleteAll::dispatch($ref);
        return true;
    }

    /**
     * Soft delete multiple files from database by ref and Ids.
     *
     * @param string $ref
     * @param array $ids
     * @return bool
     */
    public function deleteMultiple(string $ref, array $ids): bool
    {
        QueuedDeleteMultiple::dispatch($ref, $ids);
        return true;
    }

    /**
     * Soft delete single file from database by ref and id.
     *
     * @param string $ref
     * @param int|string $id
     * @return bool
     */
    public function deleteOne(string $ref, int|string $id): bool
    {
        QueuedDeleteOne::dispatch($ref, $id);
        return true;
    }

    /**
     * Permanently delete all files from directory and database by ref.
     *
     * @param string $ref
     * @return bool
     */
    public function forceDeleteAll(string $ref): bool
    {
        QueuedForceDeleteAll::dispatch($ref);
        return true;
    }

    /**
     * Permanently delete multiple files from directory
     * and database by ref and Ids.
     *
     * @param string $ref
     * @param array $ids
     * @return bool
     */
    public function forceDeleteMultiple(string $ref, array $ids): bool
    {
        QueuedForceDeleteMultiple::dispatch($ref, $ids);
        return true;
    }

    /**
     * Permanently delete single file from directory
     * and database by ref and id.
     *
     * @param string $ref
     * @param int|string $id
     * @return bool
     */
    public function forceDeleteOne(string $ref, int|string $id): bool
    {
        QueuedForceDeleteOne::dispatch($ref, $id);
        return true;
    }
}
