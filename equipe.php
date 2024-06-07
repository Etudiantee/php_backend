<?php
session_start();
header("Access-Control-Allow-Origin: *");

$host = "localhost"; 
$user = "root"; 
$password = ""; 
$dbname = "testingdb"; 
$id = '';
$matricule = '';

$con = mysqli_connect($host, $user, $password, $dbname);

$method = $_SERVER['REQUEST_METHOD'];

if (!$con) {
  die("Connection failed: " . mysqli_connect_error());
}

switch ($method) {
    case 'GET':
      if(isset($_GET["id"])){
        $id = $_GET['id'];
      }
      if(isset($_GET["matricule"])){
        $matricule = $_GET['matricule'];
        $sql = "SELECT id, CD , Matricule , `key`, VM , VL FROM equipe WHERE Matricule = '$matricule'";
      } else {
        $sql = "SELECT id, CD , Matricule , `key`, VM , VL FROM equipe" . ($id ? " WHERE id = $id" : '');
      }
      break;

    case 'POST':
        $data = json_decode(file_get_contents("php://input"), true);

        if (isset($_GET["id"])) {
          $id = $_GET['id'];
          $nom = $_POST["nom"];
          $prenom = $_POST["prenom"];
          $matricule = $_POST["matricule"];
          $base = $_POST["base"];
          $secteur = $_POST["secteur"];
          $college = $_POST["college"];
          $sql = "UPDATE equipe SET CD='$nom', Matricule='$prenom', `key`='$base', VM='$secteur', VL='$college' WHERE id = $id";
      } else if (isset($_GET["delete"])){
            $delete = $_GET['delete'];
            $sql = "DELETE FROM equipe WHERE id = $delete"; 
        } else {
          $nom = $_POST["nom"];
          $prenom = $_POST["prenom"];
          $matricule = $_POST["matricule"];
          $base = $_POST["base"];
          $secteur = $_POST["secteur"];
          $college = $_POST["college"];
 
          $sql = "INSERT INTO equipe (CD, Matricule, `key`, VM, VL) VALUES ('$nom', '$prenom', '$base', '$secteur', '$college')"; 
        }
      break;
}

$result = mysqli_query($con, $sql);

if (!$result) {
  http_response_code(404);
  die(mysqli_error($con));
}

if ($method == 'GET') {
    if (!$id && !$matricule) echo '[';
      for ($i=0; $i<mysqli_num_rows($result); $i++) {
        echo ($i > 0 ? ',' : '') . json_encode(mysqli_fetch_object($result));
      }
    if (!$id && !$matricule) echo ']';
} elseif ($method == 'POST') {
    echo json_encode($result);
} else {
    echo mysqli_affected_rows($con);
}

$con->close();
?>
