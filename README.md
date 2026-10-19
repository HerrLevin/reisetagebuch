
> [!WARNING]  
> This project is currently for my personal enjoyment and is not intended for production use.
> It is a work in progress and may contain bugs or incomplete features.
> Contributions in the form of issues are welcome but please refrain from submitting pull requests at this time.
> 
> Thanks! :)

## About Reisetagebuch

Reisetagebuch is a travel diary application built with Laravel.
It allows users to document their travels, share experiences, and connect with fellow travelers.
The application is designed to be self hosted, providing users with full control over their data and privacy.

It's a crossover between [Träwelling](https://github.com/traewelling/traewelling) and [Swarm](https://swarmapp.com/).

## Current Features
- User authentication, profiles and invite-only registration
- Check-ins into [OSM](https://openstreetmap.org)-based locations & [Transitous](https://transitous.com/)/[motis](https://github.com/motis-project/motis) vehicles
- Manual GPX/GeoJSON track upload for transport check-ins
- Map view of your location history and check-ins
- Follows, likes and notifications
- ActivityPub federation, so other Fediverse servers can follow and interact with your posts (see [federation docs](https://docs.reisetagebu.ch/activitypub-extensions))
- Cross-posting to [Träwelling](https://traewelling.de)
- A documented REST API ([OpenAPI](https://docs.reisetagebu.ch/)) powering web, iOS and Android clients

## Planned Features
- Photo uploads
- Full feature ActivityPub integration
- Statistics and analytics
- Swarm import
- Träwelling import
- more... (See [project board](https://github.com/users/HerrLevin/projects/2))

## Mobile App (Capacitor)

The iOS and Android apps wrap a standalone Capacitor build of the SPA. See
[Mobile Apps](https://docs.reisetagebu.ch/dev-setup#mobile-apps-capacitor) in the docs for the build/sync steps
Currently the apps are not available in the App Store or Play Store, but you can build them yourself using the instructions in the docs.

# Documentation

The full documentation can be found at [https://docs.reisetagebu.ch](https://docs.reisetagebu.ch/)

