# API de Transações Bancárias EBANX

Uma API simples em PHP para gerenciar contas e transações bancárias (depósito, saque e transferência), utilizando SQLite para persistência de dados.

## Endpoints da API

### `POST /reset`
Reseta o estado da aplicação, limpando todos os dados do banco de dados. Útil para iniciar um novo cenário de testes.
- **Corpo da Requisição:** Vazio
- **Resposta de Sucesso (200 OK):** `OK`

---

### `GET /balance`
Consulta o saldo de uma conta existente.
- **Parâmetros da Query:** `account_id` (string)
- **Exemplo:** `GET /balance?account_id=100`
- **Respostas:**
  - **200 OK:** `20` (o saldo da conta)
  - **404 Not Found:** `0` (se a conta não existir)

---

### `POST /event`
Realiza uma operação de depósito, saque ou transferência.
- **Corpo da Requisição:** JSON
- **Tipos de Evento:**
  - **Depósito:**
    ```json
    {
      "type": "deposit",
      "destination": "100",
      "amount": 10
    }
    ```
    Resposta (201 Created): `{"destination": {"id": "100", "balance": 10}}`

  - **Saque:**
    ```json
    {
      "type": "withdraw",
      "origin": "100",
      "amount": 5
    }
    ```
    Resposta (201 Created): `{"origin": {"id": "100", "balance": 5}}`

  - **Transferência:**
    ```json
    {
      "type": "transfer",
      "origin": "100",
      "destination": "200",
      "amount": 15
    }
    ```
    Resposta (201 Created): `{"origin": {"id": "100", "balance": -10}, "destination": {"id": "200", "balance": 15}}`

- **Respostas de Erro:**
  - **404 Not Found:** `0` (se a conta de origem não existir para saque ou transferência)

## Como Executar o Projeto

### Pré-requisitos
Antes de começar, garanta que você tem os seguintes softwares instalados:
- **PHP** (versão 8.1 ou superior)
- **Extensão PHP SQLite3**:
  ```bash
  # Para sistemas baseados em Debian/Ubuntu
  sudo apt update && sudo apt install php8.1-sqlite3
  ```
- **Composer**: Para gerenciamento de dependências do PHP.
- **(Opcional) Ngrok**: Necessário para executar os testes automatizados da plataforma Ipkiss.

### 1. Instalação
Clone o repositório e instale as dependências.
```bash
git clone https://github.com/dluiscamargo/API_EBANX.git
cd API_EBANX
composer install
```
*(Nota: O projeto não possui dependências externas, mas o `composer install` é uma boa prática para gerar o autoloader.)*

### 2. Executando o Servidor Local
Inicie o servidor de desenvolvimento embutido do PHP.
```bash
php -S localhost:8000
```
A API agora estará acessível em `http://localhost:8000`.

## Como Executar os Testes Automatizados (Ipkiss Tester)

A ferramenta de testes Ipkiss executa as requisições a partir de um servidor externo, portanto, ela não consegue acessar `localhost:8000` diretamente. Precisamos expor nosso servidor local para a internet usando o `ngrok`.

### Passo 1: Instalar e Configurar o `ngrok`
1. **Crie uma conta gratuita** no site do [ngrok](https://dashboard.ngrok.com/signup).
2. **Instale o ngrok** no seu sistema.
   ```bash
   # Para sistemas baseados em Debian/Ubuntu usando snap
   sudo snap install ngrok
   ```
3. **Conecte sua conta:** Siga as instruções em [seu dashboard](https://dashboard.ngrok.com/get-started/your-authtoken) para adicionar seu authtoken.
   ```bash
   ngrok config add-authtoken <SEU_TOKEN_AQUI>
   ```

### Passo 2: Iniciar o Túnel
Com o servidor PHP local já rodando (veja a seção "Executando o Servidor Local"), abra um **novo terminal** e inicie o `ngrok` para criar um túnel para a porta 8000.
```bash
ngrok http 8000
```
O `ngrok` irá exibir uma URL pública no formato `https://<id-aleatorio>.ngrok-free.app`. **Copie essa URL.**

### Passo 3: Rodar os Testes
1. Acesse o [Ipkiss Tester](https://ipkiss.pragmazero.com/).
2. No campo **URL**, cole a URL pública do `ngrok` que você copiou.
3. Copie e cole o script de teste fornecido pela EBANX no campo **Test script**.
4. Clique em **"Run tests!"**.

Todos os testes deverão passar com sucesso! 

**Ipkiss Tester**
URL 
https://23cf6229bd5d.ngrok-free.app

**Test script** 
--
# Reset state before starting tests
POST /reset
200 OK
--
# Get balance for non-existing account
GET /balance?account_id=1234
404 0
--
# Create account with initial balance
POST /event {"type":"deposit", "destination":"100", "amount":10}
201 {"destination": {"id":"100", "balance":10}}
--
# Deposit into existing account
POST /event {"type":"deposit", "destination":"100", "amount":10}
201 {"destination": {"id":"100", "balance":20}}
--
# Get balance for existing account
GET /balance?account_id=100
200 20
--
# Withdraw from non-existing account
POST /event {"type":"withdraw", "origin":"200", "amount":10}
404 0
--
# Withdraw from existing account
POST /event {"type":"withdraw", "origin":"100", "amount":5}
201 {"origin": {"id":"100", "balance":15}}
--
# Transfer from existing account
POST /event {"type":"transfer", "origin":"100", "amount":15, "destination":"300"}
201 {"origin": {"id":"100", "balance":0}, "destination": {"id":"300", "balance":15}}
--
# Transfer from non-existing account
POST /event {"type":"transfer", "origin":"200", "amount":15, "destination":"300"}
404 0

**Run tests!**
✅ Reset state before starting tests
POST /reset
Expected: 200 OK
Got:      200 OK

✅ Get balance for non-existing account
GET /balance?account_id=1234
Expected: 404 0
Got:      404 0

✅ Create account with initial balance
POST /event {"type":"deposit", "destination":"100", "amount":10}
Expected: 201 {"destination": {"id":"100", "balance":10}}
Got:      201 {"destination":{"id":"100","balance":10}}

✅ Deposit into existing account
POST /event {"type":"deposit", "destination":"100", "amount":10}
Expected: 201 {"destination": {"id":"100", "balance":20}}
Got:      201 {"destination":{"id":"100","balance":20}}

✅ Get balance for existing account
GET /balance?account_id=100
Expected: 200 20
Got:      200 20

✅ Withdraw from non-existing account
POST /event {"type":"withdraw", "origin":"200", "amount":10}
Expected: 404 0
Got:      404 0

✅ Withdraw from existing account
POST /event {"type":"withdraw", "origin":"100", "amount":5}
Expected: 201 {"origin": {"id":"100", "balance":15}}
Got:      201 {"origin":{"id":"100","balance":15}}

✅ Transfer from existing account
POST /event {"type":"transfer", "origin":"100", "amount":15, "destination":"300"}
Expected: 201 {"origin": {"id":"100", "balance":0}, "destination": {"id":"300", "balance":15}}
Got:      201 {"origin":{"id":"100","balance":0},"destination":{"id":"300","balance":15}}

✅ Transfer from non-existing account
POST /event {"type":"transfer", "origin":"200", "amount":15, "destination":"300"}
Expected: 404 0
Got:      404 0




