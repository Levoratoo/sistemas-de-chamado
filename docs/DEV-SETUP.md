# Guia rápido de execução local

Este documento descreve os comandos necessários para preparar e executar o Sistema de Chamados utilizando os artefatos já incluídos no repositório (Composer e Node portáteis).

## 1. Pré-requisitos

- **Windows** com PowerShell 5.1+ ou PowerShell Core.
- **PHP 8.2+** disponível no `PATH`, com a extensão `curl` habilitada (`php --ini` para conferir).
- Permissão de execução de scripts PowerShell:  
  `Set-ExecutionPolicy -Scope CurrentUser RemoteSigned`

> O Node.js portátil (v20.18.1) e o Composer já estão presentes no repositório em `node-v20.18.1-win-x64` e `bin/composer`.

## 2. Setup automático (recomendado)

Na raiz do projeto:

```powershell
.\scripts\setup-dev.ps1
```

O script executa:

1. Verificação de dependências (PHP, Composer local, runtime Node portátil).
2. Criação/atualização do `.env` com `APP_URL=http://localhost:4173` e `VITE_DEV_SERVER_URL=http://localhost:5173`.
3. Criação das pastas exigidas (`storage/framework/*`, `bootstrap/cache`).
4. Instalação das dependências PHP (`php bin/composer install`).
5. Instalação das dependências Node (`node-v20.18.1-win-x64\npm.cmd install`).
6. Geração da `APP_KEY` se estiver vazia.

Ao final, o script exibe os próximos passos.

## 3. Setup manual (alternativo)

Caso prefira executar manualmente:

```powershell
# 1. Ajustar PATH temporário para o Node portátil
$env:PATH = "$PWD\node-v20.18.1-win-x64;$env:PATH"

# 2. Instalar dependências PHP
php bin/composer install

# 3. Instalar dependências Node
node-v20.18.1-win-x64\npm.cmd install

# 4. Preparar arquivo .env (apenas se ainda não existir)
Copy-Item env.example .env
(Get-Content .env) `
  -replace '^APP_URL=.*', 'APP_URL=http://localhost:4173' `
  -replace '^VITE_DEV_SERVER_URL=.*', 'VITE_DEV_SERVER_URL=http://localhost:5173' |
    Set-Content .env

# 5. Criar pastas de cache/session
New-Item -ItemType Directory -Force -Path storage\framework\sessions, storage\framework\cache, storage\framework\views, bootstrap\cache | Out-Null

# 6. Gerar chave de aplicação
php artisan key:generate
```

## 4. Banco de dados

Configure as variáveis `DB_*` no `.env` e execute:

```powershell
php artisan migrate --seed
```

Usuários padrão (após seed):

| Email            | Senha     | Perfil  |
|------------------|-----------|---------|
| admin@local      | password  | Admin   |
| gestor@local     | password  | Gestor  |
| atendente@local  | password  | Suporte |
| usuario@local    | password  | Usuário |

## 5. Servidores de desenvolvimento

Executar o backend Laravel (porta 4173):

```powershell
php artisan serve --host=0.0.0.0 --port=4173
```

Executar o Vite (porta 5173) usando o Node portátil:

```powershell
$env:PATH = "$PWD\node-v20.18.1-win-x64;$env:PATH"
node-v20.18.1-win-x64\npm.cmd run dev
```

- Acesse o sistema via `http://localhost:4173`.
- O Vite continua ouvindo em `http://localhost:5173` para hot reload.

## 6. Comandos úteis

```powershell
# Build de produção dos assets
node-v20.18.1-win-x64\npm.cmd run build

# Rodar testes
php artisan test

# Limpar caches do Laravel
php artisan optimize:clear
```

---

> Se preferir automatizar o start dos servidores em uma única janela,
> crie atalhos ou use um script PowerShell com `Start-Process` apontando
> para os comandos acima.
