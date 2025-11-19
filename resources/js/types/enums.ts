export enum TransportMode {
    WALK = 'WALK',
    BIKE = 'BIKE',
    RENTAL = 'RENTAL',
    CAR = 'CAR',
    CAR_PARKING = 'CAR_PARKING',
    CAR_DROPOFF = 'CAR_DROPOFF',
    ODM = 'ODM',
    FLEX = 'FLEX',
    TRANSIT = 'TRANSIT',
    TRAM = 'TRAM',
    SUBWAY = 'SUBWAY',
    FERRY = 'FERRY',
    AIRPLANE = 'AIRPLANE',
    SUBURBAN = 'SUBURBAN',
    BUS = 'BUS',
    COACH = 'COACH',
    RAIL = 'RAIL',
    HIGHSPEED_RAIL = 'HIGHSPEED_RAIL',
    LONG_DISTANCE = 'LONG_DISTANCE',
    NIGHT_RAIL = 'NIGHT_RAIL',
    REGIONAL_FAST_RAIL = 'REGIONAL_FAST_RAIL',
    REGIONAL_RAIL = 'REGIONAL_RAIL',
    CABLE_CAR = 'CABLE_CAR',
    FUNICULAR = 'FUNICULAR',
    AERIAL_LIFT = 'AERIAL_LIFT',
    OTHER = 'OTHER',
    AREAL_LIFT = 'AREAL_LIFT',
    METRO = 'METRO',
}

export enum Visibility {
    PUBLIC = 'public',
    PRIVATE = 'private',
    UNLISTED = 'unlisted',
    ONLY_AUTHENTICATED = 'only-authenticated',
}

export enum TravelReason {
    COMMUTE = 'commute',
    BUSINESS = 'business',
    LEISURE = 'leisure',
    CREW = 'crew',
    ERRAND = 'errand',
    OTHER = 'other',
}

export enum TravelRole {
    DEADHEAD = 'deadhead',
    OPERATOR = 'operator',
    CATERING = 'catering',
}
