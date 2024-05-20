<?php

// Consulta para o primeiro banco de dados
$sql = "SELECT num_controle, estabelecimento, SUM(valor) as total FROM ckpt_conta_pagamento WHERE estabelecimento = :estabelecimento AND num_controle = :num_controle GROUP BY num_controle, estabelecimento";

foreach ($resultadosDocs as $resultadoDoc) {
    $estabelecimento = $resultadoDoc['estabelecimento'];
    $num_controle = $resultadoDoc['num_controle'];

    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':estabelecimento', $estabelecimento, PDO::PARAM_STR);
    $stmt->bindParam(':num_controle', $num_controle, PDO::PARAM_STR);
    $stmt->execute();

    // Atualizar a tabela consolidado para o segundo banco de dados
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

?>
