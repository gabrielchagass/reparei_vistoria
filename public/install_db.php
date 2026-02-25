<?php
require_once 'conexao.php';

// Verifica se as tabelas já existem
$tables_exist = false;
$tables_check_sql = "SHOW TABLES LIKE 'agendamentos'";
$tables_check_result = $conn->query($tables_check_sql);

if ($tables_check_result->num_rows > 0) {
    $tables_exist = true;
}

// Cria as tabelas apenas se elas não existirem
if (!$tables_exist) {
    // Criação das tabelas...

    // Cria a tabela "agendamentos"
    $sql_agendamentos = "CREATE TABLE agendamentos (
        id INT AUTO_INCREMENT PRIMARY KEY,
        contrato_id INT UNSIGNED,
        contrato_cod VARCHAR(11),
        status_id INT UNSIGNED DEFAULT 1,
        solicitante VARCHAR(100),
        aluguel_valor FLOAT,
        imovel_endereco VARCHAR(150),
        imovel_numero INT UNSIGNED,
        imovel_complemento VARCHAR(15),
        imovel_bairro VARCHAR(50),
        imovel_cidade VARCHAR(25),
        imovel_uf VARCHAR(2),
        imovel_condominio VARCHAR(100),
        imovel_tamanho_id INT UNSIGNED,
        imovel_mobiliado ENUM('nao','semi','mobiliado')
        data_agendamento DATETIME,
        prazo_inicio DATETIME,
        prazo_fim DATETIME,
        created_at DATETIME,
        updated_at DATETIME,
        vistoriador INT UNSIGNED,
        data_fim DATETIME,
        duracao INT,
        data_inicio DATETIME,
        imovel_disponivel BOOLEAN,
        disponibilidade_motivo VARCHAR(250),
        ch_qtd_controle INT UNSIGNED,
        ch_local ENUM('gaveta', 'quadro', 'portaria'),
        ch_cod_chaveiro INT UNSIGNED,
        ch_qtd_cartao INT UNSIGNED,
        ch_qtd_tag INT UNSIGNED,
        ch_qtd_correio INT UNSIGNED,
        ch_qtd_carrinho INT UNSIGNED,
        padrao_vistoria ENUM('digital', 'fisico')
    )";
    if ($conn->query($sql_agendamentos) === TRUE) {
        echo "Tabela 'agendamentos' criada com sucesso.<br>";
    } else {
        echo "Erro ao criar a tabela 'agendamentos': " . $conn->error . "<br>";
    }

    // Cria a tabela "clientes"
    $sql_clientes = "CREATE TABLE clientes (
        id INT AUTO_INCREMENT PRIMARY KEY,
        nome VARCHAR(100)
    )";
    if ($conn->query($sql_clientes) === TRUE) {
        echo "Tabela 'clientes' criada com sucesso.<br>";
    } else {
        echo "Erro ao criar a tabela 'clientes': " . $conn->error . "<br>";
    }

    // Cria a tabela "pro_agendamento_cliente"
    $sql_pro_agendamento_cliente = "CREATE TABLE pro_agendamento_cliente (
        id INT AUTO_INCREMENT PRIMARY KEY,
        id_agendamento INT,
        id_cliente INT,
        FOREIGN KEY (id_agendamento) REFERENCES agendamentos(id),
        FOREIGN KEY (id_cliente) REFERENCES clientes(id)
    )";
    if ($conn->query($sql_pro_agendamento_cliente) === TRUE) {
        echo "Tabela 'pro_agendamento_cliente' criada com sucesso.<br>";
    } else {
        echo "Erro ao criar a tabela 'pro_agendamento_cliente': " . $conn->error . "<br>";
    }

    // Cria a tabela "inq_agendamento_cliente"
    $sql_inq_agendamento_cliente = "CREATE TABLE inq_agendamento_cliente LIKE pro_agendamento_cliente";
    if ($conn->query($sql_inq_agendamento_cliente) === TRUE) {
        echo "Tabela 'inq_agendamento_cliente' criada com sucesso.<br>";
    } else {
        echo "Erro ao criar a tabela 'inq_agendamento_cliente': " . $conn->error . "<br>";
    }

    // Cria a tabela "fia_agendamento_cliente"
    $sql_fia_agendamento_cliente = "CREATE TABLE fia_agendamento_cliente LIKE inq_agendamento_cliente";
    if ($conn->query($sql_fia_agendamento_cliente) === TRUE) {
        echo "Tabela 'fia_agendamento_cliente' criada com sucesso.<br>";
    } else {
        echo "Erro ao criar a tabela 'fia_agendamento_cliente': " . $conn->error . "<br>";
    }
} else {
    echo "As tabelas já existem.<br>";
}

// Verifica se a tabela já existe no banco de dados
if ($conn->query("SHOW TABLES LIKE 'logs_agendamentos'")->num_rows == 0) {
    // Cria a tabela logs_agendamentos
    $sql = "CREATE TABLE logs_agendamentos (
        id INT(11) AUTO_INCREMENT PRIMARY KEY,
        agendamento_id INT(11) NOT NULL,
        data_antiga TEXT,
        data_nova TEXT,
        campos_alterados VARCHAR(255),
        data_hora DATETIME
    )";

    // Executa a consulta SQL para criar a tabela
    if ($conn->query($sql) === TRUE) {
        echo "Tabela logs_agendamentos criada com sucesso.";
    } else {
        echo "Erro ao criar tabela: " . $conn->error;
    }
} else {
    echo "A tabela logs_agendamentos já existe no banco de dados.";
}


// Verifica se a tabela já existe no banco de dados
if ($conn->query("SHOW TABLES LIKE 'cargos'")->num_rows == 0) {
    // Cria a tabela cargos
    $sql = "CREATE TABLE cargos (
        id INT(11) AUTO_INCREMENT PRIMARY KEY,
        email varchar(50) NOT NULL,
        cargo varchar(50) NOT NULL,
        devolus_id INT UNSIGNED
    )";

    // Executa a consulta SQL para criar a tabela
    if ($conn->query($sql) === TRUE) {
        echo "Tabela cargos criada com sucesso.";
    } else {
        echo "Erro ao criar tabela: " . $conn->error;
    }
} else {
    echo "A tabela cargos já existe no banco de dados.";
}


// Verifica se a tabela já existe no banco de dados
if ($conn->query("SHOW TABLES LIKE 'agendamento_status'")->num_rows == 0) {
    // Cria a tabela cargos
    $sql = "CREATE TABLE agendamento_status (
        id INT(11) AUTO_INCREMENT PRIMARY KEY,
        nome varchar(50) NOT NULL
    )";

    // Executa a consulta SQL para criar a tabela
    if ($conn->query($sql) === TRUE) {
        echo "Tabela cargos criada com sucesso.";

        // Dados a serem inseridos
        $dados = array(
            array(1, 'aguardando'),
            array(2, 'em transito'),
            array(3, 'iniciado'),
            array(4, 'concluído'),
            array(5, 'cancelado')
        );
        foreach ($dados as $dado) {
            $id = $dado[0];
            $status = $dado[1];
            // Monta a consulta SQL com os dados inseridos diretamente
            $sql = "INSERT INTO agendamento_status (id, nome) VALUES ($id, '$status')";
            // Executa a consulta
            if ($conn->query($sql) === true) {
                echo "Dados inseridos com sucesso!";
            } else {
                echo "Erro ao inserir dados: " . $conn->error;
            }
        }

    } else {
        echo "Erro ao criar tabela: " . $conn->error;
    }
} else {
    echo "A tabela cargos já existe no banco de dados.";
}

// Fecha a conexão com o banco de dados
$conn->close();
?>
