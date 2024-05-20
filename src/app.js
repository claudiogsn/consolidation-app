const consultaForm = document.getElementById('consultaForm');
const loadingModal = document.getElementById('loadingModal');
const loadingSubtitle = document.getElementById('loadingSubtitle');
const loadingTitle = document.getElementById('loadingTitle');
const alertBox = document.getElementById('alert');
const alertDanger = document.getElementById('alertDanger');
const result = document.getElementById('result');
const tableBody = document.getElementById('tableBody');
const nomeFantasia = document.getElementById('nomeFantasia');
const dtMov = document.getElementById('dtMov');
const idEstabelecimento = document.getElementById('idEstabelecimento');
const CodDocs = document.getElementById('CodDocs');
const NumControles = document.getElementById('NumControles');

const baseURL = `${window.location.protocol}//${window.location.host}`;


function copyToClipboard(text) {
    navigator.clipboard.writeText(text).then(() => {
        alert(`Comando SQL copiado para o clipboard: ${text}`);
    }).catch(err => {
        console.error('Erro ao copiar para o clipboard:', err);
    });
}

document.getElementById('tableBody').addEventListener('click', event => {
    const clickedElement = event.target;
    const estabelecimento = document.getElementById('estabelecimento').value.trim();

    if (clickedElement.classList.contains('clickable-controle')) {
        const numControle = clickedElement.textContent;
        const sqlCommand = `SELECT * FROM ckpt_conta WHERE estabelecimento = ${estabelecimento} AND num_controle = ${numControle}`;
        copyToClipboard(sqlCommand);
    } else if (clickedElement.classList.contains('clickable-doc')) {
        const doc = clickedElement.textContent;
        const sqlCommand = `SELECT * FROM ckpt_nfce WHERE estabelecimento = ${estabelecimento} AND doc = ${doc}`;
        copyToClipboard(sqlCommand);
    }
});


consultaForm.addEventListener('submit', async function(event) {
    event.preventDefault();

    const estabelecimento = document.getElementById('estabelecimento').value.trim();
    const data = document.getElementById('data').value.trim();

    if (!estabelecimento || !data) {
        alert('Por favor, preencha todos os campos.');
        return;
    }

    loadingSubtitle.textContent = 'Iniciando Processamento...';

    loadingModal.classList.remove('hidden');

    try {
        const response = await axios.get(`${baseURL}/api/getDocs.php?estabelecimento=${estabelecimento}&dt_mov=${data}`);
        if (response.status === 200) {
            const responseDataDocs = response.data;
            console.log("Docs Message:", responseDataDocs.message);
            const idestabelecimento = responseDataDocs.idestabelecimento;
            const dt_mov = responseDataDocs.dt_mov;
            const nome_fantasia = responseDataDocs.nome_fantasia;
            loadingTitle.textContent = responseDataDocs.message;
            loadingSubtitle.textContent = 'Processando Conta Detalhe...';

            const processResponse = await axios.get(`${baseURL}/api/process.php?table=detconta`);
            if (processResponse.status === 200) {
                const responseDataDetConta = processResponse.data;
                console.log("DetConta Message:", responseDataDetConta.message);
                loadingTitle.textContent = responseDataDetConta.message;
                loadingSubtitle.textContent = 'Processando Conta Pagamento...';

                const processPagamentoResponse = await axios.get(`${baseURL}/api/process.php?table=pagconta`);
                if (processPagamentoResponse.status === 200) {
                    const responseDataPagConta = processPagamentoResponse.data;
                    console.log("PagConta Message:", responseDataPagConta.message);
                    loadingTitle.textContent = responseDataPagConta.message;
                    loadingSubtitle.textContent = 'Processando NFCe Itens...';

                    const processItensResponse = await axios.get(`${baseURL}/api/process.php?table=nfceitens`);
                    if (processItensResponse.status === 200) {
                        const responseDataNfceItens = processItensResponse.data;
                        console.log("NfceItens Message:", responseDataNfceItens.message);
                        loadingTitle.textContent = responseDataNfceItens.message;
                        loadingSubtitle.textContent = 'Processando NFCe Pagmentos...';

                        const processPagamentosResponse = await axios.get(`${baseURL}/api/process.php?table=nfcepag`);
                        if (processPagamentosResponse.status === 200) {
                            const responseDataNfcePag = processPagamentosResponse.data;
                            console.log("NfcePag Message:", responseDataNfcePag.message);
                            loadingTitle.textContent = responseDataNfcePag.message;
                            loadingSubtitle.textContent = 'Processando Divergências...';

                            const divergenciasResponse = await axios.get(`${baseURL}/api/diference.php`);
                            if (divergenciasResponse.status === 200) {
                                const { message, data } = divergenciasResponse.data;
                                const cod_docs = divergenciasResponse.data.cod_docs;
                                const num_controles = divergenciasResponse.data.num_controles;
                                console.log("Divergencias Message:", message);
                                alertBox.textContent = message;
                                alertBox.classList.remove('hidden');

                                if (data.length > 0) {
                                    nomeFantasia.textContent = nome_fantasia;
                                    dtMov.textContent = dt_mov;
                                    idEstabelecimento.textContent = idestabelecimento;
                                    CodDocs.textContent = cod_docs;
                                    NumControles.textContent = num_controles;
                                    tableBody.innerHTML = '';
                                    data.forEach(item => {
                                        const row = document.createElement('tr');
                                        row.innerHTML = `
                                            <td class="border px-4 py-2 clickable clickable-controle" >${item.num_controle}</td>
                                            <td class="border px-4 py-2 clickable clickable-doc" >${item.cod_doc}</td>
                                            <td class="border px-4 py-2">${item.sum_nfce_pag}</td>
                                            <td class="border px-4 py-2">${item.sum_nfce_itens}</td>
                                            <td class="border px-4 py-2">${item.sum_pagconta}</td>
                                            <td class="border px-4 py-2">${item.sum_detconta}</td>
                                        `;
                                        tableBody.appendChild(row);
                                    });
                                    result.classList.remove('hidden');
                                    alertDanger.classList.add('hidden');
                                } else {
                                    result.classList.add('hidden');
                                    alertBox.classList.add('hidden');
                                    alertDanger.textContent = message;
                                    alertDanger.classList.remove('hidden');
                                }
                            } else {
                                showError('Erro ao buscar divergências.');
                            }
                        } else {
                            showError('Erro ao processar NFCE Pagamentos.');
                        }
                    } else {
                        showError('Erro ao processar NFCE Itens.');
                    }
                } else {
                    showError('Erro ao processar Pagamento de Conta.');
                }
            } else {
                showError('Erro ao processar Conta Detalhe.');
            }
        } else {
            showError('Erro ao buscar documentos.');
        }
    } catch (error) {
        showError('Erro inesperado. Por favor, tente novamente mais tarde.');
    } finally {
        loadingModal.classList.add('hidden');
    }
});

function showError(message) {
    alertDanger.textContent = message;
    alertDanger.classList.remove('hidden');
    result.classList.add('hidden');
    alertBox.classList.add('hidden');
}
