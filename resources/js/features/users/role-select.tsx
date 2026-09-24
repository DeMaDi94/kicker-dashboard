import InputError from '@/components/input-error';
import { Label } from '@/components/ui/label';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { useTranslation } from '@/hooks/use-translation';
import { roleLabel } from './role-label';
import type { Role } from './types';

/*
 * The role field of the create and edit forms. It has no preselected role:
 * the admin picks one. A locked field still submits the current role, since
 * a disabled select sends nothing.
 */
export function RoleSelect({
    roles,
    defaultValue,
    lockedReason,
    error,
}: {
    roles: Role[];
    defaultValue?: Role | null;
    lockedReason?: string | null;
    error?: string;
}) {
    const { t } = useTranslation();
    const locked = Boolean(lockedReason);

    return (
        <div className="grid gap-2">
            <Label htmlFor="role">{t('Role')}</Label>

            <Select
                name={locked ? undefined : 'role'}
                defaultValue={defaultValue ?? undefined}
                disabled={locked}
            >
                <SelectTrigger id="role" className="w-full">
                    <SelectValue placeholder={t('Choose a role')} />
                </SelectTrigger>
                <SelectContent>
                    {roles.map((role) => (
                        <SelectItem key={role} value={role}>
                            {roleLabel(role, t)}
                        </SelectItem>
                    ))}
                </SelectContent>
            </Select>

            {locked && defaultValue && (
                <input type="hidden" name="role" value={defaultValue} />
            )}

            {lockedReason && (
                <p className="text-sm text-brand-muted">{lockedReason}</p>
            )}

            <InputError message={error} />
        </div>
    );
}
