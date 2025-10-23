import i18n from '@/i18n';
import { Visibility } from '@/types/enums';
import { Earth, Lock, LucideProps, Moon, Users } from 'lucide-vue-next';
import * as vue from 'vue';

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
        description: t('visibility.authenticated_only_description'),
        label: t('visibility.authenticated_only'),
        icon: Users,
    },
};

export function getIcon(
    visibility: Visibility,
    // eslint-disable-next-line
): vue.FunctionalComponent<LucideProps, {}, any, {}> {
    return visibilityMappings[visibility].icon;
}

export function getDescription(visibility: Visibility): string {
    return visibilityMappings[visibility].description;
}

export function getLabel(visibility: Visibility): string {
    return visibilityMappings[visibility].label;
}
