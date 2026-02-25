<?php

class Devolus{
    private $api_url='https://api.devolusvistoria.com.br';
    private $bearer_token='85178a16-cf9c-4937-a326-a0cb4999a793';
    private $vistoriador_id;
    private $locadores_dados;
    private $imovel_id;
    private $tipoVistoria_id;

    public function __construct(){

    }

    public function setVistoriador_id($valor){
        $this->vistoriador_id=$valor;
    }
    public function setImovel_id($valor){
        $this->imovel_id=$valor;
    }
    public function setLocadores_dados($valor){
        $this->locadores_dados=$valor;
    }
    public function setTipoVistoria_id($valor){
        if($valor==1){
            $this->tipoVistoria_id=30408; //entrada
        }else if($valor==2){
            $this->tipoVistoria_id=30412; //saida
        }else if($valor==3){
            $this->tipoVistoria_id=30407; //CAPTACAO
        }else{
            die('erro tipo de vistoria inválido');
        }
    }

    function requisicao($payload, $url, $metodo, $print_error){
        // Configuração da requisição cURL
        $ch = curl_init($this->api_url.$url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Authorization: Bearer ' . $this->bearer_token,
            'Content-Type: application/json', 
            'User-Agent: Reparei'
        ));

        if($metodo=='POST'){            
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
        }else if($metodo=='PUT'){
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, strtoupper($metodo));
        }
        // Realiza a requisição GET
        $response = curl_exec($ch);

        // Verifica se houve algum erro na requisição
        if (curl_errno($ch)) {
            return 'Erro na requisição cURL: ' . curl_error($ch)."\N<P>".$this->api_url.$url;
        } else {
            // Processa a resposta da API
            $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

            if ($http_code === 200 or $http_code === 201) {
                // A requisição foi bem-sucedida
                return $response;
            } else {
                if($print_error==1){
                    // A API retornou um código de erro
                    echo 'Erro na resposta da API. Código HTTP: ' . $http_code.
                    "\n<p>".
                    "\n<p>".' Detalhes: ' . $response;
                }
                return false;
            }
        }
        // Fecha a conexão cURL
        curl_close($ch);
    }
    
    public function CriarImovel($dados){
        $codigo_imovel = $dados['contrato_cod'];
        if (strpos($codigo_imovel, '/') !== false) {
            $partes = explode('/', $codigo_imovel);
            $codigo_imovel = $partes[0];
        }
        $payload=array(
            "id" => 0,
            "codigoExterno" => $codigo_imovel,
            "endereco" => $dados['imovel_endereco'],
            "numero" => $dados['imovel_numero'],
            "complemento" => $dados['imovel_complemento'],
            "bairro" => $dados['imovel_bairro'],
            "cidade" => $dados['imovel_cidade'],
            "uf" => $dados['imovel_uf'],
            "cep" => "",
            "tipoImovel" => "",
            "ativo" => true,
            "locadores" => array(),
            "metragem" => 0
        );
        foreach($dados['proprietarios'] as $proprietario){
            $payload["locadores"][]=array
            (
            "id" => 0,
            "codigoExterno" => $proprietario['id_superlogica'],
            "nome" => $proprietario['nome'],
            "ativo" => true,
            "cpfCnpj" => "",
            "estadoCivil" => "",
            "nacionalidade" => "",
            "profissao" => "",
            "telefone" => "",
            "representante" => ""
            );
        }

        $payload = json_encode($payload);
        $url='/api/imoveis';

        $requisicao=$this->requisicao($payload, $url, 'POST', 1);
        if($requisicao!=false){
            $retorno=json_decode($requisicao, true);
            $this->imovel_id=$retorno['id'];
            $this->locadores_dados=$retorno['locadores'];
        }
        return $requisicao;    
    
    }
    
    public function CriarAgendamento($dados){    
        // Define o fuso horário para UTC
        date_default_timezone_set('UTC');
        // Cria um objeto DateTime
        $datetime = new DateTime();
        // Formata a data e hora no padrão ISO 8601 sem milissegundos
        $dateIso8601 = $datetime->format('Y-m-d\TH:i:s');
        // Obter os milissegundos
        $milliseconds = round(microtime(true) * 1000) % 1000;
        // Combina ambos
        $datetimeWithMilliseconds = $dateIso8601 . '.' . sprintf('%03d', $milliseconds) . 'Z';
        // Adiciona 40 minutos
        $datetime->modify('+40 minutes');
        // Formata a nova data e hora, assumindo que os milissegundos continuam sendo aproximados
        $newDateIso8601 = $datetime->format('Y-m-d\TH:i:s');
        $newDatetimeWithMilliseconds = $newDateIso8601 . '.' . sprintf('%03d', $milliseconds) . 'Z';

        //BUSCA ULTIMA VISTORIAS DE ENTRADA PARA REFERENCIA
        $codigo_imovel = $dados['contrato_cod'];
        if (strpos($codigo_imovel, '/') !== false) {
            $partes = explode('/', $codigo_imovel);
            $codigo_imovel = $partes[0];
        }
        $id_vistoria_anterior=0;
        $lista_vistorias=$this->buscaVistorias($codigo_imovel);
        if($lista_vistorias!=false){
            $lista_vistorias=json_decode($lista_vistorias, true);
            $tipo='z';
            for($ii=0;$ii<sizeof($lista_vistorias) and $tipo!='Entrada';$ii++){
                $id_vistoria_anterior=$lista_vistorias[$ii]['id'];
                $tipo=$lista_vistorias[$ii]['tipoVistoria'];
            }      
        }

        //coloca nome do locatário em uma string
            $inquilino_list='';
            $ii=0;
            foreach($dados['inquilinos'] as $inquilino){
                if($ii>0){
                    $inquilino_list.=' | ';
                }

                $inquilino_list.=$inquilino['nome'];
                
                $ii++;
            }

        //define payload        
        $payload=array(
            "id"=> 0,
            "imovel"=> array(
              "id"=> $this->imovel_id
            ),
            "dataHoraInicio"=> $datetimeWithMilliseconds,
            "dataHoraFim"=> $newDatetimeWithMilliseconds,
            "vistoriador"=> array(
              "id"=> $this->vistoriador_id
            ),
            "tipoVistoria"=> array(
              "id"=> $this->tipoVistoria_id
            ),
            "nomeContato"=> "",
            "telefone"=> "",
            "observacao"=> "",
            "empresaFilial"=> array(
              "id"=> 3600
            ),
            "locatario"=> $inquilino_list,
            "idVistoriaModelo"=> $id_vistoria_anterior,
            "idSolicitacaoVistoria"=> 0
        );

        $payload = json_encode($payload);
        $url='/api/agendamentos';

        $requisicao=$this->requisicao($payload, $url, 'POST', 1);
        return $requisicao;
    }

    public function buscaVistorias($imovel_id){
        //busca ultimas 100 vistorias de um imóvel
        $payload='';
        $url='/api/vistorias/imoveis/'.urlencode($imovel_id);
        $requisicao=$this->requisicao($payload, $url, 'GET', 0);
        return $requisicao;        
    }
    public function cancelarAgendamento($agendamento_id){
        //busca ultimas 100 vistorias de um imóvel
        $payload='';
        $url='/api/agendamentos/'.urlencode($agendamento_id).'/cancelar';
        $requisicao=$this->requisicao($payload, $url, 'PUT', 0);
        print_r($requisicao);
        return $requisicao;        
    }

    public function baixarVistoriaPdf($vistoria_id, $foto = false){
        if($foto){
            $parametro_foto='ANEXADA';
        }else{
            $parametro_foto='SEM_FOTO';
        }
        //busca ultimas 100 vistorias de um imóvel
        $payload='';
        $url='/api/vistorias/'.$vistoria_id.'?formato=PDF&tipoFoto='.$parametro_foto;
        $requisicao=$this->requisicao($payload, $url, 'GET', 1);
        return $requisicao;        
    }

    
}










?>