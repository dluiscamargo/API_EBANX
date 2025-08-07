# API EBANX - Simple Banking API

Uma API simples em PHP para operações bancárias básicas, implementada sem persistência de dados (armazenamento em memória).

## Endpoints

### GET /balance
Retorna o saldo de uma conta específica.

**Parâmetros:**
- `account_id` (obrigatório): ID da conta

**Exemplo:**
```bash
GET /balance?account_id=100
```

**Resposta:**
```json
{
  "balance": 0
}
```

### POST /event
Executa operações bancárias (depósito, saque, transferência).

**Tipos de eventos suportados:**

#### 1. Deposit (Depósito)
```json
{
  "type": "deposit",
  "destination": "100",
  "amount": 10
}
```

**Resposta:**
```json
{
  "destination": {
    "id": "100",
    "balance": 10
  }
}
```

#### 2. Withdraw (Saque)
```json
{
  "type": "withdraw",
  "origin": "100",
  "amount": 5
}
```

**Resposta:**
```json
{
  "origin": {
    "id": "100",
    "balance": 5
  }
}
```

#### 3. Transfer (Transferência)
```json
{
  "type": "transfer",
  "origin": "100",
  "destination": "200",
  "amount": 15
}
```

**Resposta:**
```json
{
  "origin": {
    "id": "100",
    "balance": 0
  },
  "destination": {
    "id": "200",
    "balance": 15
  }
}
```

## Características

- **Sem persistência**: Dados armazenados apenas em memória
- **CORS habilitado**: Suporte a requisições cross-origin
- **Validação de entrada**: Verificação de parâmetros obrigatórios
- **Tratamento de erros**: Respostas de erro apropriadas
- **Códigos de status HTTP**: Uso correto de códigos de resposta

## Códigos de Status

- `200`: Sucesso
- `400`: Erro de validação (parâmetros inválidos ou saldo insuficiente)
- `404`: Endpoint não encontrado
- `500`: Erro interno do servidor

## Requisitos

- PHP 7.4 ou superior
- Servidor web com suporte a mod_rewrite (Apache) ou configuração similar

## Instalação

1. Clone o repositório
2. Configure seu servidor web para apontar para o diretório do projeto
3. Certifique-se de que o mod_rewrite está habilitado (Apache)
4. A API estará disponível nos endpoints `/balance` e `/event`

## Testes

A API foi projetada para passar nos testes automatizados do EBANX. Todos os endpoints retornam respostas no formato JSON esperado pelos testes. 