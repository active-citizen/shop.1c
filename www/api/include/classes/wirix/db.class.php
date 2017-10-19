<?php
    namespace wirix;

    class db {
        var $write_log_change = true;   //!< Запись в лог запросов на изменение БД
        var $write_log_select = false;  //!< Запись в лог запросов на выбоку из БД
        private $log_filename = '';     //!< Имя файла, куда записывается
        var $insert_id = 0;
        
        var $dbchar = 'utf8';
        var $dbhost = 'localhost';
        var $dbuser = '';
        var $dbpass = '';
        var $dbname = '';
        
        var $show_error = true;
        var $show_query_error = true;
        var $stripcslashes = true;
        
        //  Ссылка на соединение с БД
        var $link = null;
        var $record = array();  //!< Хранение результата выборки одного элемента
        var $rows = array();    //!< Хранение результата выборки кучи элементов
        var $pages = array();   //!< Массив страниц
        
        var $error_stop = false; //!< Останавливать скрипт при обнаружении ошибки
        var $show_timestamp = false;//!< Показывать timestamp в результатах
        var $query;
        
        function __construct($dbhost='',$dbuser='',$dbpass='',$dbname='') {
            if($dbhost)$this->dbhost = $dbhost;
            if($dbuser)$this->dbuser = $dbuser;
            if($dbpass)$this->dbpass = $dbpass;
            if($dbname)$this->dbname = $dbname;
            if(!$this->link = @mysqli_connect($this->dbhost, $this->dbuser, $this->dbpass, $this->dbname)){
                die("Cant connect to database");
            }
            
            $this->log_filename = $_SERVER['DOCUMENT_ROOT']."/sql-patches/".date("Y-m-d").".sql";
            
            if(!is_object($this->link)){
                echo $_SERVER['error']->alert(mysqli_connect_error(), true);
                die;
            }
            else
                $this->sql_query("SET NAMES '".$this->dbchar."'", "select");
            
        }       

        /**
         *  Функция производит запись запросов на изменение БД в ЛОГ
        */
        function write_log(
            $query,             //!< Записываемый запрос
            $type = "change"    //!< Уточнение типа запроса (change/select) чтобы логгер знал запрос ли это на изменение БД или на выборку
        ){
            //-----Depricated-----
            return true; 
        }
        function __destruct()  {
            session_write_close();
            if(is_object($this->link) && get_class($this->link) == 'mysqli')$this->link->close();
        }
  
        /**
            Получение записи из таблицы
        */
        function search_one(
            $table,             //!< Таблица из которой получаем запись
            $search = array(),  //!< Фильтр поиска "поле"=>"значение"
            $cond = "",         //!< Допольнительное условие выборки
            $fields = "*",      //!< Поля для выборки
            $group_by = "",     //!< Группировка
            $order_by = ''      //!< Порядок сортировки
        ){
            /* Чтобы не передавать пустой массив, в search можно передать
             *  строку и тогда мы её записываем в cond
             */
            if (!is_array($search)) {
                $cond = $search;
            }

            $table = mysqli_real_escape_string($this->link, $table);
            $this->record = array();
    
            if($fields!='*')if(!preg_match("/`id`/", $fields))$fields = $fields.",`id`";
    
            $where = "1 ";
            foreach($search as $field=>$value){
                $where .= " AND
                ".mysqli_real_escape_string($this->link, $field)."='".mysqli_real_escape_string($this->link, $value)."'";
            }
    
            $query = "SELECT $fields FROM `$table` WHERE ".($cond?"$where AND ".$cond:$where)." ".
                ($group_by?"GROUP BY $group_by":"")." ".
                ($order_by?"ORDER BY $order_by":"")." LIMIT 1";
    
            $stha = $this->sql_query($query, "select");
            if(method_exists($stha,"fetch_assoc")){
                $this->record = $stha->fetch_assoc();
            }
            else{
        	$fd = fopen($_SERVER["DOCUMENT_ROOT"]."/scc_err.txt","w");
        	fwrite($fd, print_r(debug_backtrace(), 1));
        	fclose($fd);

            }
            
	    if(is_array($this->record) && $this->stripcslashes)foreach($this->record as $k=>$v)$this->record[$k] = stripcslashes($v);
            unset($stha);unset($query);unset($where);
    
            if(!$this->record)return false;
            return true;
        }
  
        function search(
            $tables,                //!< Название таблиц, из которых получаем записи
            $join_fields=array(),   //!< Поля, по которым связываем таблицы
            $search = array(),      //!< Условия выборки
            $cond = "",             //!< Дополнительное условие выборки
            $sort = "", //!< Сортировка результата
            $limit = 0,             //!< Ограничение на вывод результатов
            $offset = 0,            //!< Смещенние начальной позиции результатов
            $fields_array = array(),//!< Выводимые поля
            $group_by = ""          //!< Группировка
        ){
            if(!$sort)$sort = "`a`.`id` ASC";
            
            $fields = "";
            if(!count($fields_array)){
                $fields = "*";
            }elseif(!is_array($fields_array)){
                $fields = $fields_array;
            }else{
                $fields = "unix_timestamp() as `timestamp`";
                foreach($fields_array as $k=>$v)
                    $fields .= ", $k".($v?" AS `$v`":"");
            }
    
            $offset = intval($offset);
            $limit = intval($limit);
    
            $t = array();
            foreach($tables as $k=>$table)
                $t[] = $this->escape($table)."` AS `$k`";
    
            // Предварительная обработка связей
            $join_array = array();
            foreach($join_fields as $k=>$v)$join_array[] = array("field"=>$k,"join"=>$v);
    
    
            foreach($tables as $first_table_alias=>$v)break;
            $where = "1 ";
            foreach($search as $field=>$value)
                $where .= " AND ".$this->escape($field)."='".$this->escape($value)."'";
            
    
            $join = "";
            if(isset($t[1]) && $join_array)
                for($i=1;$i<count($t);$i++)
                    $join .= " ".$join_array[$i-1]['join']." JOIN `".$t[$i]." ON ".$join_array[$i-1]['field'];
                
            
            $query = "SELECT $fields FROM `".$tables[$first_table_alias]."` as `$first_table_alias` $join WHERE ".
                ($cond?"$where AND ".$cond:$where)." ".($group_by?" GROUP BY $group_by":"");
    
            $stha = $this->sql_query($query, "select");
            
            $total_records = $stha->num_rows;
            
            $query .= " ".($sort?" ORDER BY $sort":"").($limit?" LIMIT $offset , $limit":"");

            if(!$stha = $this->sql_query($query, "select"))return false;            
            
            $this->rows = array();
            if($stha)
                while($row = $stha->fetch_assoc()){
                    if(!$this->show_timestamp)unset($row['timestamp']);
                    foreach($row as $k=>$v)$row[$k] = str_replace("\\\"", "\"", $v);
                    foreach($row as $k=>$v)$row[$k] = str_replace("\\'", "'", $v);
                    $this->rows[] = $row;
                }
    
            $this->pages = $this->get_pages_list($total_records, $offset, $limit);
            
            return true;
        }
  
        /**
         * Генерация ошибки базы данных
        */
        private function rise_error(
            $query  //!< Текст запроса
        ){
            $this->errno = $this->link->errno;
            $this->error = $this->link->error;
            $error_text = "";
            if($this->errno && $this->show_error)$error_text .= $this->error;
            if($this->errno && $this->show_error && $this->show_query_error)$error_text .= " <i>$query</i>";
            if($this->errno && $this->show_error){echo $error_text;}
            if($this->errno && $this->error_stop)die;
            return true;
        }    
        
        /**
         * Исполнение запроса к БД
         */
        function sql_query(
            $query,     //!< Текст запроса
            $type       //!< change/select
        ){
            $this->write_log($query, $type);
            $result = $this->link->query($query);
            if($this->link->errno){
                $this->rise_error($query);
                return false;
            }
            return $result;
        }       
        
            
        /**
            Добавление поля к таблице
            Примеры использования
            $_SERVER['db']->table_add_column("users", "name", "char(32)","","UNIQUE","Имя пользователя");
            $_SERVER['db']->table_add_column("users", "password", "char(40)","","","SHA1-хэш имени пользователя и пароля");
            $_SERVER['db']->table_add_column("users", "email", "char(40)","admin@localhost","UNIQUE","email пользователя");
        */
        function table_add_column(
            $table,                 //!< Таблица к которой добавляем
            $column,                //!< Имя добавляемого поля
            $type = "CHAR(255)",    //!< Тип поля
            $default = '',          //!< Значение по умолчанию
            $index = "",            //!< Ключ пусто - без ключа, "KEY" - обычный индекс, "UNIQUE" - уникальный индекс
            $comment = ''           //!< Комментарий к полю
        ){
            $query = "ALTER TABLE `$table` ADD COLUMN `$column` $type ";
            if($default)$query .= " DEFAULT '$default'";
            if($comment)$query .= " COMMENT '$comment'";
            if(!$this->sql_query($query, "change"))return false;
            if($index){
                $query = "ALTER TABLE `$table` ADD $index `$column`(`$column`)";
                if(!$this->sql_query($query, "change"))return false;
            }
            return true; 
        }
        
        /**
            Выполнение файла с SQL-запросами. Обязательное условие: по одному запросу на строку
        */
        function sql_load_file(
            $filename,              //!< Имя файла
            $show_errors = false    //!< Показывать ошибки выполнения запросов
        ){
            $queries = file($filename);
            $show_error = $this->show_error;
            $error_stop =  $this->error_stop;
            $this->show_error = $show_errors;
            $this->error_stop = false;
            foreach($queries as $query){
                if(!trim($query))continue;
                $this->sql_query($query, "select");
            }
            $this->show_error = $show_error;
            $this->error_stop = $error_stop;
            
        }
        
        /**
            Удаление таблицы из БД
        */
        function drop_table($table_name){
            $query = "DROP TABLE IF EXISTS `".$table_name."`";
            $this->sql_query($query, "change");
        }
        
        /**
            Созание шаблонной таблицы с зарезервированными полями
            Пример использования
            $_SERVER['db']->create_tmpl_table("users");
        */
        function create_tmpl_table(
            $table_name,        //!< Имя таблицы
            $table_comment = '',//!< Комментарий к таблице
            $autoincrement = 1 //!< Автоинкремент
        ){
            $query = "
                CREATE TABLE IF NOT EXISTS `".$table_name."` (
                  `id` int(11) NOT NULL AUTO_INCREMENT,
                  `ctime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
                  `mtime` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
                  PRIMARY KEY (`id`),
                  KEY `ctime` (`ctime`),
                  KEY `mtime` (`mtime`)
                ) ENGINE=MyISAM DEFAULT CHARSET=".$this->dbchar." AUTO_INCREMENT=$autoincrement COMMENT '$table_comment';               
            ";
            $this->sql_query($query, "change");
        }

        /**
         * Добавление индекса к таблице
         */
        function table_add_index(
            $table,             //!< Имя таблицы
            $index_name,        //!< Имя индекса(для исключения дублирования)
            $fields = array(),  //!< Поля для включения в индекс
            $index_type = 'UNIQUE'
        ){
            if(!$table)return false;
            if(!$index_name)return false;
            if(!$fields)return false;
            
            $columns = '';
            for($i=0, $c = count($fields); $i<$c; $i++){
                $columns .= "`".$fields[$i]."`";
                if($i<$c-1)$columns .= ',';
            }
            
            $query = "ALTER TABLE `$table` ADD $index_type `$index_name`($columns);";
            if(!$this->sql_query($query, "change"))return false;
            return true;
        }

        function escape($string){
            if(is_array($string))print_r($string);
            return $this->link->real_escape_string($string);
        }
        
        
        /**
            Формирование массива страниц
            возвращает массив страниц вида
        */
        function get_pages_list(
            $total,             //!< общее число записей
            $offset=0,          //!< номер рекущей страницы(начиная с 1)
            $perpage=10,        //!< число записей на страницу
            $blocksize = 10     //!< размер блока сраниц
        ){
            if(!intval($perpage))$perpage = 10;
            
            $page = floor($offset/$perpage)+1;
            $page = intval($page) && $page>0?$page:1;
            $total = intval($total) && $total>0?$total:1;
            $perpage = intval($perpage) && $perpage>0?$perpage:10;
            $blocksize = intval($blocksize) && $blocksize>0?$blocksize:10;
            
            // Номер блока страниц
            $blocknum = floor(($page-1)/$blocksize + 1);
            // Определение общего количества страниц
            $total_pages = floor(($total-1)/$perpage + 1);
            // Определение общего количества блоков
            $total_blocks = floor(($total_pages-1)/$blocksize + 1);
            
            $result = array();
            if($blocknum>1){
                $result[0] = '1';
                $result[($blocknum-2)*$blocksize*$perpage] = '..';
            }
            for($i=($blocknum-1)*$blocksize+1;$i<=$blocknum*$blocksize && $i<=$total_pages;$i++){
                $result[($i-1)*$perpage] = $i;
            }
            if($blocknum*$blocksize<$total_pages)$result[($blocknum*$blocksize)*$perpage] = '..';
            if($blocknum*$blocksize<$total_pages)$result[($total_pages-1)*$perpage] = $total_pages;
            
            
            return $result;
            
        }
        
        /**
            \brief Добавление строки
            
            Добавляемые поля передаются в виде массива 
            @code
            array(
                "Имя поля1"=>"значение1",
                "Имя поля2"=>"значение1",
                .....
                "Имя поляN"=>"значениеN",
            );
            @endcode
            перед выполнения INSERT-запроса имена полей и значения оборачиваются в mysqli_real_escape_string
            
            @returns ID добавленной записи
        */
        function insert(
            $table,     //!< Имя таблицы, в которую добавляем запись
            $fields     //!< добавляемые поля
        ){
            
            $table = mysqli_real_escape_string($this->link, $table);
    
            $fields_list = "`id` ";
            // Если id не задан - используем автоинкремент
            $values_list = (!isset($fields['id'])?"NULL ":"'".$fields['id']."'");
            // Формируем набор полей и значений
            foreach($fields as $key=>$value){
                if($key=='id')continue;
                $fields_list .= ", `".mysqli_real_escape_string($this->link,$key)."` ";
                $values_list .= ", '".mysqli_real_escape_string($this->link,$value)."' ";
            }
            
            $query = "INSERT INTO `$table`($fields_list) VALUES(".$values_list.")";
            
            if(!$result = $this->sql_query($query, "change")){
                $this->error = mysqli_error($this->link);
                return false;
            }
            $this->insert_id = mysqli_insert_id($this->link);
            return $this->insert_id;
        }
        
        /**
            @grief Обновление строки в таблице
            
            
            Обновляемые поля передаются в виде массива 
            @code
            array(
                "Имя поля1"=>"значение1",
                "Имя поля2"=>"значение1",
                .....
                "Имя поляN"=>"значениеN",
            );
            @endcode
            перед выполнения UPDATE-запроса имена полей и значения оборачиваются в mysqli_real_escape_string
            
            Поля фильтра передаются в виде массива 
            @code
            array(
                "Имя поля1"=>"значение1",
                "Имя поля2"=>"значение1",
                .....
                "Имя поляN"=>"значениеN",
            );
            @endcode
            обновляются только те строки, значения полей в которых соответствуют всем условиям фильтра.
            
            
            
            \attention Значение аттрибута $cond не контролиреется на наличие SQL-инъекций, и должно быть проверено самостоятельно
            
            \attention функция по умолчанию обновляет только первую подходящую строку таблицы, см. описание параметра $limit
            
            \attention Значение параметра $search игнорируется, если задан параметр $cond

            @returns Количество затронутых записей
        */
        function update(
            $table,             //!< Таблица, в которой обновляем строку
            $fields,            //!< Список полей для обновления "поле"=>"значение"
            $search = array(),  //!< Поля для фильтра "поле"=>"значение"
            $cond = "",         //!< Условия
            $limit = 1          //!< Количество обновляемых записей за раз (по умолчанию - одна). 0 - без ограничений
        ){
            
            /* Чтобы не передавать пустой массив, в search можно передать
             *  строку и тогда мы её записываем в cond
             */
            if (!is_array($search)) {
                $cond = $search;
            }
            
            $table = mysqli_real_escape_string($this->link, $table);
    
            $set = " SET id = id";
            foreach($fields as $key=>$value){
                $set .= ", `".mysqli_real_escape_string($this->link, $key)."` = '".mysqli_real_escape_string($this->link, $value)."' ";
            }
    
    
            $where = "1 ";
            foreach($search as $field=>$value){
                $where .= " AND
                `".mysqli_real_escape_string($this->link, $field)."`='".mysqli_real_escape_string($this->link, $value)."'";
            }
    
            $query = "UPDATE `$table` $set, `mtime`='".date("Y-m-d H:i:s")."' WHERE ".($cond?$cond:$where).($limit?" LIMIT $limit":"");
            //echo $query;
            if(!$result = $this->sql_query($query, "change"))return false;
            
            return $this->link->affected_rows;
        }

        /**
            Удаление строк в таблице
        */
        function delete(
            $table,             //!< Таблица, в которой удаляем строку
            $search = array(),  //!< Поля для фильтра "поле"=>"значение"
            $cond = "",          //!< Условия
            $limit = 1          //!< Ограничение на удаления 0 - без ограничений
        ){
            $table = mysqli_real_escape_string($this->link, $table);
    
            $where = "1 ";
            foreach($search as $field=>$value){
                $where .= " AND
                `".mysqli_real_escape_string($this->link, $field)."`='".mysqli_real_escape_string($this->link, $value)."'";
            }
    
            $query = "DELETE FROM `$table` WHERE ".($cond?$cond:$where).($limit?" LIMIT $limit":"");
            if(!$result = $this->sql_query($query, "change")){
                $this->error = mysqli_error($this->link);
                return false;
            }
            return $this->link->affected_rows;
        }

        /**
            Получение описания таблицы в виде массива
        */
        function table_describe($table_name = ''){
            $result = array();
            $stha = $this->sql_query("DESCRIBE ".$this->link->real_escape_string($table_name), "select");
            if(!$stha)return false;
            while($result[] = $stha->fetch_assoc());
            return $result;
        }
    }
?>
