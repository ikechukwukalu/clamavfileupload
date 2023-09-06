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
    public function deleteMultiple(array $ids, null|string $ref = null): bool
    {
        QueuedDeleteMultiple::dispatch($ids, $ref);
        return true;
    }

    /**
     * Soft delete single file from database by ref and id.
     *
     * @param string $ref
     * @param int|string $id
     * @return bool
     */
    public function deleteOne(int|string $id, null|string $ref = null): bool
    {
        QueuedDeleteOne::dispatch($id, $ref);
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
    public function forceDeleteMultiple(array $ids, null|string $ref = null): bool
    {
        QueuedForceDeleteMultiple::dispatch($ids, $ref);
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
    public function forceDeleteOne(int|string $id, null|string $ref = null): bool
    {
        QueuedForceDeleteOne::dispatch($id, $ref);
        return true;
    }
}
