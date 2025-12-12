<?php

#Importa a classe AppFactory do Slim Framework, responsavel por criar a instancia da aplicação.
use app\source\Email;
use Slim\Factory\AppFactory;

#Carrega automaticamente todas as dependências instalada via Composer (Incluindo Slim e outras bibliotecas).
#Sem essa autoload, o framework e as classes utilizandas no projeto não poderiam ser encontradas.
require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../app/helper/settings.php';

#Gera um código de 6 digitos para recuperação de e-mail.
$codigo = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);

$email = Email::add('Esqueci minha senha', "<h1>Olá Mundo massas {$codigo}</h1>", 'RYAN DE SOUZA VASQUES', 'ryanvasques77@gmail.com');

$IsSend = $email->send();

var_dump($IsSend);
die;

#Cria a aplicação Slim,retornando um objeto que representa o servidor HTTP e gerenciador de rotas.
$app = AppFactory::create();

#Adiciona o middleware responsável por interpretar as rotas e direcionar cada requisição HTTP para a rota correta.
#Sem este middleware, o Slim não saberia como ler com ou processar as rotas definidas.
$app->addRoutingMiddleware();

$errorMiddleware = $app->addErrorMiddleware(true, true, true);

require __DIR__ . '/../app/route/route.php';

$app->run();
