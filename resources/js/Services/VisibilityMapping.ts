import { Visibility } from '@/types/enums';
import { Earth, Lock, LucideProps, Moon, Users } from 'lucide-vue-next';
import * as vue from 'vue';

export interface VisibilityMapping {
    description: string;
    label: string;
    // eslint-disable-next-line
    icon: vue.FunctionalComponent<LucideProps, {}, any, {}>;
}

const visibilityMappings: Record<Visibility, VisibilityMapping> = {
    [Visibility.PUBLIC]: {
        description: 'Anyone can see',
        label: 'Public',
        icon: Earth,
    },
    [Visibility.PRIVATE]: {
        description: 'Only visible to you',
        label: 'Private',
        icon: Lock,
    },
    [Visibility.UNLISTED]: {
        description:
            'Hidden for others from dashboard and profile. Only visible to you or others who have the link.',
        label: 'Unlisted',
        icon: Moon,
    },
    [Visibility.ONLY_AUTHENTICATED]: {
        description: 'Only visible to authenticated users',
        label: 'Authenticated Only',
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
