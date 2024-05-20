# Consolidation App

## Descrição

Esse projeto tem como objetivo confrontar a validar as informações de movimentação de vendas das 4 principais tabelas de movimentação de venda:
- ckpt_nfce_itens
- ckpt_nfce_pag
- ckpt_conta_pagamento
- ckpt_conta_detalhe
Ele busca todos os num_controle com status 2 e depois vai buscar os registros nas tabelas de movimentação de venda e confrontar as informações.

## Tecnologias Utilizadas

- PHP
- JQuery
- Tailwind CSS
- Docker

## Instalação

Para construir e executar o projeto usando Docker, tendo o docker desktop instalado em seu computador abra o projeto no terminal e execute o seguinte comando:

```bash
docker-compose up --build
```

## Acessar o projeto
Para acessar basta acessar a url  `http://localhost` no seu navegador.


