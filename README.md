# Lumen PHP Framework

[![Build Status](https://travis-ci.org/laravel/lumen-framework.svg)](https://travis-ci.org/laravel/lumen-framework)
[![Total Downloads](https://img.shields.io/packagist/dt/laravel/framework)](https://packagist.org/packages/laravel/lumen-framework)
[![Latest Stable Version](https://img.shields.io/packagist/v/laravel/framework)](https://packagist.org/packages/laravel/lumen-framework)
[![License](https://img.shields.io/packagist/l/laravel/framework)](https://packagist.org/packages/laravel/lumen-framework)

## Official Documentation

Documentation for the framework can be found on the [Lumen website](https://lumen.laravel.com/docs).

## Instalação

**1° Passo**: Clonar o repositório do projeto utilizando o terminal (ex.: GitBash):

```bash
git clone https://bitbucket.org/cristianovelkan/yoga-toolbox-api
```

**2° Passo**: Verificar requisitos do sistema para o funcionamento do projeto.
[Lumen Server Requirements](https://lumen.laravel.com/docs/8.x#server-requirements).

**Forma alternativa**:
No arquivo composer.json estão os requisitos de sistema que devem ser verificados após o clone do repositório.

**3° Passo**: Se os requisitos estiverem corretos, é necessário criar um arquivo de configuração do ambiente de desenvolvimento, o **.env**.
Para isso é possível duplicar o arquivo existente na raiz do projeto chamado **.env.example**

```bash
cp .env.example .env
```

## Configurar Variáveis do .env

Para configurar as variáveis do **.env**, abra o arquivo e edite as seguintes:

1. **APP_KEY**: Gerar uma chave uuid pelo https://www.uuidgenerator.net/version4

```
APP_KEY=f6301fe4223d405c970ae14d4ceeaee4
```

2. **APP_URL**: O endereço http do sistema no seu local. 

```
APP_URL=http://localhost:8000
```

2. **APP_FRONT**: O endereço http do front-end no seu local. Usualmente na porta 3000 

```
APP_FRONT=http://localhost:3000
```

3. **Conexão com banco de dados**: É necessário criar previamente um banco de dados na sua máquina, neste caso utilizando o mysql. [Consultar criando banco de dados mysql](https://www.notion.so/Create-Database-ee11c51d56d74d03adde2ff2e6102fe4).
Assumindo que você utilizará o mysql como banco de dados padrão de desenvolvimento:

```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=SEUBANCODEDADOS
DB_USERNAME=SEUUSUARIO
DB_PASSWORD=SUASENHA
```

Obs.: Normalmente o username e password mysql são: "root"

4. **Configuração do servidor de Email**: Para testar os emails gerados pelo sistema é recomendado copiar esses valores do site [Mailtrap](https://mailtrap.io/), na seção "Integrations" escolha laravel na caixa de seleção, copie os valores mostrados para as variáveis abaixo:

```
MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=SEUUSERNAME
MAIL_PASSWORD=SEUPASSWORD
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=SEUEMAIL
MAIL_FROM_NAME=SEUNOME
```

## Instalação de Dependências
Após a configuração do .env é necessário instalar as dependências do composer rodando o seguinte comando:

```
composer install
```

## Criação das tabelas no banco de dados
Após instalação das dependências do composer, é possível instalar as tabelas do projeto rodando o seguinte comando:

```
php artisan migrate
```

Caso desejar, execute os seeders da aplicação:

```
php artisan db:seed
```

## Configuração do Passport
É necessário configurar o passport para que o front-end possa se comunicar com a api autenticada. O passport pode ser instalado com o seguinte comando:

```
php artisan passport:install
```

Esse comando vai gerar duas informações em tela:

```
Encryption keys generated successfully.
Personal access client created successfully.
Client ID: 1
Client secret: PBVEYvhut2JV7Xq5YGgFTwN4bcOIEiZHqb1E6AMe
Password grant client created successfully.
Client ID: 2
Client secret: 2siO5CGpqgR5BxHIK5TKALxvF6KMEFRBxPOv8NnL
```

Utilizamos sempre o segundo client gerado. Para isso abrimos novamente o arquivo **.env** e adicionamos os seguintes valores:

```
PASSPORT_CLIENT_ID=2
PASSPORT_CLIENT_SECRET=2siO5CGpqgR5BxHIK5TKALxvF6KMEFRBxPOv8NnL
```

Obs.: Para que o sistema reconheça a mudança de variáveis do .env é necessário rodar o comando abaixo:

```
composer dump
```

### Rodando a aplicação

Para rodar a aplicação:

```
php artisan serve
```

### Permissões (Linux somente)

Adicionalmente é recomendado dar as permissões corretas para as pastas de cache do sistema:

```
sudo chmod -R 777 storage bootstrap
```
