import { defineConfig } from 'astro/config';
import sitemap from '@astrojs/sitemap';

/*
 * العنوان مكرَّر هنا من src/config.ts لأن ملف الإعداد يُقرأ قبل أن يُحلّ
 * TypeScript. غيّر الاثنين معاً.
 */
const SITE_URL = 'https://baseera.kitabwbs.com';

export default defineConfig( {
	site: SITE_URL,
	trailingSlash: 'always',
	build: { format: 'directory', inlineStylesheets: 'auto', assets: 'assets' },
	compressHTML: true,
	integrations: [
		sitemap( {
			// ملفّي شخصي، وصفحة الخطأ لا تُفهرس.
			filter: ( page ) => ! page.includes( '/me/' ) && ! page.includes( '/404' ),
			i18n: { defaultLocale: 'ar', locales: { ar: 'ar' } },
		} ),
	],
} );
