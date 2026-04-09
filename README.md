# php-agent

DEPRECATED PHP agentic system

No license, no warrenty of any kind. At your own risk!

## File structure

```text
php-agent/
├─ ajax.php                 # central endpoint for AJAX requests
├─ composer.json            # Composer dependency definitions
├─ composer.lock            # locked dependency versions
├─ config.yml               # main configuration
├─ goals/                   # goal/input artifacts (kept in git via .gitkeep)
│  └─ .gitkeep
├─ index.php                # entry point (routing / bootstrap)
├─ pages/                   # page modules (UI + controller)
│  └─ agent/                # agent page
│     ├─ controller.js      # browser-side controller
│     ├─ controller.php     # server-side controller
│     ├─ style.css          # page styles
│     └─ view.php           # page view
├─ run_agent.php            # script to run the agent
├─ src/                     # core PHP classes
│  ├─ Agent.php             # main agent orchestration
│  ├─ AI/                   # AI providers/adapters
│  │  ├─ AI.php
│  │  └─ GeminiAI.php
│  ├─ Memory/               # memory abstraction
│  │  └─ Memory.php
│  └─ Tools/                # agent tools (filesystem, web, shell, ...)
│     ├─ ApiTool.php
│     ├─ FileTool.php
│     ├─ GoogleSearchTool.php
│     ├─ LLMTool.php
│     ├─ ShellTool.php
│     ├─ Tool.php
│     └─ WebReaderTool.php
└─ vendor/                  # Composer-installed dependencies
```
