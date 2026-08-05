import i18n from '@/i18n';
import { LocationDto } from '../../types/Api.gen';
const { t } = i18n.global;

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
