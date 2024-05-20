CREATE TABLE `consolidado` (
                               `gen` INT(10) NOT NULL AUTO_INCREMENT,
                               `num_controle` VARCHAR(50) NULL DEFAULT NULL COLLATE 'utf8mb4_0900_ai_ci',
                               `doc` VARCHAR(50) NULL DEFAULT NULL COLLATE 'utf8mb4_0900_ai_ci',
                               `cod_doc` VARCHAR(50) NULL DEFAULT NULL COLLATE 'utf8mb4_0900_ai_ci',
                               `sum_nfce_pag` DOUBLE NULL DEFAULT NULL,
                               `sum_nfce_itens` DOUBLE NULL DEFAULT NULL,
                               `sum_pagconta` DOUBLE NULL DEFAULT NULL,
                               `sum_detconta` DOUBLE NULL DEFAULT NULL,
                               `estabelecimento` VARCHAR(50) NULL DEFAULT NULL COLLATE 'utf8mb4_0900_ai_ci',
                               PRIMARY KEY (`gen`) USING BTREE,
                               INDEX `num_controle` (`num_controle`) USING BTREE,
                               INDEX `doc` (`doc`) USING BTREE,
                               INDEX `cod_doc` (`cod_doc`) USING BTREE,
                               INDEX `sum_nfce_pag` (`sum_nfce_pag`) USING BTREE,
                               INDEX `sum_nfce_itens` (`sum_nfce_itens`) USING BTREE,
                               INDEX `sum_pagconta` (`sum_pagconta`) USING BTREE,
                               INDEX `sum_detconta` (`sum_detconta`) USING BTREE,
                               INDEX `estabelecimento` (`estabelecimento`) USING BTREE
)
    COLLATE='utf8mb4_0900_ai_ci'
    ENGINE=InnoDB
;
