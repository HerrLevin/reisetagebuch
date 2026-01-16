import i18n from '@/i18n';
import {
    BriefcaseBusinessIcon,
    BuildingIcon,
    LucideProps,
    ShipWheel,
    ShoppingBasketIcon,
    TreePalm,
} from 'lucide-vue-next';
import * as vue from 'vue';
import { TravelReason } from '../../types/Api.gen';

const { t } = i18n.global;

export interface TravelReasonMapping {
    description: string;
    label: string;
    // eslint-disable-next-line
    icon: vue.FunctionalComponent<LucideProps, {}, any, {}> | null;
}

const travelReasonMappings: Record<TravelReason, TravelReasonMapping> = {
    [TravelReason.Business]: {
        description: t('travel_reason.business_description'),
        label: t('travel_reason.business'),
        icon: BriefcaseBusinessIcon,
    },
    [TravelReason.Commute]: {
        description: t('travel_reason.commute_description'),
        label: t('travel_reason.commute'),
        icon: BuildingIcon,
    },
    [TravelReason.Errand]: {
        description: t('travel_reason.errand_description'),
        label: t('travel_reason.errand'),
        icon: ShoppingBasketIcon,
    },
    [TravelReason.Crew]: {
        description: t('travel_reason.crew_description'),
        label: t('travel_reason.crew'),
        icon: ShipWheel,
    },
    [TravelReason.Leisure]: {
        description: t('travel_reason.leisure_description'),
        label: t('travel_reason.leisure'),
        icon: TreePalm,
    },
    [TravelReason.Other]: {
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
