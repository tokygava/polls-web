### 1. Установите зависимости

- [Git для Windows](https://git-scm.com/download/win).
- [PHP 8.4 для Windows](https://www.php.net/downloads.php?os=windows&version=8.4) — архив **x64 Non Thread Safe**. Распакуйте его так, чтобы `php.exe` и папка `ext` находились в `%USERPROFILE%\php`.
- [PostgreSQL 18 для Windows](https://www.postgresql.org/download/windows/). При установке оставьте порт `5432` и запомните пароль пользователя `postgres`.

### 2. Склонируйте репозиторий

В PowerShell перейдите в папку, куда хотите скачать проект, и выполните:

```powershell
git clone https://github.com/tokygava/polls-web.git
Set-Location polls-web
```

### 3. Создайте базу и загрузите схему

Выполните эти команды из корня проекта **один раз**. При запросе вводите пароль пользователя `postgres`. Если PostgreSQL установлен в другую папку, укажите свой путь к `psql.exe`.

```powershell
$psql = 'C:\Program Files\PostgreSQL\18\bin\psql.exe'

& $psql -h 127.0.0.1 -p 5432 -U postgres -W -d postgres -v ON_ERROR_STOP=1 -c 'CREATE DATABASE polls_web_app;'
```

После сообщения `CREATE DATABASE` загрузите таблицы:

```powershell
& $psql -h 127.0.0.1 -p 5432 -U postgres -W -d polls_web_app -v ON_ERROR_STOP=1 -f database/schema.sql
```

Если база `polls_web_app` уже создана и схема загружена, переходите к следующему шагу.

### 4. Запустите приложение

Выполните команды из корня проекта. Если PHP распакован в другую папку, измените `$phpDir`.

```powershell
$phpDir = "$env:USERPROFILE\php"

$env:DB_HOST = '127.0.0.1'
$env:DB_PORT = '5432'
$env:DB_NAME = 'polls_web_app'
$env:DB_USER = 'postgres'

$password = Read-Host 'Пароль PostgreSQL' -AsSecureString
$env:DB_PASSWORD = [System.Net.NetworkCredential]::new('', $password).Password

& "$phpDir\php.exe" -n -d "extension_dir=$phpDir\ext" -d extension=pdo_pgsql -S 127.0.0.1:8000 -t public
```

Откройте [http://127.0.0.1:8000](http://127.0.0.1:8000).
