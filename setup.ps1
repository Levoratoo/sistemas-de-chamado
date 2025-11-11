Write-Host "========================================" -ForegroundColor Cyan
Write-Host "  SISTEMA DE CHAMADOS - CONFIGURACAO" -ForegroundColor Cyan
Write-Host "========================================" -ForegroundColor Cyan
Write-Host ""

# Verificar se o Composer está instalado
Write-Host "[1/8] Verificando Composer..." -ForegroundColor Yellow
try {
    composer --version | Out-Null
    Write-Host "✓ Composer encontrado" -ForegroundColor Green
} catch {
    Write-Host "✗ Composer não encontrado. Instale em: https://getcomposer.org/" -ForegroundColor Red
    exit 1
}

# Verificar se o Node.js está instalado
Write-Host "[2/8] Verificando Node.js..." -ForegroundColor Yellow
try {
    node --version | Out-Null
    Write-Host "✓ Node.js encontrado" -ForegroundColor Green
} catch {
    Write-Host "✗ Node.js não encontrado. Instale em: https://nodejs.org/" -ForegroundColor Red
    exit 1
}

# Copiar arquivo de configuração
Write-Host "[3/8] Configurando ambiente..." -ForegroundColor Yellow
if (Test-Path "env.example") {
    Copy-Item "env.example" ".env"
    Write-Host "✓ Arquivo .env criado" -ForegroundColor Green
} else {
    Write-Host "✗ Arquivo env.example não encontrado" -ForegroundColor Red
    exit 1
}

# Configurar URL para porta 8080
Write-Host "[4/8] Configurando porta 8080..." -ForegroundColor Yellow
$envContent = Get-Content ".env"
$envContent = $envContent -replace "APP_URL=http://localhost:8000", "APP_URL=http://localhost:8080"
$envContent | Set-Content ".env"
Write-Host "✓ URL configurada para porta 8080" -ForegroundColor Green

# Gerar chave da aplicação
Write-Host "[5/8] Gerando chave da aplicação..." -ForegroundColor Yellow
try {
    php artisan key:generate
    Write-Host "✓ Chave gerada com sucesso" -ForegroundColor Green
} catch {
    Write-Host "✗ Erro ao gerar chave" -ForegroundColor Red
}

# Instalar dependências PHP
Write-Host "[6/8] Instalando dependências PHP..." -ForegroundColor Yellow
try {
    composer install --no-dev --optimize-autoloader
    Write-Host "✓ Dependências PHP instaladas" -ForegroundColor Green
} catch {
    Write-Host "✗ Erro ao instalar dependências PHP" -ForegroundColor Red
}

# Instalar dependências Node.js
Write-Host "[7/8] Instalando dependências Node.js..." -ForegroundColor Yellow
try {
    npm install
    Write-Host "✓ Dependências Node.js instaladas" -ForegroundColor Green
} catch {
    Write-Host "✗ Erro ao instalar dependências Node.js" -ForegroundColor Red
}

# Compilar assets
Write-Host "[8/8] Compilando assets..." -ForegroundColor Yellow
try {
    npm run build
    Write-Host "✓ Assets compilados" -ForegroundColor Green
} catch {
    Write-Host "✗ Erro ao compilar assets" -ForegroundColor Red
}

Write-Host ""
Write-Host "========================================" -ForegroundColor Cyan
Write-Host "  CONFIGURAÇÃO CONCLUÍDA!" -ForegroundColor Green
Write-Host "========================================" -ForegroundColor Cyan
Write-Host ""
Write-Host "PRÓXIMOS PASSOS:" -ForegroundColor Yellow
Write-Host "1. Configure o banco de dados no arquivo .env" -ForegroundColor White
Write-Host "2. Execute: php artisan migrate --seed" -ForegroundColor White
Write-Host "3. Execute: php artisan serve --port=8080" -ForegroundColor White
Write-Host ""
Write-Host "Acesse: http://localhost:8080" -ForegroundColor Green
Write-Host ""
Write-Host "USUÁRIOS DE TESTE:" -ForegroundColor Yellow
Write-Host "- admin@local / password" -ForegroundColor White
Write-Host "- gestor@local / password" -ForegroundColor White
Write-Host "- atendente@local / password" -ForegroundColor White
Write-Host "- usuario@local / password" -ForegroundColor White
Write-Host ""
Read-Host "Pressione Enter para continuar"











