<?php 
/*
 * @Author: error: error: git config user.name & please set dead value or install git && error: git config user.email & please set dead value or install git & please set dead value or install git
 * @Date: 2024-03-27 15:14:15
 * @LastEditors: findnr
 * @LastEditTime: 2024-05-27 16:16:15
 * @FilePath: \swoole_http_api_xiehui\app\Mysql.php
 * @Description: 这是默认设置,请设置`customMade`, 打开koroFileHeader查看配置 进行设置: https://github.com/OBKoro1/koro1FileHeader/wiki/%E9%85%8D%E7%BD%AE
 */

declare(strict_types=1);

namespace app;

class Mysql
{
    //数据库对象
    private $obj;
    //数据名称
    protected $database;
    //表名称
    protected $table;
    //字段名称
    protected $fields = ['*'];
    //where条件
    protected $where = [];
    //排序
    protected $order = [];
    //limit条件
    protected $limit;
    //数据
    protected $data = [];
    //组合的SQL语句
    protected $sql = '';
    //日志文件路径
    protected $logPath = '';
    /**
     * 
     */

    public function __construct($obj,$path='')
    {
        $this->obj=$obj;
        $this->logPath=$path;
    }
    /**
     * 
     */
    public function setDatabase($database)
    {
        $this->database = $database;
        return $this;
    }
    /**
     * 
     */
    public function table($table)
    {
        $this->table = $table;
        return $this;
    }
    /**
     * 
     */
    public function fields($fields)
    {
        $this->fields = is_array($fields) ? $fields : func_get_args();
        return $this;
    }
    /**
     * 
     */
    public function where($column, $value = '')
    {
        if (is_string($value)) {
            $this->where[] = "$column = '$value'";
        }
        if (is_int($value)) {
            $this->where[] = "$column = $value";
        }
        return $this;
    }
    public function whereIn($column, array $values)
    {
        $values = implode("', '", $values);
        $whereClause = "$column IN ('$values')";
        $this->where[] = $whereClause;
        return $this;
    }
    public function whereLike($column, $value)
    {
        $this->where[] = "$column LIKE '$value'";
        return $this;
    }
    public function whereOr($column, $value)
    {
        if (is_string($value)) {
            $this->where[] = "OR $column = '$value'";
        }
        if (is_int($value)) {
            $this->where[] = "OR $column = $value";
        }
        return $this;
    }
    public function order($column, $direction = 'ASC')
    {
        $this->order[] = "$column $direction";
        return $this;
    }

    public function limit()
    {
        $content = func_get_args();
        $num = func_num_args();
        $one = (int)$content[0];
        if ($num > 1) {
            $two = (int)$content[1];
            $this->limit = "$one,$two";
        } else {
            $this->limit = $one;
        }
        return $this;
    }

    public function data($data)
    {
        $this->data = $data;
        return $this;
    }
    public function select()
    {
        $fields = implode(', ', $this->fields);
        $where = implode(' AND ', $this->where);
        $order = implode(', ', $this->order);
        $database = $this->database ? "`$this->database`." : '';
        $sql = "SELECT $fields FROM $database$this->table";
        if ($where) {
            $sql .= " WHERE $where";
        }
        if ($order) {
            $sql .= " ORDER BY $order";
        }
        if ($this->limit) {
            $sql .= " LIMIT $this->limit";
        }
        $this->sql = $sql;
        if($this->obj instanceof \Swoole\Database\PDOPool){
            $data=$this->_query_data('select');
            return $data;
        }else{
            return $this;
        }
        
    }

    public function update(array $data = [])
    {
        $this->data = array_merge($this->data, $data);
        $data = [];
        array_walk($this->data, function ($v, $k) use (&$data) {
            if (is_string($v)) {
                $data[] = "$k = '$v'";
            }
            if (is_numeric($v)) {
                $data[] = "$k = $v";
            }
        });
        $set = implode(', ', $data);
        $where = implode(' AND ', $this->where);
        $database = $this->database ? "`$this->database`." : '';
        if ($where == '') {
            $sql = "UPDATE $database$this->table SET $set";
        } else {
            $sql = "UPDATE $database$this->table SET $set WHERE $where";
        }

        $this->sql = $sql;
        if($this->obj instanceof \Swoole\Database\PDOPool){
            $data=$this->_query_data();
            return $data;
        }else{
            return $this;
        }
    }
    public function delete()
    {
        $where = implode(' AND ', $this->where);
        $database = $this->database ? "`$this->database`." : '';
        $sql = "DELETE FROM $database$this->table WHERE $where";
        $this->sql = $sql;
        if($this->obj instanceof \Swoole\Database\PDOPool){
            $data=$this->_query_data();
            return $data;
        }else{
            return $this;
        }
    }
    public function insert(array $data = [])
    {
        $database = $this->database ? "`$this->database`." : '';
        $table = $this->table;
        $data = array_merge($this->data, $data);

        $columns = implode(', ', array_keys($data));
        $values = '';
        array_walk($data, function ($v) use (&$values) {
            if (is_string($v)) {
                $values .= "'$v',";
            }
            if (is_int($v)) {
                $values .= "$v,";
            }
        });
        $values = rtrim($values, ',');
        $this->sql = "INSERT INTO $database$table ($columns) VALUES ($values)";
        if($this->obj instanceof \Swoole\Database\PDOPool){
            $data=$this->_query_data();
            return $data;
        }else{
            return [];
        }
    }
    public function insertGetId(array $data = [])
    {
        $database = $this->database ? "`$this->database`." : '';
        $table = $this->table;
        $data = array_merge($this->data, $data);

        $columns = implode(', ', array_keys($data));
        $values = '';
        array_walk($data, function ($v) use (&$values) {
            if (is_string($v)) {
                $values .= "'$v',";
            }
            if (is_int($v)) {
                $values .= "$v,";
            }
        });
        $values = rtrim($values, ',');
        $this->sql = "INSERT INTO $database$table ($columns) VALUES ($values)";
        if(!($this->obj instanceof \Swoole\Database\PDOPool)) return 0;
        $pdo = $this->obj->get();
        try {
            $statement = $pdo->exec($this->sql);
            $u_id = (int)$pdo->lastInsertId();
            $this->setEmpty();
            $this->obj->put($pdo);
            if ($u_id) {
                return $u_id;
            } else {
                return 0;
            }
        } catch (\Throwable $th) {
            $this->obj->put($pdo);
            return 0;
            //throw $th;
        }
    }
    public function insertAll(array $dataRows): int
    {
        if (empty($dataRows)) {
            return 0;
        }
        $columns = implode(', ', array_keys($dataRows[0]));
        $valuesPlaceholder = '(' . implode(', ', array_fill(0, count($dataRows[0]), '?')) . ')';
        $valuesPlaceholders = implode(', ', array_fill(0, count($dataRows), $valuesPlaceholder));
        $sql = "INSERT INTO `$this->table` ($columns) VALUES $valuesPlaceholders";
        $allValues = [];
        foreach ($dataRows as $data) {
            $allValues = array_merge($allValues, array_values($data));
        }
        $pdo = $this->obj->get();
        try {
            $statement = $pdo->prepare($sql);
            foreach ($allValues as $k => $v) {
                $statement->bindParam($k+1,$v);
            }
            $this->sql=$statement->queryString;
            $statement->execute();
            $this->_write_log();
            $rowCount = $statement->rowCount();
            $this->obj->put($pdo);
            $this->setEmpty();
            return $rowCount;
        } catch (\Throwable $e) {
            $this->obj->put($pdo);
            return 0;
        }
    }
    public function setLogPath($path)
    {
        $this->logPath = $path;
        return $this;
    }
    public function count($id='id')
    {
        $where = implode(' AND ', $this->where);
        $database = $this->database ? "`$this->database`." : '';
        $sql = "SELECT COUNT($id) as count FROM $database$this->table";
        if ($where) {
            $sql .= " WHERE $where";
        }
        $pdo = $this->obj->get();
        $this->_write_log();
        try {
            $statement = $pdo->prepare($sql);
            $statement->execute();
            $datas = $statement->fetchAll(\PDO::FETCH_ASSOC);
            $this->obj->put($pdo);
            return $datas[0]['count'];
        } catch (\Throwable $th) {
            $this->obj->put($pdo);
            return 0;
        }
    }
    private function _query_data(string $action='default')
    {
        $pdo = $this->obj->get();
        try {
            $this->_write_log();
            $statement = $pdo->prepare($this->sql);
            $statement->execute();
            switch ($action) {
                case 'default':
                    $rowCount = $statement->rowCount();
                    $this->obj->put($pdo);
                    $this->setEmpty();
                    return $rowCount;
                    break;
                case 'select':
                    $datas = $statement->fetchAll(\PDO::FETCH_ASSOC);
                    $this->obj->put($pdo);
                    $this->setEmpty();
                    return $datas;
                    break;
            }
        } catch (\Throwable $th) {
            //throw $th;
            $this->obj->put($pdo);
            return null;
        }
    }
    private function _write_log()
    {
        if($this->logPath != ''){
            go(function () {
                $path = $this->logPath . DIRECTORY_SEPARATOR . 'sqllog';
                if (!is_dir($path)) mkdir($path, 0777);
                file_put_contents($path . '/' . date('Ymd') . '.txt', 'SQL语句(' . date('Y-m-d H:i:s') . '):' . $this->sql . PHP_EOL, FILE_APPEND);
            });
        } 
        
    }
    public function query($sql)
    {
        $this->sql = $sql;
        if ($this->obj instanceof \Swoole\Database\PDOPool) {
            $this->_write_log();
            $data = $this->_query_data();
            $this->setEmpty();
            return $data;
        } else {
            return [];
        }
    }

    private function setEmpty()
    {
        $this->database = '';
        $this->table = '';
        $this->fields = ['*'];
        $this->where = [];
        $this->order = [];
        $this->limit = '';
        $this->data = [];
        $this->sql = '';
    }
}