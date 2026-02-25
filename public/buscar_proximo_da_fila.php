<?php
$proxima=0;
$agora=date('Y-m-d');
$sql = "SELECT * FROM agendamentos WHERE solicitacao_bloqueada = 0 AND deleted_at IS NULL AND data_fim IS NULL AND (tipo_vistoria_id = 1) AND prazo_inicio <= '$agora' ORDER BY prazo_dinamico ASC LIMIT 1";
$result = $conn->query($sql);
while ($row = $result->fetch_assoc()) {
    $proxima=$row['id'];
}

?>
