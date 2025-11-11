# 🚀 Instruções de Instalação - Windows

## ⚡ Instalação Rápida (Recomendada)

### 1. Execute o script de configuração automática:
```cmd
setup.bat
```

### 2. Configure o banco de dados:
Edite o arquivo `.env` e configure:
```env
DB_DATABASE=sistema_chamados
DB_USERNAME=root
DB_PASSWORD=sua_senha_do_mysql
```

### 3. Execute o servidor:
```cmd
start-server.bat
```

**Acesse: http://localhost:8080**

---

## 🔧 Instalação Manual

### Pré-requisitos
- [PHP 8.2+](https://windows.php.net/download/)
- [Composer](https://getcomposer.org/download/)
- [Node.js](https://nodejs.org/)
- [MySQL](https://dev.mysql.com/downloads/mysql/)

### Passo a Passo

#### 1. Instalar dependências PHP
```cmd
composer install
```

#### 2. Instalar dependências Node.js
```cmd
npm install
```

#### 3. Configurar ambiente
```cmd
copy env.example .env
php artisan key:generate
```

#### 4. Configurar banco de dados
Edite o arquivo `.env`:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=sistema_chamados
DB_USERNAME=root
DB_PASSWORD=sua_senha
```

#### 5. Criar banco de dados
```sql
CREATE DATABASE sistema_chamados;
```

#### 6. Executar migrações
```cmd
php artisan migrate --seed
```

#### 7. Compilar assets
```cmd
npm run build
```

#### 8. Iniciar servidor
```cmd
php artisan serve --port=8080
```

---

## 🐳 Usando Docker (Alternativa)

### 1. Subir os serviços
```cmd
docker-compose up -d
```

### 2. Executar migrações
```cmd
docker-compose exec app php artisan migrate --seed
```

### 3. Compilar assets
```cmd
docker-compose exec app npm run build
```

**Acesse: http://localhost:8080**

---

## 👥 Usuários de Teste

Após executar os seeders, você terá acesso aos seguintes usuários:

| Email | Senha | Role | Descrição |
|-------|-------|------|-----------|
| admin@local | password | Admin | Acesso total ao sistema |
| gestor@local | password | Gestor | Gestão de equipes e SLAs |
| atendente@local | password | Atendente | Atendimento de chamados |
| usuario@local | password | Usuário | Abertura de chamados |

---

## 🔧 Solução de Problemas

### Erro: "Composer não encontrado"
- Instale o Composer: https://getcomposer.org/download/
- Adicione o Composer ao PATH do Windows

### Erro: "Node.js não encontrado"
- Instale o Node.js: https://nodejs.org/
- Reinicie o terminal após a instalação

### Erro: "MySQL não encontrado"
- Instale o MySQL: https://dev.mysql.com/downloads/mysql/
- Configure o MySQL e crie o banco `sistema_chamados`

### Erro: "Porta 8080 já está em uso"
- Altere a porta no comando: `php artisan serve --port=8081`
- Ou pare o serviço que está usando a porta 8080

### Erro: "Chave da aplicação não gerada"
```cmd
php artisan key:generate
```

### Erro: "Migrações não executadas"
```cmd
php artisan migrate:fresh --seed
```

---

## 📱 Acessos

- **Sistema**: http://localhost:8080
- **PHPMyAdmin**: http://localhost:8081 (se usando Docker)
- **API**: http://localhost:8080/api

---

## 🎯 Próximos Passos

1. **Configure o banco de dados** no arquivo `.env`
2. **Execute as migrações**: `php artisan migrate --seed`
3. **Inicie o servidor**: `php artisan serve --port=8080`
4. **Acesse o sistema**: http://localhost:8080
5. **Faça login** com um dos usuários de teste

---

## 📞 Suporte

Se encontrar problemas:
1. Verifique se todos os pré-requisitos estão instalados
2. Confirme se o banco de dados está configurado corretamente
3. Execute `php artisan config:clear` para limpar cache
4. Verifique os logs em `storage/logs/laravel.log`











