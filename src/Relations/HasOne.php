<?php declare(strict_types=1);


namespace Stwarog\Uow\Relations;

/*
    Copyright (c) 2020 Sebastian Twaróg <contact@stwarog.com>

    Permission is hereby granted, free of charge, to any person obtaining
    a copy of this software and associated documentation files (the
    "Software"), to deal in the Software without restriction, including
    without limitation the rights to use, copy, modify, merge, publish,
    distribute, sublicense, and/or sell copies of the Software, and to
    permit persons to whom the Software is furnished to do so, subject to
    the following conditions:

    The above copyright notice and this permission notice shall be
    included in all copies or substantial portions of the Software.

    THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND,
    EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF
    MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND
    NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE
    LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION
    OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION
    WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
*/

use InvalidArgumentException;
use Stwarog\Uow\EntityInterface;
use Stwarog\Uow\EntityManagerInterface;
use Stwarog\Uow\Shared\AbstractOneToOneRelation;

class HasOne extends AbstractOneToOneRelation implements InteractWithEntityManager, HasRelationFromToSchema
{
    public function handleRelations(EntityManagerInterface $entityManager, EntityInterface $parentEntity): void
    {
        if ($this->isEmpty()) {
            return;
        }

        $parentEntity->addPostPersist(
            function (EntityInterface $parentEntity) use ($entityManager) {
                $relatedEntity = $this->relatedEntity;
                # todo: refactor! it doesn't have to be a valid related object
                # add inversion key checking
                /** @var BelongsTo[] $matchingRelatedEntityRelations */
                $relationData                   = $relatedEntity->relations()->toArray();
                $matchingRelatedEntityRelations = array_filter(
                    $relationData,
                    function (RelationInterface $relatedRelation) {
                        return $relatedRelation instanceof BelongsTo;
                    }
                );

                if (empty($matchingRelatedEntityRelations)) {
                    throw new InvalidArgumentException(
                        sprintf('No BelongsTo inversion of HasOne has been found in %s.', get_class($relatedEntity->originalClass()))
                    );
                }

                $belongsToRelation = reset($matchingRelatedEntityRelations);

                $relatedEntity->set($belongsToRelation->keyFrom(), $parentEntity->get($this->keyFrom()));
                $entityManager->persist($relatedEntity);
            }
        );
    }
}
