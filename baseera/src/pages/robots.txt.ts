import type { APIRoute } from 'astro';
import { SITE } from '../config.ts';

export const GET: APIRoute = () =>
	new Response(
		[ 'User-agent: *', 'Allow: /', 'Disallow: /me/', '', `Sitemap: ${ SITE.url }/sitemap-index.xml`, '' ].join( '\n' ),
		{ headers: { 'Content-Type': 'text/plain; charset=utf-8' } }
	);
