import { User } from '@/types';
import { usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

export function usePermissions() {
    const page = usePage();

    const user = computed<User | null>(() => page.props.auth.user || null);

    const hasRole = (role: string): boolean => {
        return user.value?.role?.includes(role) ?? false;
    };

    const hasAnyRole = (roles: string[]): boolean => {
        return roles.includes(user.value?.role ?? 'guest') ?? false;
    };

    const hasPermissionTo = (permission: string): boolean => {
        return user.value?.permissions?.includes(permission) ?? false;
    };

    const hasAnyPermissionTo = (permissions: string[]): boolean => {
        return user.value?.permissions?.some((permission) => permissions.includes(permission)) ?? false;
    };

    const hasAllPermissionsTo = (permissions: string[]): boolean => {
        return permissions.every((permission) => user.value?.permissions?.includes(permission));
    };

    return { hasRole, hasAnyRole, hasPermissionTo, hasAnyPermissionTo, hasAllPermissionsTo };
}
