<?php

namespace app\controller;

use app\database\builder\InsertQuery;
use app\database\builder\DeleteQuery;
use app\database\builder\SelectQuery;
use app\database\builder\UpdateQuery;

class User extends Base
{

    public function lista($request, $response)
    {
        $dadosTemplate = [
            'titulo' => 'Lista de usuário'
        ];
        return $this->getTwig()
            ->render($response, $this->setView('listuser'), $dadosTemplate)
            ->withHeader('Content-Type', 'text/html')
            ->withStatus(200);
    }
    public function cadastro($request, $response)
    {
        $dadosTemplate = [
            'titulo' => 'Cadastro de usuário'
        ];
        return $this->getTwig()
            ->render($response, $this->setView('user'), $dadosTemplate)
            ->withHeader('Content-Type', 'text/html')
            ->withStatus(200);
    }
    public function insert($request, $response)
    {
        try {
            $nome = $_POST['nome'];
            $sobrenome = $_POST['sobrenome'];
            $cpf = $_POST['cpf'];
            $rg = $_POST['rg'];
            $senha = $_POST['senha'];


            $FieldsAndValues = [
                'nome' => $nome,
                'sobrenome' => $sobrenome,
                'cpf' => $cpf,
                'rg' => $rg,
                'senha' => $senha
            ];

            $IsSave = InsertQuery::table('usuario')->save($FieldsAndValues);
            if (!$IsSave) {
                echo 'Erro ao salvar';
                die;
            }
            echo "Salvo com sucesso!";
            die;
        } catch (\Throwable $th) {
            //throw $th;
        }
    }
    public function listuser($request, $response)
    {
        #Captura todas a variaveis de forma mais segura VARIAVEIS POST.
        $form = $request->getParsedBody();
        #Qual a coluna da tabela deve ser ordenada.
        $order = $form['order'][0]['column'];
        #Tipo de ordenação
        $orderType = $form['order'][0]['dir'];
        #Em qual registro se inicia o retorno dos registros, OFFSET
        $start = $form['start'];
        #Limite de registro a serem retornados do banco de dados LIMIT
        $length = $form['length'];
        $fields = [
            0 => 'id',
            1 => 'nome',
            2 => 'sobrenome',
            3 => 'cpf',
            4 => 'rg',
        ];
        #Capturamos o nome do campo a ser odernado.
        $orderField = $fields[$order];
        #O termo pesquisado
        $term = $form['search']['value'];
        $query = SelectQuery::select('id,nome,sobrenome,cpf,rg')->from('usuario');
        if (!is_null($term) && ($term !== '')) {
            $query->where('nome', 'ilike', "%{$term}%", 'or')
                ->where('sobrenome', 'ilike', "%{$term}%", 'or')
                ->where('cpf', 'ilike', "%{$term}%", 'or')
                ->where('rg', 'ilike', "%{$term}%");
        }
        $users = $query
            ->order($orderField, $orderType)
            ->limit($length, $start)
            ->fetchAll();
        $userData = [];
        foreach ($users as $key => $value) {
            $userData[$key] = [
                $value['id'],
                $value['nome'],
                $value['sobrenome'],
                $value['cpf'],
                $value['rg'],
                "<button type='button'  onclick='Editar(" . $value['id'] . ");' class='btn btn-warning'>
                <i class=\"bi bi-pen-fill\"></i>
                Editar
                </button>

                 <button type='button'  onclick='Delete(" . $value['id'] . ");' class='btn btn-danger'>Excluir</button>"
            ];
        }
        $data = [
            'status' => true,
            'recordsTotal' => count($users),
            'recordsFiltered' => count($users),
            'data' => $userData
        ];
        $payload = json_encode($data);

        $response->getBody()->write($payload);

        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus(200);
    }
    public function alterar($request, $response, $args)
    {
        $id = $args['id'];
        $usuario = SelectQuery::select()
            ->from('vw_usuario_contatos')
            ->where('id', '=', $id)
            ->fetch();

        $dadosTemplate = [
            'titulo' => 'Alterar usuario',
            'usuario' => $usuario,
            'id' => $id,
            'acao' => 'alterar'
        ];
        return $this->getTwig()
            ->render($response, $this->setView('user'), $dadosTemplate)
            ->withHeader('Content-Type', 'text/html')
            ->withStatus(200);
    }
    public function delete($request, $response)
    {
        try {
            $id = $_POST['id'];
            $IsDelete = DeleteQuery::table('usuario')
                ->where('id', '=', $id)
                ->delete();

            if (!$IsDelete) {
                echo json_encode(['status' => false, 'msg' => $IsDelete, 'id' => $id]);
                die;
            }
            echo json_encode(['status' => true, 'msg' => 'Removido com sucesso!', 'id' => $id]);
            die;
        } catch (\Throwable $th) {
            echo "Erro: " . $th->getMessage();
            die;
        }
    }
    public function update($request, $response)
    {
        try {

            $form = $request->getParsedBody();
            $id = $form['id'];

            $dadosUsuario = [
                'nome'       => $form['nome'],
                'sobrenome'  => $form['sobrenome'],
                'cpf'        => $form['cpf'],
                'rg'         => $form['rg']
            ];

            if (!empty($form['senhaCadastro'])) {
                $dadosUsuario['senha'] = password_hash($form['senhaCadastro'], PASSWORD_DEFAULT);
            }

            UpdateQuery::table('usuario')
                ->set($dadosUsuario)
                ->where('id', '=', $id)
                ->update();

            
            $tipos = ['email', 'celular', 'whatsapp'];

            foreach ($tipos as $tipo) {

                $contato = SelectQuery::select('id')
                    ->from('contato')
                    ->where('id_usuario', '=', $id)
                    ->where('tipo', '=', $tipo)
                    ->fetch();

                if ($contato) {
                    UpdateQuery::table('contato')
                        ->set(['contato' => $form[$tipo]])
                        ->where('id', '=', $contato['id'])
                        ->update();
                } else {
                    InsertQuery::table('contato')->save([
                        'id_usuario' => $id,
                        'tipo'       => $tipo,
                        'contato'    => $form[$tipo]
                    ]);
                }
            }

            return $this->SendJson($response, [
                'status' => true,
                'msg' => 'Usuário atualizado com sucesso!',
                'id' => $id
            ], 200);
        } catch (\Exception $e) {
            return $this->SendJson($response, [
                'status' => false,
                'msg' => $e->getMessage(),
                'id' => 0
            ], 500);
        }
    }
}
