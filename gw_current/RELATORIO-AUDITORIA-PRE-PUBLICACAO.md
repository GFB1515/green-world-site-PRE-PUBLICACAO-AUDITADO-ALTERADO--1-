# GREEN WORLD — Auditoria pré-publicação
Data: 09/09/2026

## Escopo verificado
- Home (`index.html`)
- Catálogo / Produtos (`produtos.html`)
- Tradução PT / EN / ES e persistência de idioma
- 63 produtos, 15 famílias e 14 grupos de aplicação
- Links internos, âncoras, imagens e arquivos locais
- WhatsApp, telefone, e-mail e Instagram
- Responsividade estrutural / controles mobile
- Acessibilidade básica
- SEO técnico básico
- Privacidade / rastreamento do CRM
- PHP do CRM e endpoint de tracking
- Sintaxe JavaScript e PHP
- Peso e arquivos não utilizados

## Correções aplicadas
1. Corrigidos códigos inválidos nos filtros de aplicação:
   - `GWC- T95` -> `GWC-T95`
   - `GR-AG1000` -> `GRWC-AG1000`
   - `GR-AG2000` -> `GRWC-AG2000`
2. Todas as 15 famílias agora possuem descrição em português, inglês e espanhol.
3. Cobertura exata de tradução dos 70 textos técnicos únicos dos produtos: 70/70.
4. Corrigidos erros ortográficos visíveis em português, sem alterar valores técnicos.
5. Campo de busca recebeu rótulo acessível e `aria-label`.
6. Botão do menu mobile recebeu `type="button"`.
7. Link de Política de Privacidade deixou de apontar para Contato e agora abre uma página real, em PT/EN/ES.
8. Criado aviso de privacidade para dados opcionais de análise. O CRM de navegação só carrega após autorização do visitante.
9. CRM: senha de criação do administrador não é mais transportada em URL/base64.
10. Endpoint de tracking endurecido com validação de origem, lista de eventos permitidos e limite de metadados.
11. Adicionados favicon e ícone para iPhone/iPad.
12. Criados `robots.txt` e `404.html`.
13. Removidos arquivos de imagem duplicados/não utilizados, reduzindo o projeto de ~4,3 MB para ~3,1 MB.
14. Atualizada a versão de cache do `i18n.js`.

## Validações concluídas
- JavaScript: sem erros de sintaxe.
- PHP: todos os arquivos passaram em `php -l`.
- Links/arquivos locais: nenhum arquivo ou âncora quebrada encontrado.
- Imagens: nenhuma imagem usada sem texto alternativo.
- Códigos de produtos: 63 produtos e 63 códigos únicos.
- Famílias sem produto: 0.
- Famílias sem descrição: 0.
- Códigos inexistentes em filtros de aplicação: 0.
- Páginas principais responderam HTTP 200 em servidor local.

## Itens que dependem da publicação real
- Ativar HTTPS/SSL no domínio.
- Confirmar o domínio definitivo antes de gerar `sitemap.xml` e URLs canônicas absolutas.
- Configurar MySQL do CRM na HostGator e criar o administrador.
- Depois da configuração, testar login do CRM, gravação de visitas autorizadas e envio de eventos.
- Fazer teste final em iPhone/Android e desktop no domínio público, pois cache/CDN e configurações do servidor só podem ser validados no ambiente real.
