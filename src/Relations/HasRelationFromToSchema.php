<?php


namespace Stwarog\Uow\Relations;


interface HasRelationFromToSchema
{
    public function keyFrom(): string;

    public function tableTo(): string;

    public function keyTo(): string;
}
