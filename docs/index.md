---
# https://vitepress.dev/reference/default-theme-home-page
layout: home

hero:
  name: "Reisetagebuch"
  text: ""
  tagline: "A self-hosted travel diary built with Laravel. Like Träwelling meets Swarm. 🚂📍📓 "
  image: /images/logo.png
  
  actions:
    - theme: brand
      text: Get Started
      link: /getting-started
    - theme: alt
      text: Installation Guide
      link: /installation

features:
  - title: Installation
    details: "Get Reisetagebuch up and running quickly with our step-by-step installation guide."
    link: /installation
  - title: Local Development
    details: "Set up a local dev environment with Laravel Sail."
    link: /dev-setup
  - title: Configuration
    details: "Environment variables for registration, geo/transit data sources and Träwelling cross-posting."
    link: /configuration
  - title: ActivityPub Federation
    details: "How Reisetagebuch federates location and transport posts to the Fediverse."
    link: /activitypub-extensions
---

