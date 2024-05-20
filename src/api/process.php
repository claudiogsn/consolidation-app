<?php
header('Content-Type: application/json; charset=utf-8');


$dbHost = getenv('DB_HOST');
$dbUsername = getenv('DB_USERNAME');
$dbPassword = getenv('DB_PASSWORD');
$dbName = getenv('DB_DATABASE');


$dbHostLocal = getenv('DB_HOST_LOCAL');
$dbUsernameLocal = getenv('DB_USERNAME_LOCAL');
$dbPasswordLocal = getenv('DB_PASSWORD_LOCAL');
$dbNameLocal = getenv('DB_DATABASE_LOCAL');


$table = $_GET['table'] ?? null;

if (!$table) {
    http_response_code(400);
    echo json_encode(["error" => "O parâmetro 'table' é obrigatório."]);
    exit();
}

try {

    $pdoLocal = new PDO("mysql:host=$dbHostLocal;dbname=$dbNameLocal", $dbUsernameLocal, $dbPasswordLocal);
    $pdoLocal->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);


    $pdo = new PDO("mysql:host=$dbHost;dbname=$dbName", $dbUsername, $dbPassword);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);


    function processDetConta($pdo, $pdoLocal) {
        try {
            $sqlDocs = "SELECT num_controle, estabelecimento FROM consolidado";
            $stmtDocs = $pdoLocal->prepare($sqlDocs);
            $stmtDocs->execute();
            $resultadosDocs = $stmtDocs->fetchAll(PDO::FETCH_ASSOC);

            $sql = "SELECT num_controle, estabelecimento, SUM(nquant*npreco) as total FROM ckpt_conta_detalhe WHERE estabelecimento = :estabelecimento AND num_controle = :num_controle GROUP BY num_controle, estabelecimento";

            foreach ($resultadosDocs as $resultadoDoc) {
                $estabelecimento = $resultadoDoc['estabelecimento'];
                $num_controle = $resultadoDoc['num_controle'];

                $stmt = $pdo->prepare($sql);
                $stmt->bindParam(':estabelecimento', $estabelecimento, PDO::PARAM_STR);
                $stmt->bindParam(':num_controle', $num_controle, PDO::PARAM_STR);
                $stmt->execute();

                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    $total = $row['total'];
                    $updateSql = "UPDATE consolidado SET sum_detconta = :total WHERE num_controle = :num_controle AND estabelecimento = :estabelecimento";
                    $updateStmt = $pdoLocal->prepare($updateSql);
                    $updateStmt->bindParam(':total', $total, PDO::PARAM_INT);
                    $updateStmt->bindParam(':num_controle', $num_controle, PDO::PARAM_STR);
                    $updateStmt->bindParam(':estabelecimento', $estabelecimento, PDO::PARAM_STR);
                    $updateStmt->execute();
                }
            }

            return ["status" => 200, "message" => "Done."];

        } catch (PDOException $e) {
            return ["status" => 500, "error" => "Erro: " . $e->getMessage()];
        }
    }
    function processPagConta($pdo, $pdoLocal) {
        try {
            $sqlDocs = "SELECT num_controle, estabelecimento FROM consolidado";
            $stmtDocs = $pdoLocal->prepare($sqlDocs);
            $stmtDocs->execute();
            $resultadosDocs = $stmtDocs->fetchAll(PDO::FETCH_ASSOC);

            $sql = "SELECT num_controle, estabelecimento, SUM(valor) as total FROM ckpt_conta_pagamento WHERE estabelecimento = :estabelecimento AND num_controle = :num_controle GROUP BY num_controle, estabelecimento";

            foreach ($resultadosDocs as $resultadoDoc) {
                $estabelecimento = $resultadoDoc['estabelecimento'];
                $num_controle = $resultadoDoc['num_controle'];

                $stmt = $pdo->prepare($sql);
                $stmt->bindParam(':estabelecimento', $estabelecimento, PDO::PARAM_STR);
                $stmt->bindParam(':num_controle', $num_controle, PDO::PARAM_STR);
                $stmt->execute();

                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    $total = $row['total'];
                    $updateSql = "UPDATE consolidado SET sum_pagconta = :total WHERE num_controle = :num_controle AND estabelecimento = :estabelecimento";
                    $updateStmt = $pdoLocal->prepare($updateSql);
                    $updateStmt->bindParam(':total', $total, PDO::PARAM_INT);
                    $updateStmt->bindParam(':num_controle', $num_controle, PDO::PARAM_STR);
                    $updateStmt->bindParam(':estabelecimento', $estabelecimento, PDO::PARAM_STR);
                    $updateStmt->execute();
                }
            }

            return ["status" => 200, "message" => "Done."];

        } catch (PDOException $e) {
            return ["status" => 500, "error" => "Erro: " . $e->getMessage()];
        }
    }

    function processNfcePag($pdo, $pdoLocal) {
        try {
            $sqlDocs = "SELECT cod_doc, estabelecimento FROM consolidado";
            $stmtDocs = $pdoLocal->prepare($sqlDocs);
            $stmtDocs->execute();
            $resultadosDocs = $stmtDocs->fetchAll(PDO::FETCH_ASSOC);

            $sql = "SELECT cod_doc, estabelecimento, SUM(valor_total - valor_desconto ) as total FROM ckpt_nfce_itens WHERE estabelecimento = :estabelecimento AND cod_doc = :cod_doc GROUP BY cod_doc, estabelecimento";

            foreach ($resultadosDocs as $resultadoDoc) {
                $estabelecimento = $resultadoDoc['estabelecimento'];
                $cod_doc = $resultadoDoc['cod_doc'];

                $stmt = $pdo->prepare($sql);
                $stmt->bindParam(':estabelecimento', $estabelecimento, PDO::PARAM_STR);
                $stmt->bindParam(':cod_doc', $cod_doc, PDO::PARAM_STR);
                $stmt->execute();

                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    $total = $row['total'];
                    $updateSql = "UPDATE consolidado SET sum_nfce_pag = :total WHERE cod_doc = :cod_doc AND estabelecimento = :estabelecimento";
                    $updateStmt = $pdoLocal->prepare($updateSql);
                    $updateStmt->bindParam(':total', $total, PDO::PARAM_INT);
                    $updateStmt->bindParam(':cod_doc', $cod_doc, PDO::PARAM_STR);
                    $updateStmt->bindParam(':estabelecimento', $estabelecimento, PDO::PARAM_STR);
                    $updateStmt->execute();
                }
            }

            return ["status" => 200, "message" => "Done."];

        } catch (PDOException $e) {
            return ["status" => 500, "error" => "Erro: " . $e->getMessage()];
        }
    }
    function processNfceItens($pdo, $pdoLocal) {
        try {
            $sqlDocs = "SELECT cod_doc, estabelecimento FROM consolidado";
            $stmtDocs = $pdoLocal->prepare($sqlDocs);
            $stmtDocs->execute();
            $resultadosDocs = $stmtDocs->fetchAll(PDO::FETCH_ASSOC);

            $sql = "SELECT cod_doc, estabelecimento, SUM(valor) as total FROM ckpt_nfce_pag WHERE estabelecimento = :estabelecimento AND cod_doc = :cod_doc GROUP BY cod_doc, estabelecimento";

            foreach ($resultadosDocs as $resultadoDoc) {
                $estabelecimento = $resultadoDoc['estabelecimento'];
                $cod_doc = $resultadoDoc['cod_doc'];

                $stmt = $pdo->prepare($sql);
                $stmt->bindParam(':estabelecimento', $estabelecimento, PDO::PARAM_STR);
                $stmt->bindParam(':cod_doc', $cod_doc, PDO::PARAM_STR);
                $stmt->execute();

                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    $total = $row['total'];
                    $updateSql = "UPDATE consolidado SET sum_nfce_itens = :total WHERE cod_doc = :cod_doc AND estabelecimento = :estabelecimento";
                    $updateStmt = $pdoLocal->prepare($updateSql);
                    $updateStmt->bindParam(':total', $total, PDO::PARAM_INT);
                    $updateStmt->bindParam(':cod_doc', $cod_doc, PDO::PARAM_STR);
                    $updateStmt->bindParam(':estabelecimento', $estabelecimento, PDO::PARAM_STR);
                    $updateStmt->execute();
                }
            }

            return ["status" => 200, "message" => "Done."];

        } catch (PDOException $e) {
            return ["status" => 500, "error" => "Erro: " . $e->getMessage()];
        }
    }

    switch ($table) {
        case 'detconta':
            processDetConta($pdo, $pdoLocal);
            break;
        case 'pagconta':
            processPagConta($pdo, $pdoLocal);
            break;
        case 'nfcepag':
            processNfcePag($pdo, $pdoLocal);
            break;
        case 'nfceitens':
            processNfceItens($pdo, $pdoLocal);
            break;
        default:
            http_response_code(400);
            echo json_encode(["error" => "Tabela desconhecida."]);
            exit();
    }

    http_response_code(200);
    echo json_encode(["message" => "Processamento da $table concluído com sucesso."]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["error" => "Erro: " . $e->getMessage()]);
}


$pdo = null;
$pdoLocal = null;
?>

