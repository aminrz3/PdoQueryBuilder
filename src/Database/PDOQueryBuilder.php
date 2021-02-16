<?php


namespace App\Database;

use App\Contracts\DatabaseConnectionInterface;
use PDO;

class PDOQueryBuilder
{
    protected $table;
    protected $connection;
    protected $Conditions;
    protected $Values;
    protected $statement;

    public function __construct(DatabaseConnectionInterface $connection)
    {
        $this->connection = $connection->getConnection();
    }

    public function table($table)
    {
        $this->table = $table;
        return $this;
    }

    public function insert(array $data)
    {
        $placeHolder = [];
        foreach ($data as $column => $value) {
            $placeHolder[] = "?";
        }

        $fields = implode(',', array_keys($data));
        $placeHolder = implode(",", $placeHolder);
        $query = "INSERT INTO {$this->table} ({$fields}) VALUES ({$placeHolder})";

        $this->execute($query, array_values($data));

        return (int)$this->connection->lastInsertId();
    }

    public function update(array $data)
    {
        $fields = [];
        foreach ($data as $column => $value) {
            $fields[] = "{$column}='{$value}'";
        }

        $fields = implode(',', $fields);

        if (is_null($this->Conditions)) {
            $sql = "UPDATE {$this->table} SET {$fields}";
        } else {
            $sql = "UPDATE {$this->table} SET {$fields}{$this->Conditions}";
        }

        if (empty($this->Values))
            $this->execute($sql);
        else
            $this->execute($sql, $this->Values);

        return $this->statement->rowCount();
    }

    public function delete()
    {
        $sql = "DELETE FROM {$this->table}{$this->Conditions}";

        if (empty($this->Values))
            $this->execute($sql);
        else
            $this->execute($sql, $this->Values);

        return $this->statement->rowCount();
    }

    public function where(array $data)
    {

        $this->Values = [];

        $conditions = array_diff_key($data, array_flip(
            ['GROUP', 'ORDER', 'HAVING', 'LIMIT', 'LIKE', 'MATCH']
        ));


        if (!empty($conditions)) {
            $this->execImplode($conditions, "AND");
            $this->Conditions = substr_replace($this->Conditions, ' WHERE ', 0, 0);
        }


        if (isset($data['MATCH'])) {
            $MATCH = $data['MATCH'];

            if (is_array($MATCH) && isset($MATCH['columns'], $MATCH['key'])) {
                $op = '';

                $options_array = [
                    'natural' => 'IN NATURAL LANGUAGE MODE',
                    'naturalANDquery' => 'IN NATURAL LANGUAGE MODE WITH QUERY EXPANSION',
                    'boolean' => 'IN BOOLEAN MODE',
                    'query' => 'WITH QUERY EXPANSION'
                ];

                if (isset($MATCH['option'], $options_array[$MATCH['option']])) {
                    $op = ' ' . $options_array[$MATCH['option']];
                }

                if (empty($op)) $op = $options_array['natural'];

                $columns = implode($MATCH['columns'], ',');

                $this->Conditions .= ($this->Conditions == '' ? ' WHERE ' : ' AND ') . 'MATCH(' . $columns . ') AGAINST(?' . $op . ')';

                $this->Values[] = $MATCH['key'];
            }
        }


        if (isset($data['GROUP'])) {
            $GROUP = $data['GROUP'];

            if (is_array($GROUP)) {
                $this->Conditions .= ' GROUP BY ' . implode($GROUP, ',');
            } else {
                $this->Conditions .= ' GROUP BY ' . $GROUP;
            }


            if (isset($data['HAVING'])) {
                $this->execImplode($data['HAVING'], "AND");
            }
        }


        if (isset($data['ORDER'])) {
            $ORDER = $data['ORDER'];

            if (is_array($ORDER)) {

                $condition = [];

                foreach ($ORDER as $column => $value) {
                    if (is_array($value)) {
                        $condition[] = 'FIELD(' . $column . ',' . $this->arrayQuote($value) . ')';
                    } elseif ($value == "ASC" || $value == "DESC") {
                        $condition[] = $column . ' ' . $value;
                    } elseif (is_int($value)) {
                        $condition[] = $column;
                    }
                }

                $this->Conditions .= ' ORDER BY ' . implode($condition, ',');

            }
        }

        if (isset($data['LIMIT'])) {
            $LIMIT = $data['LIMIT'];

            if (is_numeric($LIMIT)) {
                $LIMIT = [0, $LIMIT];
            }

            if (is_array($LIMIT) && is_numeric($LIMIT[0]) && is_numeric($LIMIT[1])) {
                $this->Conditions .= ' LIMIT ' . $LIMIT[1] . ' OFFSET ' . $LIMIT[0];
            }
        }


        return $this;
    }

    private function execImplode(array $data, string $struct)
    {
        $conditions = [];
        foreach ($data as $key => $value) {
            $type = gettype($value);

            if ($type == "array" && preg_match("/^(AND|OR)(\s+#.*)?$/", $key, $relation_match)) {
                $relationship = $relation_match[1];

                $conditions[] = $value != array_keys(array_keys($value)) ?
                    '(' . $this->execImplode($value, ' ' . $relationship) . ')' :
                    '(' . $this->execInnerStruct($value, ' ' . $relationship, $struct) . ')';

                continue;
            }

            preg_match('/([a-zA-Z0-9_\.]+)(\[(?<operator>\>\=?|\<\=?|\!|\<\>|\>\<|\!?~|REGEXP)\])?/i', $key, $match);
            if (isset($match['operator'])) {
                $key = $match[1];
                $AllOperators = ['>', '>=', '<', '<=', '!', '<>', '<=>'];
                if (in_array($match['operator'], $AllOperators)) {
                    strpos($match['operator'], '=') ? $equal = "" : $equal = "=";
                    $conditions[] = $key . $match['operator'] . $equal . "?";
                } else if ($match['operator'] == '~') {
                    if ($type == "array") {
                        $LikeConditions = [];
                        $arrayValues = $value;
                        $i = 0;
                        foreach ($arrayValues as $keyValue => $arrayValue) {
                            if (is_array($arrayValue) && preg_match("/^(AND|OR)(\s+#.*)?$/", $keyValue, $relation_match)) {
                                $LikeStruct = $relation_match[1];
                                foreach ($arrayValue as $val){
                                    $LikeConditions[] = $key . ' LIKE ? ';
                                    $this->Values[] = '%' . $val . '%';
                                }
                            } else {
                                $LikeStruct = "OR";
                                $LikeConditions[] = $key . ' LIKE ? ';
                                $this->Values[] = '%' . $arrayValue . '%';
                            }

                            $i++;
                        }
                        $conditions[] = '('.implode($LikeStruct . ' ', $LikeConditions).')';
                        $value = null;
                    } else {
                        $conditions[] = '('.$key . ' LIKE ? ) ';
                        $value = '%' . $value . '%';
                    }
                }
            } else {
                $conditions[] = "{$key}=?";
            }

            if (!empty($value))
                $this->Values[] = $value;
        }

        $this->Conditions = implode($struct . ' ', $conditions);

        return $this->Conditions;
    }

    private function execInnerStruct(array $data, string $struct, string $outerStruct)
    {

        $Condition = [];
        foreach ($data as $key => $value) {
            $Condition[] = '(' . $this->execImplode($value, $struct) . ')';
        }

        return implode($outerStruct . ' ', $Condition);
    }


    private function arrayQuote(array $data)
    {
        $condition = [];

        foreach ($data as $value) {
            $condition[] = is_int($value) ? $value : $this->connection->quote($value);
        }

        return implode($condition, ',');
    }


    public function get(array $columns = ['*'])
    {
        $columns = implode(',', $columns);

        if (is_null($this->Conditions)) {
            $sql = "SELECT {$columns} FROM {$this->table}";
            $this->execute($sql);
        } else {
            $sql = "SELECT {$columns} FROM {$this->table}{$this->Conditions}";
            if (empty($this->Values)) {
                $this->execute($sql);
            } else {
                $this->execute($sql, $this->Values);
            }
        }

        return $this->statement->fetchAll();
    }

    public function first(array $columns = ['*'])
    {
        $data = $this->get($columns);

        return empty($data) ? null : $data[0];
    }

    public function find(Int $id)
    {
        return $this->where(['id' => $id])->first();
    }

    public function findby(array $by)
    {
        return $this->where($by)->get();
    }

    public function truncateAllTable()
    {
        $query = $this->connection->prepare("SHOW TABLES");
        $query->execute();

        foreach ($query->fetchAll(PDO::FETCH_COLUMN) as $table) {
            $this->connection->prepare("TRUNCATE TABLE `{$table}`")->execute();
        }
    }

    private function execute(string $sql, array $values = null)
    {

        $this->statement = $this->connection->prepare($sql);

        $this->statement->execute($values);

        return $this;
    }

    public function beginTransaction()
    {
        $this->connection->beginTransaction();
    }

    public function rollback()
    {
        $this->connection->rollback();
    }

}