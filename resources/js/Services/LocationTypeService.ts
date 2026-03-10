import i18n from '@/i18n';
import { LocationDto, LocationTagDto } from '../../types/Api.gen';
const { t } = i18n.global;

const osmCategoryToEmoji: Record<string, Record<string, string>> = {
    amenity: {
        restaurant: '🍽️',
        cafe: '☕️',
        bar: '🍸',
        pub: '🍺',
        fast_food: '🍔',
        ice_cream: '🍦',
        biergarten: '🍻',
        museum: '🏛️',
        theatre: '🎭',
        cinema: '🎥',
        nightclub: '🪩',
        arts_centre: '🎨',
        casino: '🎰',
        internet_cafe: '💻',
        public_bookcase: '📚',
        public_bath: '🛁',
        toilets: '🚻',
        waste_basket: '🗑️',
        waste_disposal: '🚮',
        vending_machine: '🤖',
        bench: '🪑',
        shelter: '🏕️',
        drinking_water: '🚰',
        fountain: '⛲',
        bbq: '🍖',
        shower: '🚿',
        bank: '🏦',
        atm: '🏧',
        bureau_de_change: '💱',
        pharmacy: '💊',
        hospital: '🏥',
        doctors: '🩺',
        clinic: '🏨',
        dentist: '🦷',
        veterinary: '🐾',
        post_box: '📮',
        post_office: '🏤',
        parcel_locker: '📦',
        telephone: '☎️',
        parking: '🅿️',
        fuel: '⛽',
        bicycle_parking: '🚲',
        bus_station: '🚌',
        bicycle_rental: '🚴',
        taxi: '🚕',
        charging_station: '🔌',
        car_rental: '🚗',
        parking_entrance: '🅿️',
        ferry_terminal: '⛴️',
        motorcycle_parking: '🏍️',
        bicycle_repair_station: '🔧',
        boat_rental: '🚤',
        police: '👮',
        townhall: '🏛️',
        fire_station: '🚒',
        social_facility: '🏠',
        courthouse: '⚖️',
        place_of_worship: '⛪',
        marketplace: '🛍️',
        car_wash: '🧼',
        vehicle_inspection: '🔍',
        driving_school: '🚦',
        nursing_home: '🏡',
        childcare: '👶',
        kindergarten: '👶',
        hunting_stand: '🏹',
        college: '🎓',
        car_sharing: '🚗',
        community_centre: '🏢',
        research_institute: '🔬',
        school: '🏫',
        music_venue: '🎶',
    },
    shop: {
        supermarket: '🛒',
        bakery: '🥖',
        butcher: '🥩',
        coffee: '☕',
        convenience: '🏪',
        jewelry: '💎',
        chocolate: '🍫',
        books: '📚',
        tobacco: '🚬',
        chemist: '💊',
        clothes: '👗',
        cosmetics: '💄',
        fashion_accessories: '👜',
        ticket: '🎫',
        kiosk: '📰',
        hairdresser: '✂️',
    },
    tourism: {
        artwork: '🖼️',
        community_centre: '🏢',
        library: '📖',
        gallery: '🎨',
        hotel: '🏨',
        attraction: '🎡',
        information: 'ℹ️',
    },
    leisure: {
        outdoor_seating: '🪑',
        amusement_arcade: '🕹️',
        park: '🌳',
        playground: '🛝',
        sports_centre: '🏋️',
    },
    building: {
        school: '🏫',
        university: '🎓',
        kindergarten: '👶',
    },
    healthcare: {
        hospital: '🏥',
        clinic: '🏥',
        doctors: '🩺',
    },
    historic: {
        memorial: '🕊️',
        monument: '🗿',
        archeological_site: '🏺',
        wayside_shrine: '⛩️',
        castle: '🏰',
    },
    highway: {
        bus_stop: '🚌',
    },
    railway: {
        station: '🚉',
        subway_entrance: '🚇',
        tram_stop: '🚊',
    },
    office: {
        lawyer: '⚖️',
    },
    boundary: {
        administrative: '🏛️',
        national_park: '🏞️',
        protected_area: '🛡️',
    },
    bridge: {
        yes: '🌉',
    },
    natural: {
        water: '🌊',
        wood: '🌲',
        forest: '🌳',
        mountain: '⛰️',
        hill: '⛰️',
        peak: '🏔️',
        beach: '🏖️',
        glacier: '🧊',
        cave: '🕳️',
        wetland: '🌾',
        grassland: '🌾',
        heath: '🌾',
        moor: '🌾',
    },
};

const fallbackEmojis: Record<string, string> = {
    shop: '🛒',
    tourism: '📸',
    leisure: '🌳',
    building: '🏬',
    historic: '🗽',
    public_transport: '🚏',
    office: '🧑‍💻',
    boundary: '🏛️',
};

export function osmCategoryToEmojiMapper(
    category: string,
    subcategory: string | null,
): string {
    if (subcategory !== null && osmCategoryToEmoji[category]?.[subcategory]) {
        return osmCategoryToEmoji[category][subcategory];
    }
    return '📍';
}

export function getFallbackEmoji(category: string): string {
    if (fallbackEmojis[category]) {
        return fallbackEmojis[category];
    }
    return '📍';
}

export function getEmojiFromTags(tags: LocationTagDto[]): string {
    for (const tag of tags) {
        const icon = osmCategoryToEmojiMapper(tag.key, tag.value);
        if (icon !== '📍') {
            return icon;
        }
    }
    for (const tag of tags) {
        const icon = getFallbackEmoji(tag.key);
        if (icon !== '📍') {
            return icon;
        }
    }
    return '📍';
}

export function getName(location: LocationDto): string {
    // show platform name if available in tags
    if (location.tags) {
        const platformName = location.tags.find(
            (tag) => tag.key === 'railway:track_ref',
        );
        if (platformName) {
            return t('name_service.platform', {
                name: platformName.value,
                location: location.name,
            });
        }
    }

    return location.name;
}
