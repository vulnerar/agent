# Vulnerar Agent

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

   The agent uses Laravel's built-in queue system and task scheduling to synchronize events in the background. Use your preferred way to [run the queue worker](https://laravel.com/docs/queues#running-the-queue-worker) and [task scheduler](https://laravel.com/docs/scheduling#running-the-scheduler).