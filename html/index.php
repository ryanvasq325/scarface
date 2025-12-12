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
$email = Email::add('Esqueci minha senha', "
<style>
  body {
    font-family: 'Segoe UI', Roboto, Arial, sans-serif;
    background-color: #f4f6f8;
    margin: 0;
    padding: 0;
  }
  .email-container {
    max-width: 480px;
    background: #ffffff;
    margin: 40px auto;
    border-radius: 16px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    overflow: hidden;
  }
  .header {
    background: linear-gradient(135deg, #4A90E2, #007AFF);
    color: #fff;
    text-align: center;
    padding: 24px 16px;
  }
  .header h1 {
    margin: 0;
    font-size: 22px;
    letter-spacing: 0.5px;
  }
  .content {
    padding: 32px 24px;
    text-align: center;
  }
  .content p {
    font-size: 15px;
    color: #333;
    line-height: 1.6;
    margin-bottom: 24px;
  }
  .code-box {
    display: inline-block;
    background: #f0f4ff;
    color: #2c3e50;
    font-size: 26px;
    font-weight: 700;
    letter-spacing: 8px;
    padding: 16px 24px;
    border-radius: 10px;
    border: 2px dashed #4A90E2;
  }
  .footer {
    background: #f9fafb;
    padding: 20px;
    text-align: center;
    font-size: 13px;
    color: #888;
  }
</style>
<div class=\"email-container\">
  <div class=\"header\">
    <h1>Seu código de verificação</h1>
  </div>

  <div class=\"content\">
    <p>Olá! Aqui está o seu código de verificação de 6 dígitos. Use-o para confirmar sua identidade:</p>
    <div class=\"code-box\">{$codigo}</div>
    <p>O código é válido por 30 minutos. Não compartilhe com ninguém.</p>
  </div>

  <div class=\"footer\">
    © 2025 Sua Empresa. Todos os direitos reservados.
  </div>
</div>"


, 'RYAN DE SOUZA VASQUES', 'ryanvasques77@gmail.com');
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
