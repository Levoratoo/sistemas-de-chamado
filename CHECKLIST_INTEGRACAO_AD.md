# Checklist de Integração com Active Directory

## 📋 Informações Técnicas do AD

### 1. Informações de Conexão
- [ ] **Domínio (FQDN)**: Ex: `empresa.local` ou `empresa.com.br`
- [ ] **Servidores DC (Domain Controllers)**:
  - IP ou hostname do DC primário: `_________________`
  - IP ou hostname do DC secundário (se houver): `_________________`
- [ ] **Porta LDAP**:
  - LDAP padrão: `389`
  - LDAPS (SSL): `636`
- [ ] **Protocolo a ser usado**:
  - [ ] LDAP (não criptografado)
  - [ ] LDAPS (SSL/TLS) - **RECOMENDADO**
- [ ] **Certificado SSL** (se LDAPS):
  - Certificado do CA do AD está disponível? `[ ] Sim [ ] Não`
  - Onde está o arquivo? `_________________`

### 2. Credenciais de Serviço
- [ ] **Usuário de Serviço** para consultas no AD:
  - Login: `_________________`
  - Password: `_________________`
  - Formato DN: `_________________`
  - Exemplo: `CN=svc_sistema,OU=Service Accounts,DC=empresa,DC=local`
- [ ] **Permissões necessárias**:
  - [ ] Leitura de usuários
  - [ ] Leitura de grupos
  - [ ] Validação de credenciais (bind)

### 3. Estrutura do AD

#### Base DN (onde procurar usuários)
- [ ] **Base DN**: `_________________`
  - Exemplo: `OU=Users,DC=empresa,DC=local`
  - Ou: `DC=empresa,DC=local` (se for toda a árvore)

#### Filtros de Busca
- [ ] **Filtro para usuários ativos**: 
  - Padrão: `(&(objectClass=user)(!(userAccountControl:1.2.840.113556.1.4.803:=2)))`
  - Customizado: `_________________`

#### Campos (Attributes) a mapear
- [ ] **sAMAccountName** → campo `login` no sistema
- [ ] **userPrincipalName** ou **mail** → campo `email` no sistema
- [ ] **displayName** ou **cn** → campo `name` no sistema
- [ ] **distinguishedName** → para identificação única
- [ ] **memberOf** → grupos do AD (para roles/permissões)
- [ ] Outros campos desejados:
  - [ ] `department` → departamento
  - [ ] `title` → cargo
  - [ ] `telephoneNumber` → telefone
  - [ ] `company` → empresa

### 4. Mapeamento de Grupos/Roles

#### Grupos do AD → Roles do Sistema
- [ ] **Grupo Administradores**:
  - Nome do grupo AD: `_________________`
  - DN do grupo: `_________________`
  - Role no sistema: `admin`

- [ ] **Grupo Gestores**:
  - Nome do grupo AD: `_________________`
  - DN do grupo: `_________________`
  - Role no sistema: `gestor`

- [ ] **Grupo Atendentes**:
  - Nome do grupo AD: `_________________`
  - DN do grupo: `_________________`
  - Role no sistema: `atendente`

- [ ] **Usuários padrão**:
  - Se não estiver em nenhum grupo acima, role: `usuario`

### 5. Autenticação

#### Método de Login
- [ ] **Como o usuário vai fazer login?**
  - [ ] Usando `sAMAccountName` (ex: `joao.silva`)
  - [ ] Usando `userPrincipalName` (ex: `joao.silva@empresa.local`)
  - [ ] Ambos aceitos

#### Fluxo de Autenticação
- [ ] **Estratégia**:
  - [ ] Apenas autenticação AD (sem sincronização)
  - [ ] Autenticação AD + sincronização automática no BD
  - [ ] Autenticação AD + criação automática de usuário no BD

#### Sincronização
- [ ] **Frequência de sincronização**:
  - [ ] Em tempo real (toda vez que logar)
  - [ ] Agendada (cron job): `_________________`
  - [ ] Manual (comando artisan)

- [ ] **Sincronizar automaticamente**:
  - [ ] Nome completo
  - [ ] E-mail
  - [ ] Grupos/Roles
  - [ ] Departamentos
  - [ ] Cargos

## 🔒 Segurança e Rede

### 6. Infraestrutura de Rede
- [ ] **Servidor Laravel pode acessar os DCs?**
  - [ ] Mesma rede/VLAN
  - [ ] Precisa liberar firewall?
  - [ ] Precisa VPN?

- [ ] **Portas a liberar no firewall**:
  - [ ] Porta `389` (LDAP) - IPs: `_________________`
  - [ ] Porta `636` (LDAPS) - IPs: `_________________`
  - [ ] Porta `3268` (Global Catalog) - se necessário

- [ ] **IP do servidor Laravel**: `_________________`

### 7. Políticas de Segurança
- [ ] **Política de senha expirada**: Como tratar?
  - [ ] Impedir login e avisar usuário
  - [ ] Redirecionar para troca de senha

- [ ] **Conta desabilitada no AD**: Como tratar?
  - [ ] Impedir login
  - [ ] Desativar usuário no sistema

- [ ] **Rate Limiting**: Quantas tentativas por minuto?
  - Sugestão: `5 tentativas / minuto`

## 💻 Informações do Ambiente Laravel

### 8. Configuração do Servidor
- [ ] **Sistema Operacional**: `_________________`
  - [ ] Windows Server
  - [ ] Linux (qual distro?): `_________________`

- [ ] **PHP versão**: `_________________`
  - Mínimo necessário: PHP 8.1+

- [ ] **Extensão PHP LDAP instalada?**
  - [ ] Sim
  - [ ] Não (precisa instalar)

- [ ] **Composer packages necessários**:
  - [ ] `adldap2/adldap2` (recomendado)
  - [ ] Ou `directorytree/ldaprecord` (alternativa)

### 9. Banco de Dados
- [ ] **Manter estrutura atual?**
  - Campos atuais: `login`, `email`, `name`, `password`, `role_id`, `team_id`
  - Campo `password` pode ficar NULL para usuários AD
  - Campo `ad_guid` ou `ad_dn` para vincular usuário ao AD?

- [ ] **Migração de dados existentes**:
  - Quantos usuários já existem? `_________________`
  - Precisa mapear usuários existentes para AD? `[ ] Sim [ ] Não`

## 🔄 Fluxo de Integração

### 10. Comportamento Desejado

#### Primeiro Login
- [ ] O que acontece quando usuário AD faz primeiro login?
  - [ ] Criar usuário automaticamente no BD
  - [ ] Sincronizar todos os dados do AD
  - [ ] Solicitar informações adicionais (se necessário)

#### Logins Subsequentes
- [ ] Validar senha no AD toda vez?
  - [ ] Sim (recomendado)
  - [ ] Não (usar sessão local)

- [ ] Atualizar dados do AD automaticamente?
  - [ ] Sim, sempre
  - [ ] Não, manter como está
  - [ ] Sim, apenas se mudou no AD

#### Logout
- [ ] Comportamento padrão do Laravel (OK)

### 11. Gestão de Usuários

- [ ] **Administradores podem criar usuários manualmente?**
  - [ ] Sim (para casos especiais)
  - [ ] Não (tudo via AD)

- [ ] **Sincronização de usuários removidos do AD?**
  - [ ] Desativar automaticamente
  - [ ] Deletar (cuidado com dados históricos)
  - [ ] Manter mas bloquear login

- [ ] **Permissões de áreas (tickets)**:
  - [ ] Mapear grupos AD → áreas do sistema
  - [ ] Configurar manualmente no sistema

## 📝 Observações Importantes

### 12. Pontos de Atenção
- [ ] **Performance**: Consultas ao AD podem ser lentas
  - Solução: Cache de autenticação
  - Tempo de cache sugerido: `5-15 minutos`

- [ ] **Fallback**: Se AD estiver indisponível?
  - [ ] Permitir login com usuários locais
  - [ ] Bloquear todos os logins
  - [ ] Mostrar mensagem de manutenção

- [ ] **Logs**: Logar tentativas de login AD?
  - [ ] Sim
  - [ ] Não

- [ ] **Testes**: Ambiente de homologação AD disponível?
  - [ ] Sim: `_________________`
  - [ ] Não (testar em produção - não recomendado)

---

## ✅ Próximos Passos Após Preenchimento

1. Revisar checklist com equipe de TI/Infraestrutura
2. Validar informações com administrador do AD
3. Preparar ambiente de testes (se houver)
4. Instalar dependências PHP necessárias
5. Configurar pacote LDAP no Laravel
6. Implementar Provider customizado
7. Testar conexão e autenticação
8. Mapear grupos → roles
9. Implementar sincronização
10. Testar fluxo completo
11. Documentar para usuários finais

---

**Preenchido por**: `_________________`  
**Data**: `_________________`  
**Revisado por**: `_________________`  
**Data de revisão**: `_________________`



