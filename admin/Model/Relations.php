<?php namespace Model;

class Relations
{
    //private Table $table;
    private $relationDetails = [];

    /* @return Table
     */
/*     public function getTable(): Table
    {
        return $this->table;
    }
 */
    /**
     * @param Table $table
     */
/*     public function setTable(Table $table): void
    {
        $this->table = $table;
    }
 */
    public function getRelationDetails()
    {
        return $this->relationDetails;
    }

    /**
     * @param $relations
     */
    public function setRelationDetails($relations)
    {
/*         foreach ($relations as $key => $relation) {
            $this->relationDetails[$key] = $relation;
        }
 */
    array_push($this->relationDetails, $relations);
    }
}
