# GREEN WORLD CRM — versão integrada

O projeto mantém o site público como está e adiciona um CRM privado em `/crm/`.

## O que registra
- visitante anônimo com ID próprio;
- data/hora, páginas acessadas e origem/UTM;
- entrada na página Produtos;
- produtos abertos;
- cliques em WhatsApp, cotação, amostra, documentação e e-mail;
- Lead Score para priorizar oportunidades.

O sistema **não tenta descobrir secretamente nome, e-mail ou telefone**. Enquanto a pessoa não se identifica, aparece como `Visitante #...`. Quando o contato chegar, você pode cadastrar os dados no CRM e manter todo o histórico daquele visitante.

## Lead Score inicial
- página comum: +1
- página Produtos: +5
- abrir produto: +10
- e-mail: +15
- WhatsApp: +20
- documentação técnica: +20
- cotação: +30
- amostra: +40

## Publicar na HostGator
1. No cPanel da HostGator, crie um **Banco de Dados MySQL** e um usuário com acesso total a esse banco.
2. Envie todo este projeto para o domínio, normalmente dentro de `public_html`.
3. Use PHP 8.1 ou superior com `PDO_MYSQL` habilitado.
4. Acesse `https://SEU-DOMINIO/crm/setup.php`.
5. Informe host (normalmente `localhost`), nome do banco, usuário e senha MySQL.
6. Na mesma tela, crie seu usuário e senha do CRM.
7. Depois acesse `https://SEU-DOMINIO/crm/`.

O instalador cria automaticamente as tabelas `gw_users`, `gw_visitors` e `gw_events`. O arquivo com a conexão (`crm/config.local.php`) é protegido por `.htaccess`.

## Segurança e privacidade
- senha do CRM armazenada com `password_hash`;
- sessão administrativa HttpOnly/SameSite;
- proteção CSRF nas edições;
- IP salvo somente como hash, não em texto puro;
- painel administrativo separado do site público.

Antes da publicação real, informe esse tratamento de dados na Política de Privacidade e defina a base legal/consentimento aplicáveis à operação conforme a LGPD.
