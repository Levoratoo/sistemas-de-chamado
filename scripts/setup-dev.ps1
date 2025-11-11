<#
.SYNOPSIS
Prepara o ambiente de desenvolvimento local para o Sistema de Chamados.

.DESCRIPTION
Executa as etapas necessárias para instalar dependências PHP e Node,
configurar o arquivo .env e garantir as pastas exigidas pelo Laravel.
Pressupondo que PHP 8.2+ (com extensão curl habilitada) esteja disponível
no PATH. Utiliza o runtime Node portátil incluído no repositório.
#>

Set-StrictMode -Version Latest
$ErrorActionPreference = 'Stop'

$repoRoot = Resolve-Path -Path (Join-Path $PSScriptRoot '..')
Set-Location $repoRoot

Write-Host "=== Sistema de Chamados - Setup local ===" -ForegroundColor Cyan

# Verificações básicas
Write-Host "[1/7] Verificando dependências locais..." -ForegroundColor Yellow
if (-not (Get-Command php -ErrorAction SilentlyContinue)) {
    throw "PHP não encontrado no PATH. Instale PHP 8.2+ e tente novamente."
}

$nodeRoot = Join-Path $repoRoot 'node-v20.18.1-win-x64'
if (-not (Test-Path (Join-Path $nodeRoot 'node.exe'))) {
    throw "Runtime Node portátil não encontrado em $nodeRoot."
}

$composerBin = Join-Path $repoRoot 'bin\composer'
if (-not (Test-Path $composerBin)) {
    throw "Composer local nao encontrado em $composerBin. Instale o Composer (https://getcomposer.org/download/) e mova o binario para bin\composer."
}

$env:PATH = "$nodeRoot;$env:PATH"

Write-Host "[2/7] Garantindo arquivo .env..." -ForegroundColor Yellow
if (-not (Test-Path '.env')) {
    if (Test-Path 'env.example') {
        Copy-Item 'env.example' '.env'
        Write-Host "Arquivo .env criado a partir de env.example." -ForegroundColor Green
    } else {
        throw "Arquivo env.example não encontrado."
    }
} else {
    Write-Host ".env já existente - mantendo arquivo atual." -ForegroundColor Green
}

Write-Host "[3/7] Ajustando variáveis de ambiente padrão..." -ForegroundColor Yellow
$envContent = Get-Content '.env'
if ($envContent -notmatch '^APP_URL=') {
    Add-Content '.env' "`nAPP_URL=http://localhost:4173"
}
$envContent = (Get-Content '.env') `
    -replace '^APP_URL=.*', 'APP_URL=http://localhost:4173' `
    -replace '^VITE_DEV_SERVER_URL=.*', 'VITE_DEV_SERVER_URL=http://localhost:5173'

if ($envContent -notmatch '^VITE_DEV_SERVER_URL=') {
    $envContent += "`nVITE_DEV_SERVER_URL=http://localhost:5173"
}
$envContent | Set-Content '.env'

Write-Host "[4/7] Criando pastas de cache/storage..." -ForegroundColor Yellow
$storageFolders = @(
    'storage\framework',
    'storage\framework\sessions',
    'storage\framework\cache',
    'storage\framework\views',
    'bootstrap\cache'
)
foreach ($folder in $storageFolders) {
    if (-not (Test-Path $folder)) {
        New-Item -ItemType Directory -Path $folder | Out-Null
    }
}
Write-Host "Pastas obrigatórias ok." -ForegroundColor Green

Write-Host "[5/7] Instalando dependências PHP..." -ForegroundColor Yellow
& php $composerBin 'install'

Write-Host "[6/7] Instalando dependências Node..." -ForegroundColor Yellow
& (Join-Path $nodeRoot 'npm.cmd') 'install'

Write-Host "[7/7] Gerando chave da aplicação (se necessário)..." -ForegroundColor Yellow
$envKeyLine = (Get-Content '.env') | Where-Object { $_ -match '^APP_KEY=' }
if ($envKeyLine -and $envKeyLine -match 'APP_KEY=$' ) {
    php artisan key:generate
    Write-Host "Chave de aplicação gerada." -ForegroundColor Green
} else {
    Write-Host "Chave já configurada - nenhuma ação." -ForegroundColor Green
}

Write-Host ""
Write-Host "Setup concluído!" -ForegroundColor Cyan
Write-Host ""
Write-Host "Próximos passos sugeridos:" -ForegroundColor Yellow
Write-Host " 1) Configure o banco em .env (DB_*)." -ForegroundColor Gray
Write-Host " 2) Execute: php artisan migrate --seed" -ForegroundColor Gray
Write-Host " 3) Inicie os servidores:" -ForegroundColor Gray
Write-Host "      php artisan serve --host=0.0.0.0 --port=4173" -ForegroundColor Gray
Write-Host "      $($nodeRoot)\npm.cmd run dev" -ForegroundColor Gray
Write-Host ""


