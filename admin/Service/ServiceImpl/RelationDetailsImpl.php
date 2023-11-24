<?php

use Model\RelationDetails;
use \Model\Relation;
use \Model\Table;
use \Service\RelationDetailsRepository;

class RelationDetailsImpl implements RelationDetailsRepository
{
    protected RelationDetailsRepository $relationDetailsRepository;

    public function __construct(RelationDetailsRepository $relationDetailsRepository)
    {
        $this->relationDetailsRepository = $relationDetailsRepository;
    }

    public function find($id): RelationDetails
    {
        return $this->relationDetailsRepository->find($id);
    }

    public function findAll(): array
    {
        return $this->relationDetailsRepository->findAll();
    }

    /**
     * findBy
     *
     * @param array criteria
     *
     * @return array
     */
    public function findBy(array $criteria): array
    {
        return $this->relationDetailsRepository->findBy($criteria);
    }

    public function findByTable(Table $table)
    {
        return $this->relationDetailsRepository->findByTable($table);
    }

    public function findByOtherTable($otherTable)
    {
        return $this->relationDetailsRepository->findByOtherTable($otherTable);
    }

    public function findByRelation(Relation $relation)
    {
        return $this->relationDetailsRepository->findByRelation($relation);
    }

}
