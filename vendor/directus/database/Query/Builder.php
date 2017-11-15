<?php

namespace Directus\Database\Query;

use Directus\Database\Query\Relations\ManyToManyRelation;
use Directus\Database\Query\Relations\ManyToOneRelation;
use Directus\Database\Query\Relations\OneToManyRelation;
use Directus\Util\ArrayUtils;
use Zend\Db\Adapter\AdapterInterface;
use Zend\Db\ResultSet\AbstractResultSet;
use Zend\Db\ResultSet\ResultSet;
use Zend\Db\Sql\Having;
use Zend\Db\Sql\Predicate\Between;
use Zend\Db\Sql\Predicate\Expression;
use Zend\Db\Sql\Predicate\In;
use Zend\Db\Sql\Predicate\IsNotNull;
use Zend\Db\Sql\Predicate\IsNull;
use Zend\Db\Sql\Predicate\Like;
use Zend\Db\Sql\Predicate\NotBetween;
use Zend\Db\Sql\Predicate\NotIn;
use Zend\Db\Sql\Predicate\NotLike;
use Zend\Db\Sql\Predicate\Operator;
use Zend\Db\Sql\Sql;

class Builder
{
    /**
     * @var AdapterInterface
     */
    protected $connection;

    /**
     * @var Sql
     */
    protected $sql = null;

    /**
     * @var array
     */
    protected $columns = ['*'];

    /**
     * @var string|null
     */
    protected $from;

    /**
     * @var array|null
     */
    protected $joins;

    /**
     * @var array
     */
    protected $wheres = [];

    /**
     * @var array
     */
    protected $order = [];

    /**
     * @var null|int
     */
    protected $offset = null;

    /**
     * @var null|int
     */
    protected $limit = null;

    /**
     * @var null|array
     */
    protected $groupBys = null;

    /**
     * @var null|array
     */
    protected $havings = null;

    /**
     * Builder constructor.
     *
     * @param AdapterInterface $connection
     */
    public function __construct(AdapterInterface $connection)
    {
        $this->connection = $connection;
    }

    /**
     * Sets the columns list to be selected
     *
     * @param array $columns
     *
     * @return $this
     */
    public function columns(array $columns)
    {
        $this->columns = $columns;

        return $this;
    }

    /**
     * Gets the selected columns
     *
     * @return array
     */
    public function getColumns()
    {
        return $this->columns;
    }

    /**
     * Sets the from table
     *
     * @param $from
     *
     * @return $this
     */
    public function from($from)
    {
        $this->from = $from;

        return $this;
    }

    /**
     * Gets the from table value
     *
     * @return null|string
     */
    public function getFrom()
    {
        return $this->from;
    }

    public function join($table, $on, $columns = ['*'], $type = 'inner')
    {
        $this->joins[] = compact('table', 'on', 'columns', 'type');

        return $this;
    }

    public function getJoins()
    {
        return $this->joins;
    }

    /**
     * Sets a condition to the query
     *
     * @param $column
     * @param $operator
     * @param $value
     * @param $not
     * @param $logical
     *
     * @return $this
     */
    public function where($column, $operator = null, $value = null, $not = false, $logical = 'and')
    {
        if ($column instanceof \Closure) {
            return $this->nestWhere($column, $logical);
        }

        $type = 'basic';

        $this->wheres[] = compact('type', 'operator', 'column', 'value', 'not', 'logical');

        return $this;
    }

    /**
     * Creates a nested condition
     *
     * @param \Closure $callback
     * @param string $logical
     *
     * @return $this
     */
    public function nestWhere(\Closure $callback, $logical = 'and')
    {
        $query = $this->newQuery();
        call_user_func($callback, $query);

        if (count($query->getWheres())) {
            $type = 'nest';
            $this->wheres[] = compact('type', 'query', 'logical');
        }

        return $this;
    }

    public function nestOrWhere(\Closure $callback)
    {
        return $this->nestWhere($callback, 'or');
    }

    /**
     * Create a condition where the given column is empty (NULL or empty string)
     *
     * @param $column
     *
     * @return Builder
     */
    public function whereEmpty($column)
    {
        return $this->nestWhere(function(Builder $query) use ($column) {
            $query->whereNull($column);
            $query->whereEqualTo($column, '');
        }, 'or');
    }

    /**
     * Create a condition where the given column is NOT empty (NULL or empty string)
     *
     * @param $column
     *
     * @return Builder
     */
    public function whereNotEmpty($column)
    {
        return $this->nestWhere(function(Builder $query) use ($column) {
            $query->whereNotNull($column);
            $query->whereNotEqualTo($column, '');
        }, 'and');
    }

    /**
     * Sets a "where in" condition
     *
     * @param $column
     * @param array|Builder $values
     * @param bool $not
     * @param string $logical
     *
     * @return Builder
     */
    public function whereIn($column, $values, $not = false, $logical = 'and')
    {
        return $this->where($column, 'in', $values, $not, $logical);
    }

    /**
     * Sets a "where or in" condition
     *
     * @param $column
     * @param array|Builder $values
     * @param bool $not
     *
     * @return Builder
     */
    public function orWhereIn($column, $values, $not = false)
    {
        return $this->where($column, 'in', $values, $not, 'or');
    }

    /**
     * Sets an "where not in" condition
     *
     * @param $column
     * @param array $values
     *
     * @return Builder
     */
    public function whereNotIn($column, array $values)
    {
        return $this->whereIn($column, $values, true);
    }

    public function whereBetween($column, array $values, $not = false)
    {
        return $this->where($column, 'between', $values, $not);
    }

    public function whereNotBetween($column, array $values)
    {
        return $this->whereBetween($column, $values, true);
    }

    public function whereEqualTo($column, $value, $not = false, $logical = 'and')
    {
        return $this->where($column, '=', $value, $not, $logical);
    }

    public function orWhereEqualTo($column, $value, $not = false)
    {
        return $this->where($column, '=', $value, $not, 'or');
    }

    public function whereNotEqualTo($column, $value)
    {
        return $this->whereEqualTo($column, $value, true);
    }

    public function whereLessThan($column, $value)
    {
        return $this->where($column, '<', $value);
    }

    public function whereLessThanOrEqual($column, $value)
    {
        return $this->where($column, '<=', $value);
    }

    public function whereGreaterThan($column, $value)
    {
        return $this->where($column, '>', $value);
    }

    public function whereGreaterThanOrEqual($column, $value)
    {
        return $this->where($column, '>=', $value);
    }

    public function whereNull($column, $not = false)
    {
        return $this->where($column, 'null', null, $not);
    }

    public function whereNotNull($column)
    {
        return $this->whereNull($column, true);
    }

    public function whereLike($column, $value, $not = false, $logical = 'and')
    {
        return $this->where($column, 'like', $value, $not, $logical);
    }

    public function orWhereLike($column, $value, $not = false)
    {
        return $this->whereLike($column, $value, $not, 'or');
    }

    public function whereNotLike($column, $value)
    {
        return $this->whereLike($column, $value, true);
    }

    public function whereAll($column, $table, $columnLeft, $columnRight, $values)
    {
        if ($columnLeft === null) {
            $relation = new OneToManyRelation($this, $column, $table, $columnRight, $this->getFrom());
        } else {
            $relation = new ManyToManyRelation($this, $table, $columnLeft, $columnRight);
        }

        $relation->all($values);

        return $this->whereIn($column, $relation);
    }

    public function whereHas($column, $table, $columnLeft, $columnRight, $count = 1)
    {
        if (is_null($columnLeft)) {
            $relation = new OneToManyRelation($this, $column, $table, $columnRight, $this->getFrom());
        } else {
            $relation = new ManyToManyRelation($this, $table, $columnLeft, $columnRight);
        }

        $relation->has($count);

        return $this->whereIn($column, $relation);
    }

    public function whereRelational($column, $table, $columnLeft, $columnRight = null, \Closure $callback = null, $logical = 'and')
    {
        if (is_callable($columnRight)) {
            // $column: Relational Column
            // $table: Related table
            // $columnRight: Related table that points to $column
            $callback = $columnRight;
            $columnRight = $columnLeft;
            $columnLeft = null;
            $relation = new ManyToOneRelation($this, $columnRight, $table);
        } else if (is_null($columnLeft)) {
            $relation = new OneToManyRelation($this, $column, $table, $columnRight, $this->getFrom());
        } else {
            $relation = new ManyToManyRelation($this, $table, $columnLeft, $columnRight);
        }

        call_user_func($callback, $relation);

        return $this->whereIn($column, $relation, false, $logical);
    }

    public function orWhereRelational($column, $table, $columnLeft, $columnRight = null, \Closure $callback = null)
    {
        return $this->whereRelational($column, $table, $columnLeft, $columnRight, $callback, 'or');
    }

    /**
     * Gets the query conditions
     *
     * @return array
     */
    public function getWheres()
    {
        return $this->wheres;
    }

    /**
     * Order the query by the given table
     *
     * @param $column
     * @param string $direction
     *
     * @return Builder
     */
    public function orderBy($column, $direction = 'ASC')
    {
        $this->order[(string) $column] = (string) $direction;

        return $this;
    }

    /**
     * Gets the sorts
     *
     * @return array
     */
    public function getOrder()
    {
        return $this->order;
    }

    /**
     * Clears the order list
     */
    public function clearOrder()
    {
        $this->order = [];
    }

    /**
     * Sets the number of records to skip
     *
     * @param $value
     *
     * @return Builder
     */
    public function offset($value)
    {
        $this->offset = max(0, $value);

        return $this;
    }

    /**
     * Alias of Builder::offset
     *
     * @param $value
     *
     * @return Builder
     */
    public function skip($value)
    {
        return $this->offset($value);
    }

    /**
     * Gets the query offset
     *
     * @return int|null
     */
    public function getOffset()
    {
        return $this->offset;
    }

    /**
     * Alias of Builder::getOffset
     *
     * @return int|null
     */
    public function getSkip()
    {
        return $this->getOffset();
    }

    /**
     * Sets the query result limit
     *
     * @param $value
     *
     * @return $this
     */
    public function limit($value)
    {
        // =============================================================================
        // LIMIT 0 quickly returns an empty set.
        // This can be useful for checking the validity of a query.
        // @see: http://dev.mysql.com/doc/refman/5.7/en/limit-optimization.html
        // =============================================================================
        if ($value >= 0) {
            $this->limit = (int) $value;
        } else {
            $this->limit = null;
        }

        return $this;
    }

    /**
     * Gets the query result limit
     *
     * @return int|null
     */
    public function getLimit()
    {
        return $this->limit;
    }

    /**
     * Sets Group by columns
     *
     * @param array|string $columns
     */
    public function groupBy($columns)
    {
        if (!is_array($columns)) {
            $columns = [$columns];
        }

        if ($this->groupBys === null) {
            $this->groupBys = [];
        }

        foreach($columns as $column) {
            $this->groupBys[] = $column;
        }
    }

    /**
     * Sets having
     *
     * @param $column
     * @param $operator
     * @param $value
     *
     * @return $this
     */
    public function having($column, $operator = null, $value = null)
    {
        $this->havings[] = compact('column', 'operator', 'value');

        return $this;
    }

    /**
     * Gets havings
     *
     * @return array|null
     */
    public function getHavings()
    {
        return $this->havings;
    }

    /**
     * Build the Select Object
     *
     * @return \Zend\Db\Sql\Select
     */
    public function buildSelect()
    {
        $select = $this->getSqlObject()->select($this->getFrom());
        $select->columns($this->getColumns());
        $select->order($this->buildOrder());

        if ($this->getJoins() !== null) {
            foreach($this->getJoins() as $join) {
                $select->join($join['table'], $join['on'], $join['columns'], $join['type']);
            }
        }

        if ($this->getOffset() !== null && $this->getLimit() !== null) {
            $select->offset($this->getOffset());
        }

        if ($this->getLimit() !== null) {
            $select->limit($this->getLimit());
        }

        foreach($this->getWheres() as $condition) {
            $logical = strtoupper(ArrayUtils::get($condition, 'logical', 'and'));
            if (ArrayUtils::get($condition, 'type') === 'nest') {
                $query = ArrayUtils::get($condition, 'query');
                if ($logical === 'OR') {
                    $select->where->or;
                }

                $where = $select->where->nest();
                // @NOTE: only allow one level nested
                foreach($query->getWheres() as $nestCondition) {
                    if (ArrayUtils::get($nestCondition, 'logical') == 'or') {
                        $whereLogical = $where::OP_OR;
                    } else {
                        $whereLogical = $where::OP_AND;
                    }

                    $where->addPredicate($this->buildConditionExpression($nestCondition), $whereLogical);
                    $condition = null;
                }
                $where->unnest();
            } else {
                $select->where($this->buildConditionExpression($condition), $logical);
            }
        }

        if ($this->groupBys !== null) {
            $groupBys = [];

            foreach ($this->groupBys as $groupBy) {
                $groupBys[] = $this->getIdentifier($groupBy);
            }

            $select->group($groupBys);
        }

        if ($this->getHavings() !== null) {
            foreach ($this->getHavings() as $having) {
                if ($having['column'] instanceof Expression) {
                    $expression = $having['column'];
                    $callback = function(Having $having) use ($expression) {
                        $having->addPredicate($expression);
                    };
                } else {
                    $callback = function(Having $havingObject) use ($having) {
                        $havingObject->addPredicate(new Operator($having['column'], $having['operator'], $having['value']));
                    };
                }

                $select->having($callback);
            }
        }

        return $select;
    }

    /**
     * Executes the query
     *
     * @return AbstractResultSet
     */
    public function get()
    {
        $sql = $this->getSqlObject();
        $select = $this->buildSelect();

        $statement = $sql->prepareStatementForSqlObject($select);
        $result = $statement->execute();

        $resultSet = new ResultSet($result);
        $resultSet->initialize($result);

        return $resultSet;
    }

    /**
     * Gets the query string
     *
     * @return string
     */
    public function getSql()
    {
        $sql = $this->getSqlObject();
        $select = $this->buildSelect();

        return $sql->getSqlStringForSqlObject($select, $this->connection->getPlatform());
    }

    protected function buildOrder()
    {
        $order = [];
        foreach($this->getOrder() as $orderBy => $orderDirection) {
            $order[] = sprintf('%s %s', $this->getIdentifier($orderBy), $orderDirection);
        }

        return $order;
    }

    /**
     * Get the column identifier (table name prepended)
     *
     * @param string $column
     *
     * @return string
     */
    protected function getIdentifier($column)
    {
        $platform = $this->getConnection()->getPlatform();
        $table = $this->getFrom();

        if (strpos($column, $platform->getIdentifierSeparator()) === false) {
            $column = implode($platform->getIdentifierSeparator(), [$table, $column]);
        }

        return $column;
    }

    protected function buildConditionExpression($condition)
    {
        $not = ArrayUtils::get($condition, 'not', false) === true;
        $notChar = '';
        if ($not === true) {
            $notChar = $condition['operator'] === '=' ? '!' : 'n';
        }

        $operator = $notChar . $condition['operator'];

        $column = $condition['column'];
        $identifier = $this->getIdentifier($column);
        $value = $condition['value'];

        if ($value instanceof Builder) {
            $value = $value->buildSelect();
        }

        switch ($operator) {
            case 'in':
                $expression = new In($identifier, $value);
                break;
            case 'nin':
                $expression = new NotIn($identifier, $value);
                break;
            case 'like':
                $value = "%$value%";
                $expression = new Like($identifier, $value);
                break;
            case 'nlike':
                $value = "%$value%";
                $expression = new NotLike($identifier, $value);
                break;
            case 'null':
                $expression = new IsNull($identifier);
                break;
            case 'nnull':
                $expression = new IsNotNull($identifier);
                break;
            case 'between':
                $expression = new Between($identifier, array_shift($value), array_pop($value));
                break;
            case 'nbetween':
                $expression = new  NotBetween($identifier, array_shift($value), array_pop($value));
                break;
            default:
                $expression = new Operator($identifier, $operator, $value);
        }

        return $expression;
    }

    protected function getSqlObject()
    {
        if ($this->sql === null) {
            $this->sql = new Sql($this->connection);
        }

        return $this->sql;
    }

    /**
     * Gets a new instance of the query builder
     *
     * @return \Directus\Database\Query\Builder
     */
    public function newQuery()
    {
        return new self($this->connection);
    }

    /**
     * Gets the connection
     *
     * @return AdapterInterface
     */
    public function getConnection()
    {
        return $this->connection;
    }
}
