<?php
/**
 * Base Model
 */
class BaseModel
{
    // 操作状态
    const MODEL_INSERT    = 1; //  插入模型数据
    const MODEL_UPDATE    = 2; //  更新模型数据
    const MODEL_BOTH      = 3; //  包含上面两种方式
    const MUST_VALIDATE   = 1; // 必须验证
    const EXISTS_VALIDATE = 0; // 表单存在字段则验证
    const VALUE_VALIDATE  = 2; // 表单值不为空则验证
    
    protected static $master = null;
    protected static $slave  = null;

    protected static $defaultOperate = array('count' => 1, 'sum' => 1, 'min' => 1, 'max' => 1, 'avg' => 1);
    // 数据库表达式
    protected static $comparison = array('eq' => '=', 'neq' => '<>', 'gt' => '>', 'egt' => '>=', 'lt' => '<', 'elt' => '<=', 'notlike' => 'NOT LIKE', 'like' => 'LIKE', 'in' => 'IN', 'notin' => 'NOT IN');


    protected $tableName = '';
    // 是否批处理验证
    protected $patchValidate = false;
    // 参数绑定
    protected $bind          = array();
    protected $options       = array();
    protected $config        = array();
    protected $data          = array();
    private $lastSql         = array();
    
    
    public function __call($method, $args)
    {
        $method = strtolower($method);
        if (isset(self::$defaultOperate[$method])) {
            // 统计查询的实现
            $field = isset($args[0]) ? $args[0] : '*';
            $this->field("{$method}($field) AS {$method}");
            $data = $this->find();
            return $data[$method];
        } else {
            $this->options[$method] = $args[0];
        }
        
        return $this;
    }
    
    
    
    
    
    protected function getDB($operate = 'select')
    {
        if ($operate === 'select') {
            return $this->getSlave();
        } else {
            return $this->getMaster();
        }
    }
    
    
    protected function getMaster()
    {
        if (self::$master === null) {
            // try {
                $this->_initConfig();
                self::$master = new PDO($this->config['m']['dns'], $this->config['m']['username'], $this->config['m']['password']);
                self::$master->setAttribute(PDO::ATTR_EMULATE_PREPARES, true);
            // }
            // catch (PDOException $e) {
            //     echo "Error!: ", $e->getMessage(), "</br>";
            //     exit;
            // }
        }
        
        return self::$master;
    }
    
    
    protected function getSlave()
    {
        if (empty($this->config['s'])) {
            return $this->getMaster();
        }
        
        if (self::$slave === null) {
            try {
                $this->_initConfig();
                self::$slave = new PDO($this->config['s']['dns'], $this->config['s']['username'], $this->config['s']['password']);
                self::$slave->setAttribute(PDO::ATTR_EMULATE_PREPARES, true);
            }
            catch (PDOExecption $e) {
                echo "Error!: ", $e->getMessage(), "</br>";
                exit;
            }
        }
        
        return self::$slave;
    }
    
    
    
    private function _initConfig()
    {
        if (!empty($this->config)) {
            return;
        }
        $config = Yaf_Registry::get('config')->database->toArray();
        
        if (empty($config['separate'])) {
            $data['m'] = array(
                'dns' => "{$config['adapter']}:host={$config['host']};dbname={$config['dbname']};charset=utf8",
                'username' => $config['username'],
                'password' => $config['password']
            );
        } else {
            $data['m'] = array(
                'dns' => "{$config['adapter']}:host={$config['host']['m']};dbname={$config['dbname']};charset=utf8",
                'username' => $config['username'],
                'password' => $config['password']
            );
            
            $data['s'] = array(
                'dns' => "{$config['adapter']}:host={$config['host']['s']};dbname={$config['dbname']}",
                'username' => $config['username'],
                'password' => $config['password']
            );
        }
        
        if (!empty($config['port'])) {
            isset($data['m']) && $data['m']['dns'] .= ";port={$config['port']}";
            isset($data['s']) && $data['s']['dns'] .= ";port={$config['port']}";
        }
        
        $this->config = $data;
    }
    
    
    
    public function execute($sql, $data = array(), $type = 'select')
    {
        $this->setLastSql($sql);
        $sth = $this->getDB($type)->prepare($sql);
        empty($data) && $data = $this->getBindValues();

        if ($sth->execute($data) === false) {
            $error = $sth->errorInfo();
            throw new Exception($error[2]);
        }

        $this->reset();
        return $sth;
    }
    
    
    
    public function select()
    {
        $sql = $this->getSelectSql();
        return $this->execute($sql)->fetchAll(PDO::FETCH_ASSOC);
    }
    

    public function add($data = array())
    {
        $sql = $this->getInsertSql($data);
        $this->execute($sql, '', 'insert');
        return $this->getSlave()->lastInsertId();
    }

    
    public function save($data=array())
    {
        $sql = $this->getUpdateSql($data);
        return $this->execute($sql, '', 'insert')->rowCount();
    }
    
    
    public function delete()
    {
        $sql = $this->getDeleteSql();
        return $this->execute($sql, '', 'insert')->rowCount();
    }
    
    
    public function find()
    {
        $sql = $this->getSelectSql();
        return $this->execute($sql)->fetch(PDO::FETCH_ASSOC);
    }
    
    

    public function getSelectSql()
    {
        $selectSql = 'SELECT%DISTINCT% %FIELD% FROM %TABLE%%JOIN%%WHERE%%GROUP%%ORDER%%LIMIT%';

        return str_replace(array(
            '%TABLE%',
            '%DISTINCT%',
            '%FIELD%',
            '%JOIN%',
            '%WHERE%',
            '%GROUP%',
            '%ORDER%',
            '%LIMIT%'
        ), array(
            $this->parseTable(),
            $this->parseDistinct(),
            $this->parseField(),
            $this->parseJoin(),
            $this->parseWhere(),
            $this->parseGroup(),
            $this->parseOrder(),
            $this->parseLimit()
        ), $selectSql);
    }



    public function getInsertSql($data = array())
    {
        $insertSql = 'INSERT %PRIORITY% INTO %TABLE% %VALUES%';

        return str_replace(array(
            '%PRIORITY%',
            '%TABLE%',
            '%VALUES%',
        ), array(
            $this->parsePriority(),
            $this->parseTable(),
            $this->parseInsertValues($data),
        ), $insertSql);
    }


    public function getUpdateSql($data = array())
    {
        $updateSql = 'UPDATE %PRIORITY% %TABLE% SET %VALUES%%WHERE%%ORDER%%LIMIT%';

        return str_replace(array(
            '%PRIORITY%',
            '%TABLE%',
            '%VALUES%',
            '%WHERE%',
            '%ORDER%',
            '%LIMIT%',
        ), array(
            $this->parsePriority(),
            $this->parseTable(),
            $this->parseSet($data),
            $this->parseWhere(),
            $this->parseOrder(),
            $this->parseLimit()
        ), $updateSql);
    }


    public function getDeleteSql()
    {
        $deleteSql = 'DELETE %PRIORITY% FROM %TABLE%%WHERE%%ORDER%%LIMIT%';
        
        return str_replace(array(
            '%PRIORITY%',
            '%TABLE%',
            '%WHERE%',
            '%ORDER%',
            '%LIMIT%',
        ), array(
            $this->parsePriority(),
            $this->parseTable(),
            $this->parseWhere(),
            $this->parseOrder(),
            $this->parseLimit()
        ), $deleteSql);
    }


    
    protected function parseTable($table = '')
    {
        if (empty($table)) {
            if (!empty($this->options['table'])) {
                $table = $this->options['table'];
            } else if (!empty($this->table)) {
                $table = $this->table;
            }
        }
        
        return "`{$table}`";
    }
    
    
    protected function parseDistinct()
    {
        return empty($this->options['distinct']) ? '' : ' DISTINCT ';
    }
    
    
    // array(
    //     'table1' => array('id','name'),
    //     'table2' => array('*'),
    // )
    protected function parseField($field = '')
    {
        empty($field) && $field = $this->options['field'];
        if (empty($field)) {
            return '*';
        }
        
        if (is_array($field)) {
            $arr = array();
            foreach ($field as $key => $data) {
                if (is_array($data)) {
                    foreach ($data as $value) {
                        $arr[] = "{$key}.`{$value}`";
                    }
                } else {
                    $arr[] = "`{$data}`";
                }
            }
            
            empty($arr) || $field = implode(',', $arr);
        }
        
        return $field;
    }
    
    
    protected function parseJoin($join = '')
    {
        empty($join) && isset($this->options['join']) && $join = $this->options['join'];
        $joinStr = '';
        if (!empty($join)) {
            if (is_array($join)) {
                foreach ($join as $key => $_join) {
                    if (false !== stripos($_join, 'JOIN'))
                        $joinStr .= " {$_join}";
                    else
                        $joinStr .= " LEFT JOIN {$_join}";
                }
            } else {
                $joinStr .= " LEFT JOIN {$join}";
            }
        }
        
        return $joinStr;
    }
    
    /**
     * array(
     *     'id' => 1,
     *     'time' => array(array('lt', 10), array('gt', 4)),
     *     'title' => array('like', '%asd%'),
     *     'pid' => array('in', $ids),
     * 
     * )
     * @return [type] [description]
     */
    protected function parseWhere($where = '')
    {
        if (empty($where) && empty($this->options['where'])) {
            return '';
        }
        
        if (is_string($this->options['where'])) {
            $where = " WHERE {$this->options['where']}";
        } elseif (is_array($this->options['where'])) {
            foreach ($this->options['where'] as $field => $values) {
                if ( ! is_array($values)) {
                    $where[] = "`{$field}` = " . $this->bindValue($values);
                } else {
                    if (is_array($values[0])) {
                        foreach ($values as $value) {
                            $where[] = "`{$field}` " . $this->_parseItem($value);
                        }
                    } else {
                        $where[] = "`{$field}` " . $this->_parseItem($values);
                    }
                }
                
            }
            
            $where = implode(' AND ', $where);
        }
        
        return " WHERE $where";
    }
    
    
    private function _parseItem($data)
    {
        $key = strtolower($data[0]);
        
        $result = '';
        switch ($key) {
            case 'exp':
                $result = $data[1];
                break;
            
            case 'in':
            case 'notin':
                if (is_string($data[1])) {
                    $data[1] = explode(',', $data[1]);
                }
                $params = array();
                foreach ($data[1] as $value) {
                    $params[] = $this->bindValue($value);
                }
                $result = self::$comparison[$key] . ' (' . implode(',', $params) . ')';
                break;
            
            
            default:
                $result = self::$comparison[$key] . ' ' . $this->bindValue($data[1]);
                break;
        }
        
        return $result;
    }
    
    
    # 参数绑定
    protected function bindValue($data)
    {
        $this->bind[] = $data;
        return '?';
    }
    
    

    private function getBindValues()
    {
        $data       = $this->bind;
        $this->bind = array();
        return $data;
    }
    

    
    protected function parseGroup()
    {
        return empty($this->options['group']) ? '' : " GROUP BY {$this->options['group']}";
    }
    
    

    protected function parseOrder()
    {
        $order = '';
        empty($this->options['order']) || $order = $this->options['order'];
        if (is_array($order)) {
            $arr = array();
            foreach ($order as $key => $val) {
                if (is_numeric($key)) {
                    $arr[] = $this->parseKey($val);
                } else {
                    $arr[] = $this->parseKey($key) . ' ' . $val;
                }
            }
            $order = implode(',', $arr);
        }
        
        return empty($order) ? '' : " ORDER BY {$order}";
    }
    
    

    protected function parseKey($value)
    {
        return str_replace(' ', '', $value);
    }
    

    
    protected function parseLimit()
    {
        return empty($this->options['limit']) ? '' : " LIMIT {$this->options['limit']} ";
    }
    


    protected function parsePriority()
    {
        return empty($this->options['priority']) ? '' : strtoupper($this->options['priority']);
    }



    protected function parseInsertValues(array $values = array())
    {
        if (empty($values)) {
            $values = $this->data;
        }

        $fields = array();
        $placeHolder = array();
        if (isset($values[0]) && is_array($values[0])) {
            $fields = '(`' . implode('`,`', array_keys($values[0])) . '`)';
            foreach ($values as $i => $arr) {
                foreach ($arr as $value) {
                    $placeHolder[$i][] = $this->bindValue($value);
                }
                $placeHolder[$i] = '(' . implode(',', $placeHolder[$i]) . ')';
            }
            $placeHolder = implode(',', $placeHolder);
        } else {
            foreach ($values as $field => $value) {
                $fields[] = $field;
                $placeHolder[] = $this->bindValue($value);
            }
            $placeHolder = '(' . implode(',', $placeHolder) . ')';
            $fields = '(`' . implode('`,`', $fields) . '`)';
        }

        return "{$fields} VALUES {$placeHolder}";
    }



    protected function parseSet(array $values = array())
    {
        if (empty($values)) {
            $values = $this->data;
        }

        $params = array();
        foreach ($values as $field => $value) {
            $params[] = "`{$field}`=" . $this->bindValue($value);
        }

        return implode(',', $params);
    }


    
    /**
     * 创建数据对象 但不保存到数据库
     * @access public
     * @param mixed $data 创建数据
     * @param string $type 状态
     * @return mixed
     */
    public function isValid($data = '', $type = '')
    {
        // 如果没有传值默认取POST数据
        if (empty($data)) {
            $data = $_POST;
        } elseif (is_object($data)) {
            $data = get_object_vars($data);
        }
        
        // 验证数据
        if (empty($data) || !is_array($data)) {
            throw new Exception('数据类型有误');
        }
        
        $type = empty($type) ? self::MODEL_INSERT : self::MODEL_UPDATE;
        
        // 数据自动验证
        $this->autoValidation($data, $type);
        
        // 表单令牌验证
        // if( ! $this->autoCheckToken($data)) {
        //     $this->error = 'TOKEN ERROR';
        //     return false;
        // }
        
        // 创建完成对数据进行自动处理
        $this->autoOperation($data, $type);
        // 赋值当前数据对象
        $this->data = $data;
        // 返回创建的数据以供其他调用
        return $data;
    }
    
    
    
    
    /**
     * 自动表单验证
     * @access protected
     * @param array $data 创建数据
     * @param string $type 创建类型
     * @return boolean
     */
    protected function autoValidation($data, $type)
    {
        if ( ! empty($this->options['rules'])) {
            $rules = $this->options['rules'];
            unset($this->options['rules']);
        } elseif ( ! empty($this->rules)) {
            $rules = $this->rules;
        }
        // 属性验证
        if (isset($rules)) { // 如果设置了数据自动验证则进行数据验证
            if ($this->patchValidate) { // 重置验证错误信息
                $this->error = array();
            }

            foreach ($rules as $key => $val) {
                // 验证因子定义格式
                // array(field,rule,message,condition,type,when,params)
                // 判断是否需要执行验证
                if (empty($val[5]) || $val[5] == self::MODEL_BOTH || $val[5] == $type) {
                    $val[3] = isset($val[3]) ? $val[3] : self::EXISTS_VALIDATE;
                    $val[4] = isset($val[4]) ? $val[4] : 'regex';
                    // 判断验证条件
                    switch ($val[3]) {
                        case self::MUST_VALIDATE: // 必须验证 不管表单是否有设置该字段
                            $this->_validationField($data, $val);
                            break;
                        case self::VALUE_VALIDATE: // 值不为空的时候才验证
                            if ('' != trim($data[$val[0]]))
                                $this->_validationField($data, $val);
                            break;
                        default: // 默认表单存在该字段就验证
                            if (array_key_exists($val[0], $data))
                                $this->_validationField($data, $val);
                    }
                }
            }

            if ( ! empty($this->error)) {
                throw new Exception(implode(', ', $this->error));
            }
        }
        
        return true;
    }
    
    
    
    /**
     * 验证表单字段 支持批量验证
     * 如果批量验证返回错误的数组信息
     * @access protected
     * @param array $data 创建数据
     * @param array $val 验证因子
     * @return boolean
     */
    protected function _validationField($data, $val)
    {
        if (false === $this->_validationFieldItem($data, $val)) {
            if ($this->patchValidate) {
                $this->error[] = "{$val[0]}: {$val[2]}";
            } else {
                throw new Exception($val[2]);
            }
        }
        return;
    }
    
    
    /**
     * 根据验证因子验证字段
     * @access protected
     * @param array $data 创建数据
     * @param array $val 验证因子
     * @return boolean
     */
    protected function _validationFieldItem($data, $val)
    {
        if ($data[$val[0]] === null)
            return false;
        
        switch (strtolower(trim($val[4]))) {
            case 'function': // 使用函数进行验证
            case 'callback': // 调用方法进行验证
                $args = isset($val[6]) ? (array) $val[6] : array();
                if (is_string($val[0]) && strpos($val[0], ','))
                    $val[0] = explode(',', $val[0]);
                
                if (is_array($val[0])) {
                    // 支持多个字段验证
                    foreach ($val[0] as $field)
                        $_data[$field] = $data[$field];
                    
                    array_unshift($args, $_data);
                } else {
                    array_unshift($args, $data[$val[0]]);
                }
                
                if ('function' == $val[4]) {
                    return call_user_func_array($val[1], $args);
                } else {
                    return call_user_func_array(array(
                        &$this,
                        $val[1]
                    ), $args);
                }
            case 'confirm': // 验证两个字段是否相同
                return $data[$val[0]] == $data[$val[1]];
            case 'unique': // 验证某个值是否唯一
                if (is_string($val[0]) && strpos($val[0], ','))
                    $val[0] = explode(',', $val[0]);
                
                $map = array();
                if (is_array($val[0])) {
                    // 支持多个字段验证
                    foreach ($val[0] as $field)
                        $map[$field] = $data[$field];
                } else {
                    $map[$val[0]] = $data[$val[0]];
                }
                
                if (!empty($data[$this->getPk()])) { // 完善编辑的时候验证唯一
                    $map[$this->getPk()] = array(
                        'neq',
                        $data[$this->getPk()]
                    );
                }
                
                if ($this->where($map)->count() > 0)
                    return false;
                
                return true;
            default: // 检查附加规则
                return $this->check($data[$val[0]], $val[1], $val[4]);
        }
    }
    
    
    protected function getPk()
    {
        return 'id';
    }
    
    
    
    /**
     * 验证数据 支持 in between equal length regex expire ip_allow ip_deny
     * @access public
     * @param string $value 验证数据
     * @param mixed $rule 验证表达式
     * @param string $type 验证方式 默认为正则验证
     * @return boolean
     */
    public function check($value, $rule, $type = 'regex')
    {
        $type = strtolower(trim($type));
        switch ($type) {
            case 'in': // 验证是否在某个指定范围之内 逗号分隔字符串或者数组
            case 'notin':
                $range = is_array($rule) ? $rule : explode(',', $rule);
                return $type == 'in' ? in_array($value, $range) : !in_array($value, $range);
            case 'between': // 验证是否在某个范围
            case 'notbetween': // 验证是否不在某个范围            
                if (is_array($rule)) {
                    $min = $rule[0];
                    $max = $rule[1];
                } else {
                    list($min, $max) = explode(',', $rule);
                }
                return $type == 'between' ? $value >= $min && $value <= $max : $value < $min || $value > $max;
            case 'equal': // 验证是否等于某个值
            case 'notequal': // 验证是否等于某个值            
                return $type == 'equal' ? $value == $rule : $value != $rule;
            case 'length': // 验证长度
                $length = mb_strlen($value, 'utf-8'); // 当前数据长度
                if (strpos($rule, ',')) { // 长度区间
                    list($min, $max) = explode(',', $rule);
                    return $length >= $min && $length <= $max;
                } else { // 指定长度
                    return $length == $rule;
                }
            case 'expire':
                list($start, $end) = explode(',', $rule);
                if (!is_numeric($start))
                    $start = strtotime($start);
                if (!is_numeric($end))
                    $end = strtotime($end);
                return NOW_TIME >= $start && NOW_TIME <= $end;
            case 'ip_allow': // IP 操作许可验证
                return in_array(get_client_ip(), explode(',', $rule));
            case 'ip_deny': // IP 操作禁止验证
                return !in_array(get_client_ip(), explode(',', $rule));
            case 'regex':
            default: // 默认使用正则验证 可以使用验证类中定义的验证名称
                // 检查附加规则
                return $this->regex($value, $rule);
        }
    }
    
    
    
    /**
     * 使用正则验证数据
     * @access public
     * @param string $value  要验证的数据
     * @param string $rule 验证规则
     * @return boolean
     */
    public function regex($value, $rule)
    {
        $validate = array(
            'require'  => '/.+/',
            'email'    => '/^\w+([-+.]\w+)*@\w+([-.]\w+)*\.\w+([-.]\w+)*$/',
            'url'      => '/^http(s?):\/\/(?:[A-za-z0-9-]+\.)+[A-za-z]{2,4}(?:[\/\?#][\/=\?%\-&~`@[\]\':+!\.#\w]*)?$/',
            'currency' => '/^\d+(\.\d+)?$/',
            'number'   => '/^\d+$/',
            'zip'      => '/^\d{6}$/',
            'integer'  => '/^[-\+]?\d+$/',
            'double'   => '/^[-\+]?\d+(\.\d+)?$/',
            'english'  => '/^[A-Za-z]+$/'
        );
        // 检查是否有内置的正则表达式
        if (isset($validate[strtolower($rule)]))
            $rule = $validate[strtolower($rule)];
        
        return preg_match($rule, $value) === 1;
    }
    
    
    
    /**
     * 自动表单处理
     * @access public
     * @param array $data 创建数据
     * @param string $type 创建类型
     * @return mixed
     */
    private function autoOperation(&$data, $type)
    {
        if (!empty($this->options['auto'])) {
            $_auto = $this->options['auto'];
            unset($this->options['auto']);
        } elseif (!empty($this->_auto)) {
            $_auto = $this->_auto;
        }
        // 自动填充
        if (isset($_auto)) {
            foreach ($_auto as $auto) {
                // 填充因子定义格式
                // array('field','填充内容','填充条件','附加规则',[额外参数])
                if (empty($auto[2]))
                    $auto[2] = self::MODEL_INSERT; // 默认为新增的时候自动填充
                if ($type == $auto[2] || $auto[2] == self::MODEL_BOTH) {
                    switch (trim($auto[3])) {
                        case 'function': //  使用函数进行填充 字段的值作为参数
                        case 'callback': // 使用回调方法
                            $args = isset($auto[4]) ? (array) $auto[4] : array();
                            if (isset($data[$auto[0]])) {
                                array_unshift($args, $data[$auto[0]]);
                            }
                            
                            if ('function' === $auto[3]) {
                                $data[$auto[0]] = call_user_func_array($auto[1], $args);
                            } else {
                                $data[$auto[0]] = call_user_func_array(array(
                                    &$this,
                                    $auto[1]
                                ), $args);
                            }
                            
                            break;
                        case 'field': // 用其它字段的值进行填充
                            $data[$auto[0]] = $data[$auto[1]];
                            break;
                        case 'ignore': // 为空忽略
                            if ('' === $data[$auto[0]])
                                unset($data[$auto[0]]);
                            break;
                        case 'string':
                        default: // 默认作为字符串填充
                            $data[$auto[0]] = $auto[1];
                    }
                    if (false === $data[$auto[0]])
                        unset($data[$auto[0]]);
                }
            }
        }
        return $data;
    }
    

    
    public function getLastSql()
    {
        return $this->lastSql;
    }


    public function setLastSql($sql)
    {
        $this->lastSql = array(
            'sql' => $sql,
            'params' => $this->bind
        );
    }
    

    protected function reset()
    {
        $this->data = array();
        $this->options = array();
    }
}
