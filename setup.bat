@echo off
echo ========================================
echo  SISTEMA DE CHAMADOS - CONFIGURACAO
echo ========================================
echo.

echo [1/6] Copiando arquivo de configuracao...
copy env.example .env
if errorlevel 1 (
    echo ERRO: Nao foi possivel copiar o arquivo .env
    pause
    exit /b 1
)

echo [2/6] Configurando URL para porta 8080...
powershell -Command "(Get-Content .env) -replace 'APP_URL=http://localhost:8000', 'APP_URL=http://localhost:8080' | Set-Content .env"

echo [3/6] Gerando chave da aplicacao...
php artisan key:generate

echo [4/6] Instalando dependencias PHP...
composer install --no-dev --optimize-autoloader

echo [5/6] Instalando dependencias Node.js...
npm install

echo [6/6] Compilando assets...
npm run build

echo.
echo ========================================
echo  CONFIGURACAO CONCLUIDA!
echo ========================================
echo.
echo PROXIMOS PASSOS:
echo 1. Configure o banco de dados no arquivo .env
echo 2. Execute: php artisan migrate --seed
echo 3. Execute: php artisan serve --port=8080
echo.
echo Acesse: http://localhost:8080
echo.
echo USUARIOS DE TESTE:
echo - admin@local / password
echo - gestor@local / password  
echo - atendente@local / password
echo - usuario@local / password
echo.
pause











