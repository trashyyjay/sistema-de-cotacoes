# Sistema de Cotações

![PHP](https://img.shields.io/badge/PHP-777BB4?style=for-the-badge&logo=php&logoColor=white)
![MySQL](https://img.shields.io/badge/MySQL-005C84?style=for-the-badge&logo=mysql&logoColor=white)
![Bootstrap](https://img.shields.io/badge/Bootstrap-563D7C?style=for-the-badge&logo=bootstrap&logoColor=white)

## 📖 Descrição

O **Sistema de Cotações** é uma aplicação web completa desenvolvida em PHP para gerenciar e automatizar o processo de criação de cotações comerciais. O sistema foi projetado para ser uma ferramenta interna, permitindo que usuários cadastrados gerenciem clientes, produtos e gerem orçamentos detalhados em formato PDF de maneira rápida e eficiente.

Este projeto foi construído do zero, com foco na organização do código, segurança e usabilidade, servindo como uma solução prática para pequenas e médias empresas.

---

## ✨ Funcionalidades Principais

O sistema conta com um conjunto robusto de funcionalidades, divididas em módulos:

* **🔐 Autenticação de Usuários:**
    * Tela de login segura para acesso ao painel administrativo.
    * Controle de sessão para proteger as páginas internas.

* **👤 Gerenciamento de Usuários (CRUD):**
    * Cadastro, visualização, edição e exclusão de usuários que podem acessar o sistema.

* **👥 Gerenciamento de Clientes (CRUD):**
    * Cadastro, visualização, edição e exclusão da base de clientes da empresa.

* **📦 Gerenciamento de Produtos (CRUD):**
    * Cadastro, visualização, edição e exclusão de produtos, incluindo nome, descrição e valor.

* **📄 Módulo de Cotações:**
    * Criação de novas cotações, associando um cliente e selecionando múltiplos produtos.
    * Cálculo automático do valor total da cotação.
    * Listagem e gerenciamento das cotações já criadas.

* **🖨️ Geração de PDF:**
    * Exportação de cada cotação para um arquivo PDF profissional, pronto para ser enviado ao cliente.
    * O PDF inclui os dados da empresa, do cliente, os produtos listados, valores e o total.

---

## 🛠️ Tecnologias Utilizadas

Este projeto foi construído utilizando as seguintes tecnologias e ferramentas:

* **Backend:** PHP 7+ (Linguagem principal para a lógica do servidor)
* **Frontend:** HTML5, CSS3, JavaScript
* **Framework CSS:** [Bootstrap](https://getbootstrap.com/) (Para criação de uma interface responsiva e moderna)
* **Banco de Dados:** MySQL (Para armazenamento de todos os dados da aplicação)
* **Biblioteca para PDF:** [FPDF](http://www.fpdf.org/) (Utilizada para gerar os documentos PDF dinamicamente)

---

## 🚀 Como Executar o Projeto Localmente

Para rodar este projeto no seu ambiente de desenvolvimento, siga os passos abaixo:

1.  **Pré-requisitos:**
    * Ter um ambiente de servidor local como [XAMPP](https://www.apachefriends.org/pt_br/index.html) ou [WAMP](https://www.wampserver.com/) instalado.
    * Ter um gerenciador de banco de dados como phpMyAdmin ou DBeaver.

2.  **Clone o Repositório:**
    ```bash
    git clone [https://github.com/hectorhansen/sistema-de-cotacoes.git](https://github.com/hectorhansen/sistema-de-cotacoes.git)
    ```

3.  **Configure o Banco de Dados:**
    * Crie um novo banco de dados no seu MySQL (ex: `cotacoes_db`).
    * Importe o arquivo `.sql` do projeto (se houver) ou crie as tabelas manualmente conforme a estrutura da aplicação. As principais tabelas são `usuarios`, `clientes`, `produtos` e `cotacoes`.

4.  **Configure a Conexão:**
    * Localize o arquivo de conexão (ex: `conexao.php`).
    * Altere as credenciais de acesso ao banco de dados (host, nome de usuário, senha e nome do banco) para as do seu ambiente local.
    * **IMPORTANTE:** Este arquivo deve ser listado no `.gitignore` para não expor dados sensíveis.

5.  **Inicie o Servidor:**
    * Mova a pasta do projeto para o diretório `htdocs` (no XAMPP) ou `www` (no WAMP).
    * Inicie os módulos Apache e MySQL do seu servidor.
    * Acesse o projeto pelo seu navegador, geralmente em `http://localhost/sistema-de-cotacoes`.

---

## 🖼️ Telas do Sistema (Opcional)

*(Dica: Tire prints das principais telas do seu sistema e adicione aqui para deixar seu portfólio mais visual!)*

**Tela de Login:**
`![Tela de Login](caminho/para/sua/imagem_login.png)`

**Dashboard Principal:**
`![Dashboard](caminho/para/sua/imagem_dashboard.png)`

**Geração de PDF:**
`![PDF Gerado](caminho/para/sua/imagem_pdf.png)`

---