<?php
require 'Slim/Slim.php';

\Slim\Slim::registerAutoloader();

function createconn(){
    $host = "localhost";
    $uname = "root";
    $pass = "";
    $dbname = "meesho";
    $con = "mysql:host=$host;dbname=$dbname";
    $dbConnection = new PDO($con, $uname, $pass); 
    $dbConnection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    return $dbConnection;
}

$app = new \Slim\Slim();

$app->get('/', function() use($app) {
    $app->response->setStatus(200);
    echo "<table style='border:1px solid gray'>
            <tr><td><b>To get All the addresses</b></td><td> http://localhost/address/index.php/all</td></tr>
            <tr><td><b>To get address of people with specific name(multiple)</b></td><td> http://localhost/address/index.php/all/name</td></tr>
            <tr><td><b>To add an address</b></td><td> Open send.html or send a post request to index.php/add</td></tr>
          </table><br><br>
          Email address is primary key";
});
$app->get('/all(/:name)',function($name = "") use($app){
    try{
        $ans = 0;
        echo $name;
        $conn = createconn();
        if($name == ""){
            $sql = $conn->prepare("Select * from address");
            $sql->execute();
            $all = $sql->fetchAll(PDO::FETCH_OBJ);
        }
        else
        {
            $sql = $conn->prepare("Select * from address where name = :name");
            $sql->bindParam(':name', $name, PDO::PARAM_INT);
            $sql->execute();
            $all = $sql->fetch(PDO::FETCH_OBJ);
        }
        if($all) {
            $app->response->setStatus(200);
            $app->response()->headers->set('Content-Type', 'application/json');
            echo json_encode($all);
            $conn = null;
        } else {
            $app->response->setStatus(200);
            echo "no addresses found.";
        }

    } catch(PDOException $e) {
        $app->response()->setStatus(404);
        echo '{"error":{"text":'. $e->getMessage() .'}}';
    }
});

$app->post('/add',function() use($app){
    
    $variables = $app->request->post();
    //getting all variables
    $name = $variables['name'];
    $email = $variables['email'];
    $address_line_1 = $variables['address_line_1'];
    $address_line_2 = $variables['address_line_2'];
    $mobile = $variables['mobile'];
    $city = $variables['city'];
    $state = $variables['state'];
    $pin = $variables['pin'];

    try{
        $conn = createconn();
        //checking if same email address already registered(as email is primary key)
        $sqlc = $conn->prepare("Select * from address where email = :email");
        $sqlc->bindParam(':email', $email, PDO::PARAM_INT);
        $sqlc->execute();
        $all = $sqlc->fetch(PDO::FETCH_OBJ);

        //adding new address
        if($all){
            $app->response()->setStatus(200);
            echo "Sorry, this email id has already been registered.";
        }
        else{
            $app->response()->setStatus(200);
            $q = "insert into address(name, email, address_line_1, address_line_2, mobile, city, state, pin) values(?,?,?,?,?,?,?,?) ";
            $sql = $conn->prepare($q)->execute([$name, $email, $address_line_1, $address_line_2, $mobile, $city, $state, $pin]);
            echo "Success.";
        }
        $conn = null;

    } catch(PDOException $e){
        $app->response()->setStatus(404);
        echo '{"error":{"text":'. $e->getMessage() .'}}';
    }
});

$app->run();


?>