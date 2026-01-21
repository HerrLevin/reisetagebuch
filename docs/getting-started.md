# Getting Started

Welcome to Reisetagebuch, a travel diary application built with Laravel and Vue.js.

## What is Reisetagebuch?

Reisetagebuch (German for "travel diary") is a web application that allows users to document their travel experiences.
It uses [Transitous](https://transitous.org/) / [motis](https://github.com/motis-project/motis) for real-time public transport data and [OpenStreetMap](https://www.openstreetmap.org/) for single locations.

Users can create different types of posts:

- **Base Posts**: Simple text-based entries
- **Location Posts**: Posts tied to specific geographic locations
- **Transport Posts**: Posts documenting journeys and transportation

Posts can also be synchronized with [Träwelling](https://trawelling.com/).

You can get an overview of all planned features in the repository's [issue tracker](https://github.com/HerrLevin/reisetagebuch/issues).

::: warning
Reisetagebuch is still in **very** early stages of development.
While basic functionality is in place, many features are still being worked on.

I'm trying my best to keep it stable, but expect occasional bugs and breaking changes.
Back up your data regularly!
:::

## Quick Start

1. [Install the application](./installation.md)
   - [Development Setup](./dev-setup.md)
2. [Configure your environment](./configuration.md)
3. Start developing or deploying

## Architecture

The application uses:
- **Backend**: Laravel (PHP)
- **Frontend**: Vue.js with Inertia.js (long term plan to remove Inertia.js)
- **Database**: PostgreSQL with PostGIS
- **Maps**: [MapLibre GL](https://maplibre.org/)
- **Styling**: Tailwind CSS with [DaisyUI](https://daisyui.com/)
