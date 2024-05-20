<?php

$dbHostLocal = getenv('DB_HOST_LOCAL');
$dbUsernameLocal = getenv('DB_USERNAME_LOCAL');
$dbPasswordLocal = getenv('DB_PASSWORD_LOCAL');
$dbNameLocal = getenv('DB_DATABASE_LOCAL');

try {

    $pdoLocal = new PDO("mysql:host=$dbHostLocal;dbname=$dbNameLocal", $dbUsernameLocal, $dbPasswordLocal);
    $pdoLocal->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $sql = "SELECT *
            FROM consolidado
            WHERE sum_nfce_itens <> sum_pagconta
            OR sum_nfce_itens <> sum_nfce_pag
            OR sum_pagconta <> sum_nfce_pag";
    $stmt = $pdoLocal->prepare($sql);
    $stmt->execute();
    $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $numEntradas = count($resultados);

    $docs = [];
    $numControles = [];

    $response = [];

    if ($numEntradas > 0) {
        foreach ($resultados as $row) {
            $response['data'][] = [
                'num_controle' => $row['num_controle'],
                'cod_doc' => $row['cod_doc'],
                'sum_nfce_pag' => $row['sum_nfce_pag'],
                'sum_nfce_itens' => $row['sum_nfce_itens'],
                'sum_pagconta' => $row['sum_pagconta'],
                'sum_detconta' => $row['sum_detconta'],
            ];

            $docs[] = $row['cod_doc'];
            $numControles[] = $row['num_controle'];
        }

        $docsString = implode(", ", $docs);
        $numControlesString = implode(", ", $numControles);

        header('Content-Type: application/json; charset=utf-8');
        if($numEntradas == 1) {
            echo json_encode([
                'message' => "Foi encontrada 1 divergência.",
                'data' => $response['data'],
                'docs' => $docsString,
                'num_controles' => $numControlesString
            ]);
        } else {
            echo json_encode([
                'message' => "Foram encontradas $numEntradas divergências.",
                'data' => $response['data'],
                'cod_docs' => $docsString,
                'num_controles' => $numControlesString
            ]);
        }
    } else {

        http_response_code(200);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['message' => 'Nenhuma diferença encontrada', 'data' => [], 'docs' => [], 'num_controles' => []]);
    }

} catch (PDOException $e) {
    http_response_code(500);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['error' => $e->getMessage()]);
}

?>
