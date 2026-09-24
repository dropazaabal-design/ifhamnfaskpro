/**
 * ads.txt — يعلن أن Google مخوّل ببيع مساحات هذا الموقع.
 *
 * بلا هذا الملفّ تُخصم أرباح AdSense أو تُحجب بعض الإعلانات. يُبنى من
 * معرّف الناشر في src/config.ts، فلا يُنسى تحديثه.
 */
import type { APIRoute } from 'astro';
import { ADSENSE } from '../config.ts';

export const GET: APIRoute = () => {
	const pub = ADSENSE.client.replace( /^ca-/, '' );
	const body = pub
		? `google.com, ${ pub }, DIRECT, f08c47fec0942fa0\n`
		: '# أضف معرّف ناشر AdSense في src/config.ts ليُولَّد هذا الملفّ.\n';

	return new Response( body, { headers: { 'Content-Type': 'text/plain; charset=utf-8' } } );
};
