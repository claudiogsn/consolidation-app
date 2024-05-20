<?php

// Consulta para o primeiro banco de dados
$sql = "SELECT cod_doc, estabelecimento, SUM(valor_total - valor_desconto ) as total FROM ckpt_nfce_itens WHERE estabelecimento = :estabelecimento AND cod_doc = :cod_doc GROUP BY cod_doc, estabelecimento";

foreach ($resultadosDocs as $resultadoDoc) {
    $estabelecimento = $resultadoDoc['estabelecimento'];
    $cod_doc = $resultadoDoc['cod_doc'];

    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':estabelecimento', $estabelecimento, PDO::PARAM_STR);
    $stmt->bindParam(':cod_doc', $cod_doc, PDO::PARAM_STR);
    $stmt->execute();

    // Atualizar a tabela consolidado para o segundo banco de dados
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

?>
