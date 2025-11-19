import i18n from '@/i18n';
import { TravelRole } from '@/types/enums';

const { t } = i18n.global;

export interface TravelRoleMapping {
    description: string;
    label: string;
}

const travelRoleMapping: Record<TravelRole, TravelRoleMapping> = {
    [TravelRole.DEADHEAD]: {
        description: t('travel_role.deadhead_description'),
        label: t('travel_role.deadhead'),
    },
    [TravelRole.OPERATOR]: {
        description: t('travel_role.operator_description'),
        label: t('travel_role.operator'),
    },
    [TravelRole.CATERING]: {
        description: t('travel_role.catering_description'),
        label: t('travel_role.catering'),
    },
};

export function getTravelRoleDescription(travel_role: TravelRole): string {
    return travelRoleMapping[travel_role].description;
}

export function getTravelRoleLabel(travel_role: TravelRole): string {
    return travelRoleMapping[travel_role].label;
}
