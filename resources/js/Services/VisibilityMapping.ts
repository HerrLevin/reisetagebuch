import i18n from '@/i18n';
import { Visibility } from '@/types/enums';
import { Earth, Lock, LucideProps, Moon, Users } from 'lucide-vue-next';
import * as vue from 'vue';
import { Visibility as ApiVisibility } from '../../types/Api.gen';

const { t } = i18n.global;

export interface VisibilityMapping {
    description: string;
    label: string;
    // eslint-disable-next-line
    icon: vue.FunctionalComponent<LucideProps, {}, any, {}>;
}

const visibilityMappings: Record<Visibility, VisibilityMapping> = {
    [Visibility.PUBLIC]: {
        description: t('visibility.public_description'),
        label: t('visibility.public'),
        icon: Earth,
    },
    [Visibility.PRIVATE]: {
        description: t('visibility.private_description'),
        label: t('visibility.private'),
        icon: Lock,
    },
    [Visibility.UNLISTED]: {
        description: t('visibility.unlisted_description'),
        label: t('visibility.unlisted'),
        icon: Moon,
    },
    [Visibility.ONLY_AUTHENTICATED]: {
        description: t('visibility.only_authenticated_description'),
        label: t('visibility.only_authenticated'),
        icon: Users,
    },
};

export function getVisibilityIcon(
    visibility: Visibility | ApiVisibility,
    // eslint-disable-next-line
): vue.FunctionalComponent<LucideProps, {}, any, {}> {
    return visibilityMappings[visibility].icon;
}

export function getVisibilityDescription(
    visibility: Visibility | ApiVisibility,
): string {
    return visibilityMappings[visibility].description;
}

export function getVisibilityLabel(
    visibility: Visibility | ApiVisibility,
): string {
    return visibilityMappings[visibility].label;
}
