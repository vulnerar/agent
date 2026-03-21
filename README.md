# Vulnerar Agent

![Laravel Badge](https://img.shields.io/badge/Laravel-11|12|13-blue?logo=laravel)
[![tests](https://github.com/vulnerar/agent/actions/workflows/tests.yml/badge.svg)](https://github.com/vulnerar/agent/actions/workflows/tests.yml)

## Installation
To install the agent, follow the steps below:

1. Install agent

   ```bash
    composer require vulnerar/agent
    ```

2. Add token to your .env file

    ```dotenv
    VULNERAR_TOKEN=<YOUR-TOKEN>
    ```

3. Run agent
   
   ```bash
    php artisan vulnerar:agent
    ```
   The agent uses Laravel's built-in task scheduling to collect periodic events in the background. Use your preferred way to [run the task scheduler](https://laravel.com/docs/scheduling#running-the-scheduler).