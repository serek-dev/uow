<?php


namespace Stwarog\Uow;


interface EntityInterface extends TouchAble, HasIdStrategy, PersistAble
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

    public function set(string $field, $value);
}
