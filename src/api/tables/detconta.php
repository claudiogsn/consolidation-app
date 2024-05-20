<?php

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

    echo "Valores atualizados com sucesso.";

} catch (PDOException $e) {
    echo "Erro: " . $e->getMessage();
}

?>
