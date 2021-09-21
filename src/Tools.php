<?php

namespace NFPanel\Common;

use Exception;

/**
 * Classe Tools
 *
 * Classe responsável pela comunicação com a API NFPanel
 *
 * @category  NFPanel
 * @package   NFPanel\Common\Tools
 * @author    Jefferson Moreira <jeematheus at gmail dot com>
 * @copyright 2020 NFSERVICE
 * @license   https://opensource.org/licenses/MIT MIT
 */
class Tools
{
    /**
     * URL base para comunicação com a API
     *
     * @var string
     */
    public static $API_URL = [
        1 => 'https://api.fuganholisistemas.com.br/api/systems',
        2 => 'http://api.nfpanel.com.br/api/systems',
        3 => 'https://api.sandbox.fuganholisistemas.com.br/api/systems',
        4 => 'https://api.dusk.fuganholisistemas.com.br/api/systems'
    ];

    /**
     * Variável responsável por armazenar os dados a serem utilizados para comunicação com a API
     * Dados como token, ambiente(produção ou homologação) e debug(true|false)
     *
     * @var array
     */
    private $config = [
        'token' => '',
        'environment' => '',
        'product-id' => 0,
        'customer-id' => 0,
        'customer-cnpj' => '',
        'debug' => false,
        'upload' => false,
        'decode' => true
    ];

    /**
     * Define se a classe realizará um upload
     *
     * @param bool $isUpload Boleano para definir se é upload ou não
     *
     * @access public
     * @return void
     */
    public function setUpload(bool $isUpload) :void
    {
        $this->config['upload'] = $isUpload;
    }

    /**
     * Define se a classe realizará o decode do retorno
     *
     * @param bool $decode Boleano para definir se fa decode ou não
     *
     * @access public
     * @return void
     */
    public function setDecode(bool $decode) :void
    {
        $this->config['decode'] = $decode;
    }

    /**
     * Função responsável por definir se está em modo de debug ou não a comunicação com a API
     * Utilizado para pegar informações da requisição
     *
     * @param bool $isDebug Boleano para definir se é produção ou não
     *
     * @access public
     * @return void
     */
    public function setDebug(bool $isDebug) :void
    {
        $this->config['debug'] = $isDebug;
    }

    /**
     * Define o token a ser utilizado na comunicação com a API
     *
     * @param string $token Token a ser utilizado
     *
     * @access public
     * @return void
     */
    public function setToken(string $token) :void
    {
        $this->config['token'] = $token;
    }

    /**
     * Função responsável por setar o ambiente utilizado na API
     *
     * @param int $environment Ambiente API (1 - Produção | 2 - Local | 3 - Sandbox | 4- Dusk)
     *
     * @access public
     * @return void
     */
    public function setEnvironment(int $environment) :void
    {
        if (in_array($environment, [1, 2, 3, 4])) {
            $this->config['environment'] = $environment;
        }
    }

    /**
     * Define o produto que está utilizando a api
     *
     * @param int $product-id ID do produto
     *
     * @access public
     * @return void
     */
    public function setProductId(int $product_id) :void
    {
        $this->config['product-id'] = $product_id;
    }

    /**
     * Define o ID da empresa no NFPanel
     *
     * @param int $customer_id ID da empresa como customer no NFPanel
     *
     * @access public
     * @return void
     */
    public function setCustomerId(int $customerId) :void
    {
        $this->config['customer-id'] = $customerId;
    }

    /**
     * Define o CNPJ da empresa no NFPanel
     *
     * @param string $customer_id ID da empresa como customer no NFPanel
     *
     * @access public
     * @return void
     */
    public function setCustomerCnpj(string $customerCnpj) :void
    {
        $this->config['customer-cnpj'] = $customerCnpj;
    }

    /**
     * Recupera se é upload ou não
     *
     *
     * @access public
     * @return bool
     */
    public function getUpload() : bool
    {
        return $this->config['upload'];
    }

    /**
     * Recupera se faz decode ou não
     *
     *
     * @access public
     * @return bool
     */
    public function getDecode() : bool
    {
        return $this->config['decode'];
    }

    /**
     * Recupera o token
     *
     * @access public
     * @return string
     */
    public function getToken() :string
    {
        return $this->config['token'];
    }

    /**
     * Recupera o ambiente setado para comunicação com a API
     *
     * @access public
     * @return int
     */
    public function getEnvironment() :int
    {
        return $this->config['environment'];
    }

    /**
     * Recupera o produto-id
     *
     * @access public
     * @return int
     */
    public function getProductId() :int
    {
        return $this->config['product-id'];
    }

    /**
     * Recupera o customer-cnpj
     *
     * @access public
     * @return int
     */
    public function getCustomerCnpj() :int
    {
        return $this->config['customer-cnpj'];
    }

    /**
     * Recupera o customer-id
     *
     * @access public
     * @return int
     */
    public function getCustomerId() :int
    {
        return $this->config['customer-id'];
    }

    /**
     * Retorna os cabeçalhos padrão para comunicação com a API
     *
     * @access private
     * @return array
     */
    private function getDefaultHeaders() :array
    {
        $headers = [
            'access-token: '.$this->config['token'],
            'customer-id: '.$this->config['customer-id'],
            'customer-cnpj: '.$this->config['customer-cnpj'],
            'product-id: '.$this->config['product-id'],
            'Accept: application/json',
        ];

        if (!$this->config['upload']) {
            $headers[] = 'Content-Type: application/json';
        } else {
            $headers[] = 'Content-Type: multipart/form-data';
        }
        return $headers;
    }

    /**
     * Função responsável por criar um Cliente no NFPanel
     *
     * @param array $dados Dados para criação do cliente
     * @return \stdClass
     */
    public function cadastraCliente(array $dados, array $params = []) :\stdClass
    {
        try {
            $response = $this->post("customers", $dados, $params);

            if ($response['httpCode'] === 200) {
                return $response['body'];
            }

            if (isset($response['body']->errors) && !empty($response['body']->errors)) {
                throw new \Exception("\r\n".implode("\r\n", $response['body']->errors));
            } else {
                throw new \Exception(json_encode($response));
            }
        } catch (\Exception $error) {
            throw $error;
        }
    }

    /**
     * Função responsável por criar um Cliente no NFPanel
     *
     * @param int $id ID do cliente no NFPanel
     * @param array $dados Dados para criação do cliente
     * @return \stdClass
     */
    public function atualizaCliente(int $id, array $dados, array $params = []) :\stdClass
    {
        try {
            $response = $this->post("customers/$id", $dados, $params);

            if ($response['httpCode'] === 200) {
                return $response['body'];
            }

            if (isset($response['body']->errors) && !empty($response['body']->errors)) {
                throw new \Exception("\r\n".implode("\r\n", $response['body']->errors));
            } else {
                throw new \Exception(json_encode($response));
            }
        } catch (\Exception $error) {
            throw $error;
        }
    }

    /**
     * Função responsável por criar ou atualiar um Contador no NFPanel
     *
     * @param array $dados Dados do usuário
     * @return \stdClass
     */
    public function criaOuAtualizaContador(array $dados) :\stdClass
    {
        if(!isset($dados['cpfcnpj'])) {
            throw new \Exception("O CPF/CNPJ do contador é obrigatório");
        }
        try {
            $response = $this->put("contadors", $dados);

            if ($response['httpCode'] == 200) {
                return $response['body'];
            }

            if (isset($response['body']->errors) && !empty($response['body']->errors)) {
                throw new \Exception("\r\n".implode("\r\n", $response['body']->errors));
            } else {
                throw new \Exception(json_encode($response));
            }
        } catch (\Exception $error) {
            throw $error;
        }
    }
    
    /**
     * Função responsável por criar um contrato com plano no NFPanel
     *
     * @param array $dados Dados para criação do contrato
     * @return \stdClass
     */
    public function cadastraContrato(array $dados, array $params = []) :\stdClass
    {
        try {
            $response = $this->post("contracts", $dados, $params);

            if ($response['httpCode'] === 200) {
                return $response['body'];
            }

            if (isset($response['body']->errors) && !empty($response['body']->errors)) {
                throw new \Exception("\r\n".implode("\r\n", $response['body']->errors));
            } else {
                throw new \Exception(json_encode($response));
            }
        } catch (\Exception $error) {
            throw $error;
        }
    }

    /**
     * Função responsável por criar uma notificação no NFPanel
     *
     * @param array $dados Dados para criação da notificação
     * @return \stdClass
     */
    public function criaNotificacao(array $dados) :\stdClass
    {
        try {
            $response = $this->post("/notifications", $dados);

            if ($response['httpCode'] === 200) {
                return $response['body'];
            }

            if (isset($response['body']->errors) && !empty($response['body']->errors)) {
                throw new \Exception("\r\n".implode("\r\n", $response['body']->errors));
            } else {
                throw new \Exception(json_encode($response));
            }
        } catch (\Exception $error) {
            throw $error;
        }
    }

    /**
     * Função responsável por marcar uma notificação como recebida no NFPanel
     *
     * @param int $notification_id ID da Notificação no NFPanel
     * @param string $date Data em que foi recebida a notificação
     * @return \stdClass
     */
    public function notificaoRecebida(int $notification_id, string $date) :\stdClass
    {
        try {
            $data = [
                "date" => $date
            ];
            $response = $this->post("/notifications/$notification_id/received", $data);

            if ($response['httpCode'] === 200) {
                return $response['body'];
            }

            if (isset($response['body']->errors) && !empty($response['body']->errors)) {
                throw new \Exception("\r\n".implode("\r\n", $response['body']->errors));
            } else {
                throw new \Exception(json_encode($response));
            }
        } catch (\Exception $error) {
            throw $error;
        }
    }

    /**
     * Função responsável por marcar uma notificação como lida no NFPanel
     *
     * @param int $notification_id ID da Notificação no NFPanel
     * @param string $date Data em que foi lida a notificação
     * @return \stdClass
     */
    public function notificaoLida(int $notification_id, string $date) :\stdClass
    {
        try {
            $data = [
                "date" => $date
            ];
            $response = $this->post("/notifications/$notification_id/viewed", $data);

            if ($response['httpCode'] === 200) {
                return $response['body'];
            }

            if (isset($response['body']->errors) && !empty($response['body']->errors)) {
                throw new \Exception("\r\n".implode("\r\n", $response['body']->errors));
            } else {
                throw new \Exception(json_encode($response));
            }
        } catch (\Exception $error) {
            throw $error;
        }
    }

    /**
     * Função responsável por buscar os tickets de uma empresa no NFPanel
     *
     * @param array $params Parametros a serem passados
     * @return \stdClass
     */
    public function buscaTickets(array $params = []) :\stdClass
    {
        try {
            $response = $this->get('tickets', $params);

            if ($response['httpCode'] === 200) {
                return $response['body'];
            }

            if (isset($response['body']->errors) && !empty($response['body']->errors)) {
                throw new \Exception("\r\n".implode("\r\n", $response['body']->errors));
            } else {
                throw new \Exception(json_encode($response));
            }
        } catch (\Exception $error) {
            throw $error;
        }
    }

    /**
     * Função responsável por buscar um ticket especifico de uma empresa no NFPanel
     *
     * @param int $ticket_id ID do ticket
     * @return \stdClass
     */
    public function buscaTicket(int $ticket_id) :\stdClass
    {
        try {
            $response = $this->get("tickets/$ticket_id");

            if ($response['httpCode'] === 200) {
                return $response['body'];
            }

            if (isset($response['body']->errors) && !empty($response['body']->errors)) {
                throw new \Exception("\r\n".implode("\r\n", $response['body']->errors));
            } else {
                throw new \Exception(json_encode($response));
            }
        } catch (\Exception $error) {
            throw $error;
        }
    }

    /**
     * Função responsável por cadastrar um ticket no NFPanel
     *
     * @param array $dados ID do ticket
     * @return \stdClass
     */
    public function cadastraTicket(array $dados, array $params = []) :\stdClass
    {
        try {
            $response = $this->post('tickets', $dados);

            if ($response['httpCode'] === 200) {
                return $response['body'];
            }

            if (isset($response['body']->errors) && !empty($response['body']->errors)) {
                throw new \Exception("\r\n".implode("\r\n", $response['body']->errors));
            } else {
                throw new \Exception(json_encode($response));
            }
        } catch (\Exception $error) {
            throw $error;
        }
    }

    /**
     * Função responsável por enviar uma nova mensagem de cliente para um ticket no NFPanel
     *
     * @param int $ticket_id ID do Ticket no NFPanel
     * @param array $dados Dados da mensagem
     * @return \stdClass
     */
    public function enviaMensagemTicket(int $ticket_id, array $dados) :\stdClass
    {
        try {
            // Transforma os anexos em instancias de CURLFile
            foreach ($dados['attachments'] as $keyAttachment => $attachment) {
                $dados["attachments[$keyAttachment]"] = new \CURLFile($attachment['path'], $attachment['type'], $attachment['name']);
            }
            unset($dados['attachments']);
            $this->setUpload(true);
            $response = $this->post("tickets/$ticket_id/messages", $dados);

            if ($response['httpCode'] === 200) {
                return $response['body'];
            }

            if (isset($response['body']->errors) && !empty($response['body']->errors)) {
                throw new \Exception("\r\n".implode("\r\n", $response['body']->errors));
            } else {
                throw new \Exception(var_export($response, true));
            }
        } catch (\Exception $error) {
            throw $error;
        }
    }

    /**
     * Função responsável por buscar o arquivo de um ticket
     *
     * @param int $ticket_id ID do Ticket
     * @param int $attachment_id ID do Anexo do Ticket
     * @return \stdClass
     */
    public function buscaArquivoTicket(int $ticket_id, int $attachment_id) :\stdClass
    {
        try {
            $response = $this->get("tickets/$ticket_id/attachments/$attachment_id");

            if ($response['httpCode'] === 200) {
                return $response['body'];
            }

            if (isset($response['body']->errors) && !empty($response['body']->errors)) {
                throw new \Exception("\r\n".implode("\r\n", $response['body']->errors));
            } else {
                throw new \Exception(json_encode($response));
            }
        } catch (\Exception $error) {
            throw $error;
        }
    }

    /**
     * Função responsável por buscar o arquivo de uma mensagem de um ticket
     *
     * @param int $ticket_id ID do Ticket
     * @param int $message_id ID da Mensagem do Ticket
     * @param int $attachment_id ID do Anexo do Ticket
     * @return \stdClass
     */
    public function buscaArquivoTicketMensagem(int $ticket_id, int $message_id, int $attachment_id) :\stdClass
    {
        try {
            $response = $this->get("tickets/$ticket_id/messages/$message_id/attachments/$attachment_id");

            if ($response['httpCode'] === 200) {
                return $response['body'];
            }

            if (isset($response['body']->errors) && !empty($response['body']->errors)) {
                throw new \Exception("\r\n".implode("\r\n", $response['body']->errors));
            } else {
                throw new \Exception(json_encode($response));
            }
        } catch (\Exception $error) {
            throw $error;
        }
    }

    /**
     * Função responsável por cadastrar a pesquisa de satisfação do cliente
     *
     * @param int $ticket_id ID do ticket no NFPanel
     * @param array $dados Dados da pesquisa de satisfação
     *
     * @access public
     * @return \stdClass
     */
    public function enviaSatisfacao(int $ticket_id, array $dados, array $params = []) :\stdClass
    {
        $errors = [];
        if (!isset($dados['satisfaction']) || empty($dados['satisfaction'])) {
            $errors[] = 'O nivel de satisfação é obrigatório';
        } else if (!in_array((int)$dados['satisfaction'], [1, 2, 3, 4, 5])) {
            $errors[] = 'O nivel de satisfação pode conter apenas os valores de 1 a 5';
        }
        if (!empty($errors)) {
            throw new \Exception(implode("\r\n", $dados), 1);
        }

        try {
            $response = $this->post("tickets/$ticket_id/satisfaction", $dados, $params);

            if ($response['httpCode'] === 200) {
                return $response['body'];
            }

            if (isset($response['body']->errors) && !empty($response['body']->errors)) {
                throw new \Exception("\r\n".implode("\r\n", $response['body']->errors));
            } else {
                throw new \Exception(json_encode($response));
            }
        } catch (\Exception $error) {
            throw $error;
        }
    }

    /**
     * Função responsável por buscar as sugestões de uma empresa no NFPanel
     *
     * @param array $params Parametros a serem passados
     * @return \stdClass
     */
    public function buscaSugestoes(array $params = []) :\stdClass
    {
        try {
            $response = $this->get('suggestions', $params);

            if ($response['httpCode'] === 200) {
                return $response['body'];
            }

            if (isset($response['body']->errors) && !empty($response['body']->errors)) {
                throw new \Exception("\r\n".implode("\r\n", $response['body']->errors));
            } else {
                throw new \Exception(json_encode($response));
            }
        } catch (\Exception $error) {
            throw $error;
        }
    }

    /**
     * Função responsável por buscar uma sugestão especifico de uma empresa no NFPanel
     *
     * @param int $suggestion_id ID da sugestão
     * @return \stdClass
     */
    public function buscaSugestao(int $suggestion_id) :\stdClass
    {
        try {
            $response = $this->get("suggestions/$suggestion_id");

            if ($response['httpCode'] === 200) {
                return $response['body'];
            }

            if (isset($response['body']->errors) && !empty($response['body']->errors)) {
                throw new \Exception("\r\n".implode("\r\n", $response['body']->errors));
            } else {
                throw new \Exception(json_encode($response));
            }
        } catch (\Exception $error) {
            throw $error;
        }
    }

    /**
     * Função responsável por buscar o arquivo de uma sugestão
     *
     * @param int $suggestion_id ID da Sugestão
     * @param int $attachment_id ID do Anexo da Sugestão
     * @return \stdClass
     */
    public function buscaArquivoSugestao(int $suggestion_id, int $attachment_id) :\stdClass
    {
        try {
            $response = $this->get("suggestions/$suggestion_id/attachments/$attachment_id");

            if ($response['httpCode'] === 200) {
                return $response['body'];
            }

            if (isset($response['body']->errors) && !empty($response['body']->errors)) {
                throw new \Exception("\r\n".implode("\r\n", $response['body']->errors));
            } else {
                throw new \Exception(json_encode($response));
            }
        } catch (\Exception $error) {
            throw $error;
        }
    }

    /**
     * Função responsável por enviar um voto de cliente sobre uma sugestão
     *
     * @param int $suggestion_id ID da Sugestão
     * @param array $dados Dados para votação
     * @return \stdClass
     */
    public function enviaVotoSugestao(int $suggestion_id, array $dados) :\stdClass
    {
        try {
            $response = $this->post("suggestions/$suggestion_id/vote", $dados);

            if ($response['httpCode'] == 200) {
                return $response['body'];
            }

            if (isset($response['body']->errors) && !empty($response['body']->errors)) {
                throw new \Exception("\r\n".implode("\r\n", $response['body']->errors));
            } else {
                throw new \Exception(json_encode($response));
            }
        } catch (\Exception $error) {
            throw $error;
        }
    }

    /**
     * Função responsável por remover um voto de cliente sobre uma sugestão
     *
     * @param int $suggestion_id ID da Sugestão
     * @param array $dados Dados para remoção da votação
     * @return string
     */
    public function removeVotoSugestao(int $suggestion_id, array $dados) :string
    {
        try {
            $response = $this->delete("suggestions/$suggestion_id/vote", $dados);

            if ($response['httpCode'] == 200) {
                return $response['body'];
            }

            if (isset($response['body']->errors) && !empty($response['body']->errors)) {
                throw new \Exception("\r\n".implode("\r\n", $response['body']->errors));
            } else {
                throw new \Exception(json_encode($response));
            }
        } catch (\Exception $error) {
            throw $error;
        }
    }

    /**
     * Função responsável por cadastrar um usuário no NFPanel
     *
     * @param array $dados Dados do usuário
     * @return \stdClass
     */
    public function cadastraUsuario(array $dados) :\stdClass
    {
        try {
            $response = $this->post("users", $dados);

            if ($response['httpCode'] == 200) {
                return $response['body'];
            }

            if (isset($response['body']->errors) && !empty($response['body']->errors)) {
                throw new \Exception("\r\n".implode("\r\n", $response['body']->errors));
            } else {
                throw new \Exception(json_encode($response));
            }
        } catch (\Exception $error) {
            throw $error;
        }
    }

    /**
     * Função responsável por atualizar um usuário no NFPanel
     *
     * @param int   $user_id ID do usuário
     * @param array $dados Dados do usuário
     * @return \stdClass
     */
    public function atualizaUsuario(int $user_id, array $dados) :\stdClass
    {
        try {
            $response = $this->put("users/$user_id", $dados);

            if ($response['httpCode'] == 200) {
                return $response['body'];
            }

            if (isset($response['body']->errors) && !empty($response['body']->errors)) {
                throw new \Exception("\r\n".implode("\r\n", $response['body']->errors));
            } else {
                throw new \Exception(json_encode($response));
            }
        } catch (\Exception $error) {
            throw $error;
        }
    }

    /**
     * Função responsável por remover um usuário no NFPanel
     *
     * @param int $user_id ID do Usuário a ser removido
     * @return string
     */
    public function removeUsuario(int $user_id) :string
    {
        try {
            $response = $this->delete("users/$user_id");

            if ($response['httpCode'] == 200) {
                return $response['body'];
            }

            if (isset($response['body']->errors) && !empty($response['body']->errors)) {
                throw new \Exception("\r\n".implode("\r\n", $response['body']->errors));
            } else {
                throw new \Exception(json_encode($response));
            }
        } catch (\Exception $error) {
            throw $error;
        }
    }

    /**
     * Função responsável por atualizar o último acesso do usuário no sistema
     *
     * @param int $user_id ID do Usuário
     * @param string $date Data do Acesso
     * @return string
     */
    public function acessoUsuario(int $user_id, string $date) :string
    {
        try {
            $dados = [
                'date' => $date
            ];
            $response = $this->post("users/$user_id/access", $dados);

            if ($response['httpCode'] == 200) {
                return $response['body'];
            }

            if (isset($response['body']->errors) && !empty($response['body']->errors)) {
                throw new \Exception("\r\n".implode("\r\n", $response['body']->errors));
            } else {
                throw new \Exception(json_encode($response));
            }
        } catch (\Exception $error) {
            throw $error;
        }
    }

    /**
     * Função responsável por atualizar o contador no NFPanel
     *
     * @param string $cpfcnpj CPF/CNPJ do contador
     * @return \stdClass
     */
    public function atualizaContador(string $cpfcnpj) :\stdClass
    {
        try {
            $dados['cpfcnpj'] = $cpfcnpj;
            $response = $this->post("/contador", $dados);

            if ($response['httpCode'] === 200) {
                return $response['body'];
            }

            if (isset($response['body']->errors) && !empty($response['body']->errors)) {
                throw new \Exception("\r\n".implode("\r\n", $response['body']->errors));
            } else {
                throw new \Exception(json_encode($response));
            }
        } catch (\Exception $error) {
            throw $error;
        }
    }

    /**
     * Função responsável por remover o contador no NFPanel
     *
     * @return \stdClass
     */
    public function removeContador()
    {
        try {
            $response = $this->delete("/contador");

            if ($response['httpCode'] === 200) {
                return $response['body'];
            }

            if (isset($response['body']->errors) && !empty($response['body']->errors)) {
                throw new \Exception("\r\n".implode("\r\n", $response['body']->errors));
            } else {
                throw new \Exception(json_encode($response));
            }
        } catch (\Exception $error) {
            throw $error;
        }
    }

    /**
     * Função responsável por solicitar a sincronização de um contador com o usuário de outros sistemas
     */
    public function solicitaSincronizacaoContador(array $dados)
    {
        $errors = [];
        if (!isset($dados['type']) || empty($dados['type'])) {
            $errors[] = 'Informe o tipo de pessoa do contador';
        }
        if (!isset($dados['cpfcnpj']) || empty($dados['cpfcnpj'])) {
            $errors[] = 'Informe o CPF/CNPJ do contador';
        }
        if (!isset($dados['name']) || empty($dados['name'])) {
            $errors[] = 'Informe o nome/razão social do contador';
        }
        if (!isset($dados['access_token']) || empty($dados['access_token'])) {
            $errors[] = 'Informe o token de acesso do contador';
        }
        if (!empty($errors)) {
            throw new Exception(implode("\r\n", $errors), 1);
        }

        try {
            $response = $this->post("/contador/request", $dados);

            if ($response['httpCode'] === 200) {
                return $response['body'];
            }

            if (isset($response['body']->errors) && !empty($response['body']->errors)) {
                throw new \Exception("\r\n".implode("\r\n", $response['body']->errors));
            } else {
                throw new \Exception(json_encode($response));
            }
        } catch (\Exception $error) {
            throw $error;
        }
    }

    /**
     * Função responsável por gerar o contrato Clicksign
     *
     * @param $dados Dados para gerar o contrato
     *
     * @access public
     */
    public function geraContratoClicksign(array $dados, array $params = [])
    {
        $errors = [];
        if (!isset($dados['agent_name']) || empty($dados['agent_name'])) {
            $errors[] = 'Informe o nome do representante legal da empresa';
        }
        if (!isset($dados['agent_cpf']) || empty($dados['agent_cpf'])) {
            $errors[] = 'Informe o CPF do representante legal da empresa';
        }
        if (!isset($dados['agent_email']) || empty($dados['agent_email'])) {
            $errors[] = 'Informe o E-mail do representante legal da empresa';
        }
        if (!empty($errors)) {
            throw new Exception(implode("\r\n", $errors), 1);
        }

        if (!isset($dados['update'])) {
            $dados['update'] = false;
        }
        try {
            $response = $this->post("contracts/clicksign", $dados, $params);

            if ($response['httpCode'] === 200) {
                return $response['body'];
            }

            if (isset($response['body']->errors) && !empty($response['body']->errors)) {
                throw new \Exception("\r\n".implode("\r\n", $response['body']->errors));
            } else {
                throw new \Exception(json_encode($response));
            }
        } catch (\Exception $error) {
            throw $error;
        }
    }

    /**
     * Função responsável por verificar um documento da clicksign pendente de assinatura
     *
     * @param $dados Dados para consulta do documento
     *
     * @access public
     */
    public function verificaDocumentoClicksign(array $dados, array $params = [])
    {
        $errors = [];
        if (!isset($dados['contract_id']) || empty($dados['contract_id'])) {
            $errors[] = 'Informe o ID do contrato referente ao documento';
        }
        if (!isset($dados['key']) || empty($dados['key'])) {
            $errors[] = 'Informe a chave Clicksign do documento';
        }
        if (!isset($dados['agent_email']) || empty($dados['agent_email'])) {
            $errors[] = 'Informe o E-mail do representante legal da empresa';
        }
        if (!empty($errors)) {
            throw new Exception(implode("\r\n", $errors), 1);
        }
        try {
            $response = $this->post("contracts/clicksign/verify", $dados, $params);

            if ($response['httpCode'] === 200) {
                return $response['body'];
            }

            if (isset($response['body']->errors) && !empty($response['body']->errors)) {
                throw new \Exception("\r\n".implode("\r\n", $response['body']->errors));
            } else {
                throw new \Exception(json_encode($response));
            }
        } catch (\Exception $error) {
            throw $error;
        }
    }

    /**
     * Função responsável por marcar um contrato como assinado após a assitatura do documento clicksign
     *
     * @param array $dados Dados para a requisição
     *
     * @access public
     */
    public function marcaContrato(array $dados, array $params = [])
    {
        try {
            $response = $this->post("contracts/clicksign/check", $dados, $params);

            if ($response['httpCode'] === 200) {
                return $response['body'];
            }

            if (isset($response['body']->errors) && !empty($response['body']->errors)) {
                throw new \Exception("\r\n".implode("\r\n", $response['body']->errors));
            } else {
                throw new \Exception(json_encode($response));
            }
        } catch (\Exception $error) {
            throw $error;
        }
    }

    /**
     * Função responsável por buscar a URL de download do último contrato Clicksign
     *
     * @access public
     */
    public function baixaDocumento(array $params = [])
    {
        try {
            $response = $this->get("contracts/clicksign/download", $params);

            if ($response['httpCode'] === 200) {
                return $response['body'];
            }

            if (isset($response['body']->errors) && !empty($response['body']->errors)) {
                throw new \Exception("\r\n".implode("\r\n", $response['body']->errors));
            } else {
                throw new \Exception(json_encode($response));
            }
        } catch (\Exception $error) {
            throw $error;
        }
    }

    /**
     * Função responsável por buscar os planos disponíveis
     *
     * @param array $params parametros para a requisição
     * @return \stdClass
     */
    public function buscaPlanos(array $params = []) :\stdClass
    {
        try {
            $response = $this->get("products/plans", $params);

            if ($response['httpCode'] === 200) {
                return $response['body'];
            }

            if (isset($response['body']->errors) && !empty($response['body']->errors)) {
                throw new \Exception("\r\n".implode("\r\n", $response['body']->errors));
            } else {
                throw new \Exception(json_encode($response));
            }
        } catch (\Exception $error) {
            throw $error;
        }
    }

    /**
     * Função responsável por buscar tutoriais
     *
     * @param array $params parametros para a requisição
     * @return \stdClass
     */
    public function buscaTutoriais(array $params = []) :\stdClass
    {
        try {
            $response = $this->get("tours", $params);

            if ($response['httpCode'] === 200) {
                return $response['body'];
            }

            if (isset($response['body']->errors) && !empty($response['body']->errors)) {
                throw new \Exception("\r\n".implode("\r\n", $response['body']->errors));
            } else {
                throw new \Exception(json_encode($response));
            }
        } catch (\Exception $error) {
            throw $error;
        }
    }

    /**
     * Função responsável por cadastrar as mensagem do chat no CRM de um cliente
     *
     * @param array $answers Respostas enviadas pelo chat
     * @param array $params Parametros adicionais para a requisição
     *
     * @access public
     * @return object
     */
    public function cadastraMsgChat(array $answers, array $params = [])
    {
        try {
            $dados['answers'] = $answers;

            $response = $this->post("customers/answers", $dados, $params);

            if ($response['httpCode'] === 200) {
                return $response['body'];
            }

            if (isset($response['body']->errors) && !empty($response['body']->errors)) {
                throw new \Exception("\r\n".implode("\r\n", $response['body']->errors));
            } else {
                throw new \Exception(json_encode($response));
            }
        } catch (\Exception $error) {
            throw $error;
        }
    }


    /**
     * Execute a GET Request
     *
     * @param string $path
     * @param array $params
     * @param array $headers Cabeçalhos adicionais para requisição
     *
     * @access protected
     * @return array
     */
    protected function get(string $path, array $params = [], array $headers = []) :array
    {
        $opts = [
            CURLOPT_HTTPHEADER => $this->getDefaultHeaders()
        ];

        if (!empty($headers)) {
            $opts[CURLOPT_HTTPHEADER] = array_merge($opts[CURLOPT_HTTPHEADER], $headers);
        }

        $exec = $this->execute($path, $opts, $params);

        return $exec;
    }

    /**
     * Execute a POST Request
     *
     * @param string $path
     * @param string $body
     * @param array $params
     * @param array $headers Cabeçalhos adicionais para requisição
     *
     * @access protected
     * @return array
     */
    protected function post(string $path, array $body = [], array $params = [], array $headers = []) :array
    {
        $opts = [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => !$this->config['upload'] ? json_encode($body) : $body,
            CURLOPT_HTTPHEADER => $this->getDefaultHeaders()
        ];

        if (!empty($headers)) {
            $opts[CURLOPT_HTTPHEADER] = array_merge($opts[CURLOPT_HTTPHEADER], $headers);
        }

        $exec = $this->execute($path, $opts, $params);

        return $exec;
    }

    /**
     * Execute a PUT Request
     *
     * @param string $path
     * @param string $body
     * @param array $params
     * @param array $headers Cabeçalhos adicionais para requisição
     *
     * @access protected
     * @return array
     */
    protected function put(string $path, array $body = [], array $params = [], array $headers = []) :array
    {
        $opts = [
            CURLOPT_HTTPHEADER => $this->getDefaultHeaders(),
            CURLOPT_CUSTOMREQUEST => "PUT",
            CURLOPT_POSTFIELDS => json_encode($body)
        ];

        if (!empty($headers)) {
            $opts[CURLOPT_HTTPHEADER] = array_merge($opts[CURLOPT_HTTPHEADER], $headers);
        }

        $exec = $this->execute($path, $opts, $params);

        return $exec;
    }

    /**
     * Execute a DELETE Request
     *
     * @param string $path
     * @param array $params
     * @param array $headers Cabeçalhos adicionais para requisição
     *
     * @access protected
     * @return array
     */
    protected function delete(string $path, array $body = [], array $params = [], array $headers = []) :array
    {
        $opts = [
            CURLOPT_HTTPHEADER => $this->getDefaultHeaders(),
            CURLOPT_CUSTOMREQUEST => "DELETE"
        ];

        if (!empty($body)) {
            $opts[CURLOPT_POSTFIELDS] = json_encode($body);
        }

        if (!empty($headers)) {
            $opts[CURLOPT_HTTPHEADER] = array_merge($opts[CURLOPT_HTTPHEADER], $headers);
        }

        $exec = $this->execute($path, $opts, $params);

        return $exec;
    }

    /**
     * Execute a OPTION Request
     *
     * @param string $path
     * @param array $params
     * @param array $headers Cabeçalhos adicionais para requisição
     *
     * @access protected
     * @return array
     */
    protected function options(string $path, array $params = [], array $headers = []) :array
    {
        $opts = [
            CURLOPT_CUSTOMREQUEST => "OPTIONS"
        ];

        if (!empty($headers)) {
            $opts[CURLOPT_HTTPHEADER] = $headers;
        }

        $exec = $this->execute($path, $opts, $params);

        return $exec;
    }

    /**
     * Função responsável por realizar a requisição e devolver os dados
     *
     * @param string $path Rota a ser acessada
     * @param array $opts Opções do CURL
     * @param array $params Parametros query a serem passados para requisição
     *
     * @access protected
     * @return array
     */
    protected function execute(string $path, array $opts = [], array $params = []) :array
    {
        if (!preg_match("/^\//", $path)) {
            $path = '/' . $path;
        }

        $url = self::$API_URL[$this->config['environment']].$path;

        $curlC = curl_init();

        if (!empty($opts)) {
            curl_setopt_array($curlC, $opts);
        }

        if (!empty($params)) {
            $paramsJoined = [];

            foreach ($params as $param) {
                if (isset($param['name']) && !empty($param['name']) && isset($param['value']) && (!empty($param['value']) || $param['value'] == 0)) {
                    $paramsJoined[] = urlencode($param['name'])."=".urlencode($param['value']);
                }
            }

            if (!empty($paramsJoined)) {
                $params = '?'.implode('&', $paramsJoined);
                $url = $url.$params;
            }
        }

        curl_setopt($curlC, CURLOPT_URL, $url);
        curl_setopt($curlC, CURLOPT_RETURNTRANSFER, true);
        if (!empty($dados)) {
            curl_setopt($curlC, CURLOPT_POSTFIELDS, json_encode($dados));
        }
        $retorno = curl_exec($curlC);
        $info = curl_getinfo($curlC);
        $return["body"] = ($this->config['decode'] || !$this->config['decode'] && $info['http_code'] != '200') ? json_decode($retorno) : $retorno;
        $return["httpCode"] = curl_getinfo($curlC, CURLINFO_HTTP_CODE);
        if ($this->config['debug']) {
            $return['info'] = curl_getinfo($curlC);
        }
        curl_close($curlC);

        return $return;
    }
}
