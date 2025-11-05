import i18n from '@/i18n';
import { TravelReason } from '@/types/enums';
import {
    BriefcaseBusinessIcon,
    BuildingIcon,
    LucideProps,
    ShipWheel,
    ShoppingBasketIcon,
    TreePalm,
} from 'lucide-vue-next';
import * as vue from 'vue';

const { t } = i18n.global;

export interface TravelReasonMapping {
    description: string;
    label: string;
    // eslint-disable-next-line
    icon: vue.FunctionalComponent<LucideProps, {}, any, {}> | null;
}

const travelReasonMappings: Record<TravelReason, TravelReasonMapping> = {
    [TravelReason.BUSINESS]: {
        description: t('travel_reason.business_description'),
        label: t('travel_reason.business'),
        icon: BriefcaseBusinessIcon,
    },
    [TravelReason.COMMUTE]: {
        description: t('travel_reason.commute_description'),
        label: t('travel_reason.commute'),
        icon: BuildingIcon,
    },
    [TravelReason.ERRAND]: {
        description: t('travel_reason.errand_description'),
        label: t('travel_reason.errand'),
        icon: ShoppingBasketIcon,
    },
    [TravelReason.CREW]: {
        description: t('travel_reason.crew_description'),
        label: t('travel_reason.crew'),
        icon: ShipWheel,
    },
    [TravelReason.LEISURE]: {
        description: t('travel_reason.leisure_description'),
        label: t('travel_reason.leisure'),
        icon: TreePalm,
    },
    [TravelReason.OTHER]: {
        description: t('travel_reason.other_description'),
        label: t('travel_reason.other'),
        icon: null,
    },
};

export function getTravelReasonIcon(
    travelReason: TravelReason,
    // eslint-disable-next-line
): vue.FunctionalComponent<LucideProps, {}, any, {}> | null {
    return travelReasonMappings[travelReason].icon;
}

export function getTravelReasonDescription(travelReason: TravelReason): string {
    return travelReasonMappings[travelReason].description;
}

export function getTravelReasonLabel(travelReason: TravelReason): string {
    return travelReasonMappings[travelReason].label;
}
