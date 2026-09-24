/**
 * صور المعاينة (Open Graph) — تُرسم عند البناء، واحدة لكل صفحة.
 *
 * واتساب وX يعرضان هذه الصورة حين يُلصق الرابط. صورة واحدة عامّة لكل
 * الموقع تجعل كل رابط يبدو مثل غيره؛ صورة بعنوان الاختبار نفسه ترفع
 * النقر على الرابط المشارَك.
 *
 * resvg يشكّل العربية ويصل حروفها (عبر rustybuzz)، لكنه يقرأ TTF فقط،
 * فتُحوَّل ملفّات WOFF من ‎@fontsource‎ مرّة واحدة إلى مجلّد مؤقّت.
 */
import { readFileSync, writeFileSync, mkdirSync, existsSync } from 'node:fs';
import { join } from 'node:path';
import { tmpdir } from 'node:os';
import { Resvg } from '@resvg/resvg-js';
import { woffToTtf } from '../../tools/woff-to-ttf.mjs';
import { SITE } from '../config.ts';

const FONTS: [ string, string, number ][] = [
	[ 'reem-kufi', 'arabic', 700 ],
	[ 'reem-kufi', 'latin', 700 ],
	[ 'tajawal', 'arabic', 500 ],
	[ 'tajawal', 'latin', 500 ],
	[ 'tajawal', 'arabic', 800 ],
	[ 'tajawal', 'latin', 800 ],
];

let fontFiles: string[] | null = null;

function fonts(): string[] {
	if ( fontFiles ) {
		return fontFiles;
	}

	const dir = join( tmpdir(), 'baseera-og-fonts' );
	mkdirSync( dir, { recursive: true } );

	fontFiles = FONTS.map( ( [ fam, sub, w ] ) => {
		const out = join( dir, `${ fam }-${ sub }-${ w }.ttf` );

		if ( ! existsSync( out ) ) {
			const src = join( process.cwd(), 'node_modules/@fontsource', fam, 'files', `${ fam }-${ sub }-${ w }-normal.woff` );
			writeFileSync( out, woffToTtf( readFileSync( src ) ) );
		}

		return out;
	} );

	return fontFiles;
}

const esc = ( s: string ) => s.replace( /&/g, '&amp;' ).replace( /</g, '&lt;' ).replace( />/g, '&gt;' );

/** تقسيم بعدد الأحرف: resvg لا يلفّ النصّ، والعربية عرضها متقارب بما يكفي. */
function lines( text: string, budget: number, max: number ): string[] {
	const out: string[] = [];
	let line = '';

	for ( const w of text.split( /\s+/ ) ) {
		if ( ( line + ' ' + w ).trim().length > budget && line ) {
			out.push( line );
			line = w;
		} else {
			line = ( line + ' ' + w ).trim();
		}
	}

	if ( line ) {
		out.push( line );
	}

	if ( out.length > max ) {
		const kept = out.slice( 0, max );
		kept[ max - 1 ] = kept[ max - 1 ].replace( /\s*\S+$/, '' ) + '…';

		return kept;
	}

	return out;
}

export function ogPng( title: string, subtitle: string, accent = '#2563eb', kicker = '' ): Buffer {
	const t = lines( title, 24, 3 );
	const s = lines( subtitle, 50, 2 );
	const titleY = 250 - ( t.length - 1 ) * 42;

	const svg = `<svg xmlns="http://www.w3.org/2000/svg" width="1200" height="630" viewBox="0 0 1200 630">
		<defs><radialGradient id="g" cx="85%" cy="10%" r="70%"><stop offset="0" stop-color="${ accent }" stop-opacity=".16"/><stop offset="1" stop-color="${ accent }" stop-opacity="0"/></radialGradient></defs>
		<rect width="1200" height="630" fill="#f8fafc"/>
		<rect width="1200" height="630" fill="url(#g)"/>
		<rect x="0" y="0" width="1200" height="12" fill="${ accent }"/>
		${ kicker ? `<text x="1100" y="${ titleY - 78 }" font-family="Tajawal ExtraBold" font-size="30" fill="${ accent }" text-anchor="end" direction="rtl">${ esc( kicker ) }</text>` : '' }
		${ t.map( ( l, i ) => `<text x="1100" y="${ titleY + i * 84 }" font-family="Reem Kufi" font-weight="700" font-size="68" fill="#0f172a" text-anchor="end" direction="rtl">${ esc( l ) }</text>` ).join( '' ) }
		${ s.map( ( l, i ) => `<text x="1100" y="${ titleY + t.length * 84 + 30 + i * 50 }" font-family="Tajawal Medium" font-size="34" fill="#475569" text-anchor="end" direction="rtl">${ esc( l ) }</text>` ).join( '' ) }
		<g transform="translate(1040 520)"><path d="M4 24C10 14.5 16.5 12 24 12s14 2.5 20 12c-6 9.5-12.5 12-20 12S10 33.5 4 24Z" fill="none" stroke="#2563eb" stroke-width="2.6" stroke-linejoin="round"/><circle cx="24" cy="24" r="6.5" fill="none" stroke="#2563eb" stroke-width="2.6"/><circle cx="24" cy="24" r="2.4" fill="#2563eb"/></g>
		<text x="1025" y="557" font-family="Tajawal ExtraBold" font-size="38" fill="#0f172a" text-anchor="end" direction="rtl">${ esc( SITE.name ) }</text>
		<text x="100" y="557" font-family="Tajawal Medium" font-size="28" fill="#64748b">${ esc( SITE.url.replace( /^https?:\/\//, '' ) ) }</text>
	</svg>`;

	return new Resvg( svg, { font: { fontFiles: fonts(), loadSystemFonts: false, defaultFontFamily: 'Tajawal Medium' } } ).render().asPng();
}
