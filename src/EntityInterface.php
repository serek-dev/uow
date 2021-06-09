<?php
declare(strict_types=1);


namespace Stwarog\Uow;

use Stwarog\Uow\IdGenerators\HasIdStrategy;
use Stwarog\Uow\UnitOfWork\PersistAble;

interface EntityInterface extends TouchAble, HasIdStrategy, PersistAble, HasPostActions
{
    /**
     * Generate ID by provided strategy and assign it to it self.
     *
     * @param DBConnectionInterface $db
     */
    public function generateIdValue(DBConnectionInterface $db): void;

    public function relations(): RelationBag;

    public function setId(string $id): void;

    public function get(string $field);

    public function set(string $field, $value): void;
}
