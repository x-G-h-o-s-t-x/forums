<?php
// Prevent direct access from url to this file
if(stristr(htmlentities($_SERVER['SCRIPT_NAME']), 'pdo.class.php')): // input your class file name here
    echo '<meta http-equiv="Refresh" content="0; url=index.php">'; // input your index location here
    die();
endif;

/** ****************************************************
  *                  @author: Ghost                    *
  *                  @copyright: 2016                  *
  **************************************************** **/

class db {

    private $engine = 'mysql'; // input your engine details here
    private $host = 'localhost'; // input your host details here
    private $username = ''; // input your username details here
    private $password = ''; // input your password details here
    private $database = ''; // input your database details here
    protected function __clone() { /** Me not like clones! Me smash clones! */ }
    public function __wakeup() { throw new Exception('Cannot unserialize singleton'); }
    private static $instance = array(); // stores the instance results in an array
    private $pdo; // stores the pdo database details
    private $pdo_options = array(PDO::ATTR_PERSISTENT => true, PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION); // stores the pdo options
    private $query; // stores the pdo queries

    // call to start the database instance
    public static function pdo() {
        $class = get_called_class(); // late-static-bound class name
            if(!isset(self::$instance[$class])):
                self::$instance[$class] = new static;
            endif;
        return self::$instance[$class];
    }

    // builds the database connection to work with
    // http://php.net/manual/en/pdo.construct.php
    public function __construct() {
        if(!isset($this->pdo)):
            try {
                $this->pdo = new PDO($this->engine.': host='.$this->host.'; dbname='.$this->database, $this->username, $this->password, $this->pdo_options);
            } catch(PDOException $error) {
                printf('<br/><u>Information:</u><br/><b>Connecting To Database Failed:</b><br/>%s', $error->getMessage(), '<br/>');
                exit();
            }
        endif;
    }

    // runs the pdo queries
    // http://php.net/manual/en/pdo.prepare.php
    public function query($query) {
        $this->query = $this->pdo->prepare($query);
    }

    // binds values to placeholders
    // http://php.net/manual/en/pdostatement.bindvalue.php
    public function bind($param, $value, $type = null) {
        if(is_null($type)):
            switch(true) {
                case is_int($value):
                    $type = PDO::PARAM_INT;
                    break;
                case is_bool($value):
                    $type = PDO::PARAM_BOOL;
                    break;
                case is_null($value):
                    $type = PDO::PARAM_NULL;
                    break;
                default:
                    $type = PDO::PARAM_STR;
            }
        endif;
        $this->query->bindValue($param, $value, $type);
    }

    // executes the pdo query
    // http://php.net/manual/en/pdostatement.execute.php
    public function execute() {
        try {
            return $this->query->execute();
        } catch(PDOException $error) {
            printf('<br/><u>Information:</u><br/><b>query failed. check your query:</b><br/>%s', $error->getMessage(), '<br/>');
            exit();
        }
    }

    // returns the pdo object results in an array
    // http://php.net/manual/en/pdostatement.fetchall.php
    public function result() {
        return $this->query->fetchAll(PDO::FETCH_OBJ);
    }

    // returns the number of rows (count) from a pdo query
    // http://php.net/manual/en/pdostatement.rowcount.php
    public function count() {
        return $this->query->rowCount();
    }

    // returns the pdo last inserted id number
    // http://php.net/manual/en/pdo.lastinsertid.php
    public function lastInsertId() {
        return $this->pdo->lastInsertId();
    }

    // initiates a transaction (turns off autocommit mode)
    // http://php.net/manual/en/pdo.begintransaction.php
    public function beginTransaction() {
        return $this->pdo->beginTransaction();
    }

    // commits a transaction
    // http://php.net/manual/en/pdo.commit.php
    public function commit() {
        return $this->pdo->commit();
    }

    // rolls back the current transaction
    // http://php.net/manual/en/pdo.rollback.php
    public function rollBack() {
        return $this->pdo->rollBack();
    }

    // dumps the information contained by a prepared statement
    // http://php.net/manual/en/pdostatement.debugdumpparams.php
    public function debugDumpParams() {
        return $this->query->debugDumpParams();
    }

    // close the pdo connection to database
    // http://php.net/manual/en/pdo.connections.php#114822
    public function close() {
        $this->query = null;
        $this->pdo = null;
    }
}
?>