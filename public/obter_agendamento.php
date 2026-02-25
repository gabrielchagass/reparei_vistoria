<?php
// Conexão com o banco de dados
require_once('conexao.php');


// Verificar se foi fornecido um ID válido
if (isset($_GET['id'])) {
  $agendamentoId = $_GET['id'];

  // Consulta SQL para obter o agendamento pelo ID
  $sql = "SELECT * FROM agendamentos WHERE id = $agendamentoId";
  $result = $conn->query($sql);

  if ($result->num_rows > 0) {
    $agendamento = $result->fetch_assoc();

    // Consulta SQL para obter os proprietários do agendamento
    $sqlProprietarios = "SELECT c.* FROM clientes c INNER JOIN pro_agendamento_cliente pac ON c.id = pac.id_cliente WHERE pac.id_agendamento = $agendamentoId";
    $resultProprietarios = $conn->query($sqlProprietarios);
    $proprietarios = [];
    while ($proprietario = $resultProprietarios->fetch_assoc()) {
      $proprietarios[] = $proprietario;
    }

    // Consulta SQL para obter os inquilinos do agendamento
    $sqlInquilinos = "SELECT c.* FROM clientes c INNER JOIN inq_agendamento_cliente iac ON c.id = iac.id_cliente WHERE iac.id_agendamento = $agendamentoId";
    $resultInquilinos = $conn->query($sqlInquilinos);
    $inquilinos = [];
    while ($inquilino = $resultInquilinos->fetch_assoc()) {
      $inquilinos[] = $inquilino;
    }

    // Consulta SQL para obter os fiadores do agendamento
    $sqlFiadores = "SELECT c.* FROM clientes c INNER JOIN fia_agendamento_cliente fac ON c.id = fac.id_cliente WHERE fac.id_agendamento = $agendamentoId";
    $resultFiadores = $conn->query($sqlFiadores);
    $fiadores = [];
    while ($fiador = $resultFiadores->fetch_assoc()) {
      $fiadores[] = $fiador;
    }

    // Construir um array com os dados do agendamento e relacionamentos
    $agendamentoData = [
      'id' => $agendamento['id'],
      'vistoriador' => $agendamento['vistoriador'],
      'contrato_id' => $agendamento['contrato_id'],
      'contrato_cod' => $agendamento['contrato_cod'],
      'solicitante' => $agendamento['solicitante'],
      'aluguel_valor' => $agendamento['aluguel_valor'],
      'imovel_endereco' => $agendamento['imovel_endereco'],
      'imovel_numero' => $agendamento['imovel_numero'],
      'imovel_complemento' => $agendamento['imovel_complemento'],
      'imovel_bairro' => $agendamento['imovel_bairro'],
      'imovel_cidade' => $agendamento['imovel_cidade'],
      'imovel_uf' => $agendamento['imovel_uf'],
      'data_agendamento' => $agendamento['data_agendamento'],
      'prazo_inicio' => $agendamento['prazo_inicio'],
      'prazo_fim' => $agendamento['prazo_fim'],
      'prazo_dinamico' => $agendamento['prazo_dinamico'],
      'proprietarios' => $proprietarios,
      'inquilinos' => $inquilinos,
      'fiadores' => $fiadores,
      'ch_local' => $agendamento['ch_local'],
      'padrao_vistoria' => $agendamento['padrao_vistoria'],
      'imovel_tamanho_id' => $agendamento['imovel_tamanho_id'],
      'imovel_mobiliado' => $agendamento['imovel_mobiliado'],
      'ch_qtd_controle' => $agendamento['ch_qtd_controle'],
      'ch_qtd_cartao' => $agendamento['ch_qtd_cartao'],
      'ch_qtd_tag' => $agendamento['ch_qtd_tag'],
      'ch_qtd_correio' => $agendamento['ch_qtd_correio'],
      'ch_qtd_carrinho' => $agendamento['ch_qtd_carrinho'],
      'created_at' => $agendamento['created_at'],
      'nome_cliente' => $agendamento['nome_cliente'],
      'whatsapp' => $agendamento['whatsapp'],
      'obs_contato' => $agendamento['obs_contato'],
      'termoassinado' => $agendamento['termoassinado'],
      'feitopadrao' => $agendamento['feitopadrao'],
      'testesrealizados' => $agendamento['testesrealizados'],
      'descricaopendencias' => $agendamento['descricaopendencias']
    ];

    // Retornar os dados do agendamento como JSON
    echo json_encode($agendamentoData);
  }
}

// Fechar conexão com o banco de dados
$conn->close();
?>
