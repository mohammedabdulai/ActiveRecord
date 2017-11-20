<?php
/**
 * Created by Mohammed Abdulai.
 * Email: ma678@njit.edu
 * Date: 11/18/17
 * Time: 7:35 PM
 */
//turn on debugging messages
ini_set('display_errors', 'Off');
error_reporting(E_ALL);

define('DATABASE', 'ma678');
define('USERNAME', 'ma678');
define('PASSWORD', 'vbBE9fgQM');
define('CONNECTION', 'sql1.njit.edu');

class dbConn{
    //variable to hold connection object.
    protected static $db;
    //private construct - class cannot be instatiated externally.
    private function __construct() {
        try {
            // assign PDO object to db variable
            self::$db = new PDO( 'mysql:host=' . CONNECTION .';dbname=' . DATABASE, USERNAME, PASSWORD );
            self::$db->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
        }
        catch (PDOException $e) {
            //Output error - would normally log this to error file rather than output to user.
            echo "Connection Error: " . $e->getMessage();
        }
    }
    // get connection function. Static method - accessible without instantiation
    public static function getConnection() {
        //Guarantees single instance, if no connection object exists then create one.
        if (!self::$db) {
            //new connection object.
            new dbConn();
        }
        //return connection.
        return self::$db;
    }
}
abstract class collection {
    static public function create() {
      $model = new static::$modelName;
      return $model;
    }
    static public function findAll() {
        $db = dbConn::getConnection();
        $tableName = get_called_class();
        $sql = 'SELECT * FROM ' . $tableName.';';
        $statement = $db->prepare($sql);
        $statement->execute();
        //$class = static::$modelName;
        //$statement->setFetchMode(PDO::FETCH_CLASS, $class);
        $statement->setFetchMode(PDO::FETCH_ASSOC);
        $recordsSet =  $statement->fetchAll();
        return $recordsSet;
        //print_r ($recordsSet) . '<hr>';
        //echo '<hr>';
    }
    static public function findOne($id) {
        $db = dbConn::getConnection();
        $tableName = get_called_class();
        $sql = 'SELECT * FROM ' . $tableName . ' WHERE id = '. $id .';';
        $statement = $db->prepare($sql);
        $statement->execute();
        //$class = static::$modelName;
        //$statement->setFetchMode(PDO::FETCH_CLASS, $class);
        $statement->setFetchMode(PDO::FETCH_ASSOC);
        $recordsSet =  $statement->fetchAll();
        return $recordsSet;
        //print_r ($recordsSet[0]);
        //echo '<hr>';
    }
}
class accounts extends collection {
    protected static $modelName = 'account';
}
class todos extends collection {
    protected static $modelName = 'todo';
}
class model {
    protected $tableName;
    public function save($id = NULL)
    {
        if ($id == NULL) {
            $sql = $this->insert();
        } else {
            $sql = $this->update();
        }
        $db = dbConn::getConnection();
        $statement = $db->prepare($sql);
        $statement->execute();
        $tableName = get_called_class();
        $array = get_object_vars($this);
        $columnString = implode(',', $array);
        $valueString = ":".implode(',:', $array);
       // echo "INSERT INTO $tableName (" . $columnString . ") VALUES (" . $valueString . ")</br>";
        if($id == NULL){
            echo '<h2>Insert Excuted (todos)</h2>';
            echo '<mark>I just inserted a record</mark><br/>';
            echo $statement->rowCount() . " record(s) INSERTED successfully<br/>";
        }else{
            echo '<h2>Update Excuted (todos)</h2>';
            echo '<mark>I just updated a record - ID: ' . $this->id . '</mark><br/>';
            echo $statement->rowCount() . " record(s) UPDATED successfully<br/>";
        }
    }
    private function insert() {
        $sql = "INSERT INTO todos VALUES('', '$this->owneremail', '$this->ownerid', '$this->createddate', '$this->duedate', '$this->message', '$this->isdone')";
        return $sql;
    }
    private function update() {
        $sql = "UPDATE todos SET owneremail = 'ma678@njit.edu' WHERE ownerid = $this->ownerid";
        return $sql;
    }
    public function delete() {
        $db = dbConn::getConnection();
        $sql = "DELETE FROM todos WHERE id = $this->id";
        $statement = $db->exec($sql);
        echo '<h2>Delete Excuted</h2>';
        echo '<mark>I just deleted record with id: ' . $this->id . ' where message is: '. $this->message .'</mark><br/>';
        echo $statement->rowCount() . " record(s) DELETED successfully<br/>";       
    }
}
class account extends model {
}
class todo extends model {
    public $id;
    public $owneremail;
    public $ownerid;
    public $createddate;
    public $duedate;
    public $message;
    public $isdone;
    public function __construct()
    {
        $this->tableName = 'todos';
	
    }
}
class display{
    public static function table($obj, $tableName){
        $table ='';
        $table .= '<table>';
        if($tableName=='accounts'){
            $table .= "<tr>";
            $table .= "<th>ID</th>";
            $table .= "<th>EMAIL</th>";
            $table .= "<th>FIRST NAME</th>";
            $table .= "<th>LAST NAME</th>";
            $table .= "<th>PHONE</th>";
            $table .= "<th>BIRTHDAY</th>";
            $table .= "<th>GENDER</th>";
            $table .= "<th>PASSWORD</th>";
            $table .= "</tr>";            
        }
        else if($tableName=='todos'){
            $table .= "<tr>";
            $table .= "<th>ID</th>";
            $table .= "<th>OWNER EMAIL</th>";
            $table .= "<th>OWNER ID</th>";
            $table .= "<th>CREATE DATE</th>";
            $table .= "<th>DUE DATE</th>";
            $table .= "<th>MESSAGE</th>";
            $table .= "<th>IS DONE</th>"; 
            $table .= "</tr>";
        }
        foreach ($obj as $row) {
            if($tableName=='accounts'){
                $table .= "<tr>";
                $table .= "<td>".$row['id']."</td>";
                $table .= "<td>".$row['emaill']."</td>";
                $table .= "<td>".$row['fname']."</td>";
                $table .= "<td>".$row['lname']."</td>";
                $table .= "<td>".$row['phone']."</td>";
                $table .= "<td>".$row['birthday']."</td>";
                $table .= "<td>".$row['gender']."</td>";
                $table .= "<td>".$row['password']."</td>";
                $table .= "</tr>";
            } 
            else if($tableName=='todos'){
                $table .= "<tr>";
                $table .= "<td>".$row['id']."</td>";
                $table .= "<td>".$row['owneremail']."</td>";
                $table .= "<td>".$row['ownerid']."</td>";
                $table .= "<td>".$row['createddate']."</td>";
                $table .= "<td>".$row['duedate']."</td>";
                $table .= "<td>".$row['message']."</td>";
                $table .= "<td>".$row['isdone']."</td>"; 
                $table .= "</tr>";
            }    
        }
        $table .= '</table>';
        print $table; 
    }
}
// this would be the method to put in the index page for accounts
$records = accounts::findAll();
echo '<h2>Select All Records (accounts)</h2>';
echo '<mark>Select All Records</mark>';
display::table($records, 'accounts');
echo '<hr>';

// this would be the method to put in the index page for todos
$records = todos::findAll();
echo '<h2>Select All Records (todos)</h2>';
echo '<mark>Select All Records</mark>';
display::table($records, 'todos');
echo '<hr>';

//this code is used to get one record and is used for showing one record or updating one record
$record = accounts::findOne(1);
echo '<h2>Select One Record (accounts)</h2>';
echo '<mark>Select One Record - ID: 1</mark>';
display::table($record, 'accounts');
echo '<hr>';

$record = todos::findOne(1);
echo '<h2>Select One Record (todos)</h2>';
echo '<mark>Select One Record - ID: 1</mark>';
display::table($record, 'todos');
echo '<hr>';

// New todo object created.
$record = new todo();

$record->id = '117';
$record->owneremail = 'moe@njit.edu';
$record->ownerid = '3';
$record->createddate = 'NOW()';
$record->duedate = 'DATE_ADD(NOW(), INTERVAL 1 WEEK)';
$record->message = 'Active Record';
$record->isdone = '0';

// Insert Functionality
$record->save();
// Display all records after inserting
$records = todos::findAll();
display::table($records, 'todos');
echo '<hr>';

// Update Functionality
$record->save($record->id);
// Display updated record after updating
$records = todos::findOne($record->id);
display::table($record, 'todos');
echo '<hr>';

// Delete Functionality
$record->delete();
// Display all records after deleting
$records = todos::findAll();
display::table($records, 'todos');
echo '<br/>';
echo '<h3>Thank you!</h3>';


?>