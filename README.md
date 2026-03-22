# Vulnerar Agent

![Laravel Badge](https://img.shields.io/badge/Laravel-11|12|13-blue?logo=laravel)
[![tests](https://github.com/vulnerar/agent/actions/workflows/tests.yml/badge.svg)](https://github.com/vulnerar/agent/actions/workflows/tests.yml)

## Installation
To install the agent, follow the steps below:

### 1. Install agent

```bash
composer require vulnerar/agent
```

### 2. Add token to your .env file

```dotenv
VULNERAR_TOKEN=<YOUR-TOKEN>
```

### 3. Run agent
   
The agent must be actively running to collect and send data to Vulnerar. Start the agent using the following Artisan command:

```bash
 php artisan vulnerar:agent
 ```

The agent also uses Laravel's built-in task scheduling to collect periodic events in the background. Use your preferred way
to [run the task scheduler](https://laravel.com/docs/scheduling#running-the-scheduler).

<details>
   <summary><b>Run agent using supervisord (recommended)</b></summary>

   We strongly recommend using supervisord to ensure the agent stays running in the background.

   ```
   [program:vulnerar-agent]
   process_name=%(program_name)s
   command=php /var/www/your-app/artisan vulnerar:agent
   directory=/var/www/your-app
   autostart=true
   autorestart=true
   user=www-data
   numprocs=1
   redirect_stderr=true
   stdout_logfile=/var/log/supervisor/vulnerar-agent.log
   ```
</details>