<?php
header('Content-Type: application/json; charset=utf-8');

$host = getenv('DB_HOST');
$db = getenv('DB_DATABASE');
$user = getenv('DB_USERNAME');
$pass = getenv('DB_PASSWORD');

$host_local = getenv('DB_HOST_LOCAL');
$db_local = getenv('DB_DATABASE_LOCAL');
$user_local = getenv('DB_USERNAME_LOCAL');
$pass_local = getenv('DB_PASSWORD_LOCAL');

// Recebe os parâmetros por GET
$estabelecimento = $_GET['estabelecimento'] ?? null;
$dt_mov = $_GET['dt_mov'] ?? null;

// Verifica se os parâmetros foram enviados e não estão vazios
if (!isset($_GET['estabelecimento']) || empty($_GET['estabelecimento']) || !isset($_GET['dt_mov']) || empty($_GET['dt_mov'])) {
    http_response_code(400);
    echo json_encode(["error" => "Os parâmetros 'estabelecimento' e 'dt_mov' são obrigatórios e não podem estar vazios"]);
    exit();
}

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $sqlEstabelecimento = "SELECT idestabelecimento, nome_fantasia FROM estabelecimento WHERE idestabelecimento = :estabelecimento";
    $stmtEstabelecimento = $pdo->prepare($sqlEstabelecimento);
    $stmtEstabelecimento->bindParam(':estabelecimento', $estabelecimento, PDO::PARAM_INT);
    $stmtEstabelecimento->execute();
    $resultadosEstabelecimento = $stmtEstabelecimento->fetch(PDO::FETCH_ASSOC);
    $nome_fantasia = $resultadosEstabelecimento['nome_fantasia'];
    $id_estabelecimento = $resultadosEstabelecimento['idestabelecimento'];


    $data_formatada = date('d/m/Y', strtotime($dt_mov));

    $sqlDocNfce = "SELECT num_controle, doc FROM ckpt_nfce WHERE estabelecimento = :estabelecimento AND num_controle IN (SELECT num_controle FROM ckpt_conta WHERE estabelecimento = :estabelecimento AND dt_mov = :dt_mov AND status = 2)";
    $stmtDocNfce = $pdo->prepare($sqlDocNfce);
    $stmtDocNfce->bindParam(':estabelecimento', $estabelecimento, PDO::PARAM_INT);
    $stmtDocNfce->bindParam(':dt_mov', $dt_mov, PDO::PARAM_STR);
    $stmtDocNfce->execute();
    $resultadosDocNfce = $stmtDocNfce->fetchAll(PDO::FETCH_ASSOC);

    $pdoLocal = new PDO("mysql:host=$host_local;dbname=$db_local", $user_local, $pass_local);
    $pdoLocal->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $sqlDelete = "DELETE FROM consolidado where gen > 0";
    $stmtDelete = $pdoLocal->prepare($sqlDelete);
    $stmtDelete->execute();

    $registrosGravados = 0;
    foreach ($resultadosDocNfce as $resultadoDocNfce) {
        $sqlInsert = "INSERT INTO consolidado (num_controle, doc, cod_doc, estabelecimento) VALUES (:num_controle, :doc, :cod_doc, :estabelecimento)";
        $stmtInsert = $pdoLocal->prepare($sqlInsert);
        $stmtInsert->bindParam(':num_controle', $resultadoDocNfce['num_controle'], PDO::PARAM_STR);
        $stmtInsert->bindParam(':doc', $resultadoDocNfce['doc'], PDO::PARAM_STR);
        $stmtInsert->bindParam(':cod_doc', $resultadoDocNfce['doc'], PDO::PARAM_STR);
        $stmtInsert->bindParam(':estabelecimento', $estabelecimento, PDO::PARAM_STR);
        $stmtInsert->execute();
        $registrosGravados++;
    }

    http_response_code(200);
    echo json_encode([
        "message" => "Foram encontrados $registrosGravados controles para o estabelecimento: $nome_fantasia",
        "idestabelecimento" => $id_estabelecimento,
        "nome_fantasia" => $nome_fantasia,
        "dt_mov" => $data_formatada
    ]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["error" => "Erro: " . $e->getMessage()]);
}

$pdo = null;
$pdoLocal = null;
?>
