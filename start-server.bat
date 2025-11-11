@echo off
echo ========================================
echo  INICIANDO SERVIDOR - PORTA 8080
echo ========================================
echo.

echo Verificando se o banco de dados esta configurado...
php artisan migrate:status >nul 2>&1
if errorlevel 1 (
    echo.
    echo ATENCAO: Configure o banco de dados no arquivo .env primeiro!
    echo.
    echo Exemplo de configuracao:
    echo DB_DATABASE=sistema_chamados
    echo DB_USERNAME=root
    echo DB_PASSWORD=sua_senha
    echo.
    pause
    exit /b 1
)

echo Executando migracoes e seeders...
php artisan migrate --seed

echo.
echo Iniciando Vite (janela minimizada)...
REM Usa o Node/NPM embarcado no projeto
powershell -NoProfile -ExecutionPolicy Bypass -Command "Start-Process -FilePath '.\\node-v20.18.1-win-x64\\npm.cmd' -ArgumentList 'run','dev' -WindowStyle Minimized"

echo.
echo ========================================
echo  SERVIDOR INICIADO!
echo ========================================
echo.
echo Acesse: http://localhost:8080
echo.
echo USUARIOS DE TESTE:
echo - admin@local / password
echo - gestor@local / password
echo - atendente@local / password
echo - usuario@local / password
echo.
echo Pressione Ctrl+C para parar o servidor
echo.

php artisan serve --port=8080


