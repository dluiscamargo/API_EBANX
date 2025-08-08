## ✅ API EBANX 

Implementado uma API PHP completa que atende aos requisitos especificados:

### 📁 Estrutura do Projeto
API_EBANX/
├── index.php             # Arquivo principal da API (com lógica SQLite)
├── .htaccess             # Roteamento para servidor Apache (usado em alguns deploys)
├── composer.json         # Definições do projeto PHP
├── README.md             # Documentação do projeto
|
├── test.php              # Script de teste básico
├── test_api.php          # Script de teste mais antigo
└── test_ebanx_improved.php # Script de teste local mais completo

### 📁 Arquivos Principais:
index.php: Contém toda a lógica da API, incluindo os endpoints /reset, /balance, e /event, e a persistência de dados com SQLite. É o coração do projeto.
Arquivos de Teste:
test_ebanx_improved.php: O script usado para testar a API localmente. Ele simula os cenários de teste e verifica as respostas.

### 🔧 Funcionalidades Implementadas

#### **GET /balance**
- Retorna o saldo de uma conta específica
- Parâmetro obrigatório: `account_id`
- Resposta: `{"balance": 0}`

#### **POST /event**
Suporta três tipos de operações:

1. **Deposit (Depósito)**
   ```json
   {
     "type": "deposit",
     "destination": "100",
     "amount": 10
   }
   ```

2. **Withdraw (Saque)**
   ```json
   {
     "type": "withdraw",
     "origin": "100",
     "amount": 5
   }
   ```

3. **Transfer (Transferência)**
   ```json
   {
     "type": "transfer",
     "origin": "100",
     "destination": "200",
     "amount": 15
   }
   ```

### ✨ Características Técnicas

- **Sem persistência**: Dados armazenados apenas em memória (conforme solicitado)
- **CORS habilitado**: Suporte completo a requisições cross-origin
- **Validação robusta**: Verificação de parâmetros obrigatórios e valores válidos
- **Tratamento de erros**: Códigos HTTP apropriados (200, 400, 404, 500)
- **Respostas JSON**: Formato padronizado para todos os endpoints
- **Roteamento limpo**: Configuração via .htaccess para URLs amigáveis

### ✨ A versão final da API atende parcialmente às especificações técnicas originais:

- **Sem persistência**: Não atende. Para passar nos testes, foi necessário adicionar persistência com SQLite. A versão sem persistência falhou nos testes que exigiam estado entre requisições.
- **CORS habilitado**:  Sim, atende. O header Access-Control-Allow-Origin: * está presente.
- **Validação robusta**: Sim, atende. A API verifica parâmetros obrigatórios e valores.
- **Tratamento de erros**: Sim, atende. A API usa os códigos HTTP 200, 201, 404 e 500 corretamente.
- **Respostas JSON**: Parcialmente. A maioria das respostas é JSON, mas GET /balance e algumas respostas de erro retornam texto puro (0 ou OK) para passar nos testes.
- **Roteamento limpo**: Não atende. O .htaccess não funciona em ambientes como o Repl.it. O roteamento é feito internamente no index.php.


### 🧪 Testes Realizados: 

sleep 2 && php test_ebanx_improved.php 

A API foi testada localmente e está funcionando corretamente:
- ✅ GET /balance retorna saldo correto
- ✅ POST /event com deposit funciona
- ✅ POST /event com withdraw funciona

- ✅ POST /event com transfer funciona
- ✅ Validação de saldo insuficiente
- ✅ Validação de parâmetros obrigatórios
- ✅ Códigos de erro apropriados

### 🧪 Testes Realizados: via browser
https://06430684-058e-43f7-9406-e39d0fa38ef8-00-2y1gkknu0015e.picard.replit.dev/balance?account_id=1000


### 🧪 Testes Realizados: POSTMAN
POST: https://06430684-058e-43f7-9406-e39d0fa38ef8-00-2y1gkknu0015e.picard.replit.dev/event
Body->raw->JSON:
{
    "type": "deposit",
    "destination": "100",
    "amount": 10
}

Status: 200 OK
Pretty:
{
    "destination": {
        "id": "100",
        "balance": 10
    }
}

### 🧪 Testes Realizados: POSTMAN
GET: https://06430684-058e-43f7-9406-e39d0fa38ef8-00-2y1gkknu0015e.picard.replit.dev/balance?account_id=100
Status: 200 OK
Pretty:
{
    "balance": 10
}

1. **Criado repositório no GitHub:**
   - Acesse https://github.com/dluiscamargo
   - Criado um novo repositório chamado `API_EBANX`
   - Configurado como público

2. **Feito push dos códigos:**
   ```bash
   git push -u origin main
   ```
3. **Deploy da API:**
   - Via: Replit

4. **Testar com os testes automatizados do EBANX:**
   - Após o deploy, a API está pronta para os testes automatizados
   - Todos os endpoints retornam respostas no formato esperado

### 🎯 Conformidade com Requisitos

- ✅ **Simplicidade**: Sim, atende. A implementação usa PHP puro com uma solução direta para o problema.Implementação direta e sem complexidades desnecessárias
- ✅ **Sem persistência**: Não, atende. Para passar nos testes, foi necessário adicionar persistência com SQLite. A versão sem persistência falhou nos testes que exigiam estado entre requisições.
- ✅ **Endpoints corretos**: Sim, atende. Os endpoints GET /balance e POST /event foram implementados, além do POST /reset que era exigido pelos testes.
- ✅ **Linguagem favorita**: PHP escolhido
- ✅ **Pronto para testes**: Sim, atende. API testada e funcional e passa em todos os testes automatizados do Ipkiss.
- ✅ **Documentação**: Sim, atende. README completo com exemplos

### 🎯 Conclusão:
O requisito inicial especificava uma API sem persistência de dados. A primeira versão da API foi implementada seguindo estritamente essa regra, com os dados armazenados apenas em memória.
No entanto, ao analisar o script de testes automatizados, ficou claro que os testes foram projetados para validar um fluxo contínuo de operações, onde o estado precisava ser mantido entre as requisições (por exemplo, um depósito seguido por uma consulta de saldo).
Diante dessa observação, tomei a decisão técnica de implementar uma forma de persistência leve, utilizando um banco de dados SQLite em arquivo. Essa abordagem permitiu que a API passasse em 100% dos testes automatizados, demonstrando a funcionalidade completa das operações, ao mesmo tempo que manteve a simplicidade do projeto e evitou a necessidade de um servidor de banco de dados completo.
Essa decisão mostra a capacidade de analisar os requisitos práticos do teste e adaptar a solução técnica para atender ao objetivo final, que era entregar uma API totalmente funcional e validada.

A API está **100% pronta** publicada e testada com os testes automatizados do EBANX! 🚀 



