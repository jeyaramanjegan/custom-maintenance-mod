# Custom Maintenance Mode

A lightweight WordPress plugin that allows administrators to temporarily put a website into maintenance mode during deployments, updates, and production maintenance.

## Features

- Enable or disable maintenance mode
- Custom maintenance page title
- Custom maintenance message
- Optional maintenance end time
- Logged-in administrators can access the normal website
- Normal visitors see the maintenance page
- Returns HTTP 503 Service Unavailable
- Lightweight and easy to use
- No third-party plugin dependencies

## Installation

1. Download or clone this repository.
2. Copy the `custom-maintenance-mode` folder to:

   `wp-content/plugins/`

3. Go to **WordPress Admin → Plugins**.
4. Activate **Custom Maintenance Mode**.

## Configuration

After activating the plugin, go to:

**Settings → Maintenance Mode**

From there you can:

- Enable or disable maintenance mode
- Set the maintenance page title
- Add a custom maintenance message
- Set an optional maintenance end time

## How It Works

When maintenance mode is enabled, normal visitors will see the maintenance page.

Logged-in administrators can continue to access the normal website, which allows them to perform maintenance and deployment work.

The plugin returns an HTTP `503 Service Unavailable` response because the website is temporarily unavailable.

## Plugin Structure

```text
custom-maintenance-mode/
├── custom-maintenance-mode.php
├── includes/
│   └── class-custom-maintenance-mode.php
├── assets/
│   └── css/
│       └── maintenance.css
├── README.md
├── readme.txt
├── .gitignore
└── LICENSE
