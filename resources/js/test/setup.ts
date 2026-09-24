import '@testing-library/jest-dom/vitest';
import { cleanup } from '@testing-library/react';
import { afterEach, vi } from 'vitest';
import { translate } from '@/lib/i18n';

afterEach(() => {
    cleanup();
});

/*
 * Components read their text through useTranslation(), which needs an Inertia
 * page. Outside one, `t()` answers with the English key — the source text —
 * so a test asserts the words it can read in the code. A test that needs a
 * catalogue mocks the hook itself.
 */
vi.mock('@/hooks/use-translation', () => ({
    useTranslation: () => ({
        t: (key: string, replacements?: Record<string, string | number>) =>
            translate({}, key, replacements),
        locale: 'en',
        locales: ['en'],
    }),
}));
