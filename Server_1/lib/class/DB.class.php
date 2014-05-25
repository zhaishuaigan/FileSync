<?php

class DB {

    public static $dblink;
    public static $dns = 'sqlite:';
    public static $dbname = '../runtime/data/log.sqlite';

    //构造方法
    function __construct() {
        
    }

    //析构方法
    function __destruct() {
        //释放资源
        if (isset(self::$dblink)) {
            self::$dblink = null;
        }
    }

    //返回数据库连接对象,如果不存在就创建
    public static function getdblink() {
        if (!isset(self::$dblink)) {
            if (preg_match('/^\//', self::$dbname)) {
                self::$dbname = str_replace('\\', '/', $_SERVER['DOCUMENT_ROOT'] . self::$dbname);
                //echo self::$dbname;
            } else {
                self::$dbname = dirname(__FILE__) . '/' . self::$dbname;
            }
            $mydbexists = true;
            if (!file_exists(self::$dbname)) {
                $mydbexists = false;
            }
            self::$dblink = new PDO(self::$dns . self::$dbname);
            if (!$mydbexists) {  //如果数据库不存在,就创建,并且创建表结构
                self::exec("
                    --创建文件信息表
                    create table files(id INTEGER PRIMARY KEY, id16, name, names, exts, time, bytes, size, hash, icon, aich, mime, score, status);
                ");
            }
        }
        return self::$dblink;
    }

    //执行一条sql语句,返回是否执行成功
    public static function exec($sql) {
        return self::getdblink()->exec($sql);
    }

    //执行一条sql语句,并且返回结果
    public static function query($sql) {
        return self::getdblink()->query($sql);
    }

    //将执行结果放在一个二维数组中,默认字段名为数组的下表
    public static function query_array($sql) {
        return self::query($sql)->fetchAll(2);
    }

    //收缩数据库
    public static function vacuum() {
        return self::exec("VACUUM");
    }

    //参数化查询
    public static function prepare($sql, $arr) {
        $stm = self::getdblink()->prepare($sql);
        if (preg_match_all("/\:\w+/", $sql, $param)) {
            $i = 0;
            foreach ($param[0] as $p) {
                $stm->bindParam($p, $arr[$i++]);
            }
            $stm->execute();
            return $stm;
        }
        return false;
    }

    //参数化查询后将结果放在二维数组中,默认字段名为数组的下表
    public static function prepare_array($sql, $arr, $type = 2) {
        return self::prepare($sql, $arr)->fetchAll($type);
    }

}

?>
