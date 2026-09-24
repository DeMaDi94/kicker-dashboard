import type { Translate } from '@/hooks/use-translation';
import type { Role } from './types';

/** B13 — a role's label; the stored value is never shown. */
export function roleLabel(role: Role | null, t: Translate): string {
    switch (role) {
        case 'admin':
            return t('Admin');
        case 'user':
            return t('User');
        default:
            return '—';
    }
}
