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
	[ 'alexandria', 'arabic', 800 ],
	[ 'alexandria', 'latin', 800 ],
	[ 'ibm-plex-sans-arabic', 'arabic', 500 ],
	[ 'ibm-plex-sans-arabic', 'latin', 500 ],
	[ 'ibm-plex-sans-arabic', 'arabic', 700 ],
	[ 'ibm-plex-sans-arabic', 'latin', 700 ],
	[ 'ibm-plex-mono', 'latin', 500 ],
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

/**
 * علامة الاتجاه من اليمين (RLM) أوّل كل سطر عربي.
 *
 * سطر يبدأ برقم («10 أسئلة») لا يعرف resvg أن فقرته عربية، فيضع الرقم
 * في آخرها. العلامة غير مرئية وتحسم الاتجاه.
 */
const rtl = ( s: string ) => `\u200F${ esc( s ) }`;

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

/**
 * V2: ورق وحبر، والمسطرة نفسها التي في الموقع — بإبرة وشريط برتقالي لهامش
 * الخطأ. من يرى الرابط في واتساب يرى وعد المنصّة قبل أن يفتحها.
 */
export function ogPng( title: string, subtitle: string, accent = '#0d7a68', kicker = '' ): Buffer {
	const t = lines( title, 22, 3 );
	const s = lines( subtitle, 50, 2 );
	const titleY = 214 - ( t.length - 1 ) * 40;
	const INK = '#0f1a19';
	const INK2 = '#4b5b58';
	const PAPER = '#f2f5f3';
	const SIGNAL = '#ff7a1a';

	// المسطرة: من 100 إلى 1100، والحدّ الأدنى يميناً كما في الموقع.
	const ticks = Array.from( { length: 11 }, ( _, i ) => {
		const x = 1100 - i * 100;
		const h = i % 5 === 0 ? 16 : 9;
		return `<rect x="${ x - 1 }" y="${ 506 - h }" width="2" height="${ h }" fill="${ INK }" fill-opacity=".28"/>`;
	} ).join( '' );

	const svg = `<svg xmlns="http://www.w3.org/2000/svg" width="1200" height="630" viewBox="0 0 1200 630">
		<rect width="1200" height="630" fill="${ PAPER }"/>
		${ kicker ? `<rect x="1084" y="${ titleY - 104 }" width="16" height="16" rx="4" fill="${ accent }"/><text x="1070" y="${ titleY - 90 }" font-family="IBM Plex Sans Arabic" font-weight="700" font-size="28" fill="${ accent }" text-anchor="end" direction="rtl">${ rtl( kicker ) }</text>` : '' }
		${ t.map( ( l, i ) => `<text x="1100" y="${ titleY + i * 80 }" font-family="Alexandria" font-weight="800" font-size="66" fill="${ INK }" text-anchor="end" direction="rtl">${ rtl( l ) }</text>` ).join( '' ) }
		${ s.map( ( l, i ) => `<text x="1100" y="${ titleY + t.length * 80 + 22 + i * 48 }" font-family="IBM Plex Sans Arabic" font-weight="500" font-size="32" fill="${ INK2 }" text-anchor="end" direction="rtl">${ rtl( l ) }</text>` ).join( '' ) }
		<rect x="100" y="470" width="1000" height="36" rx="9" fill="#e6ece9"/>
		${ ticks }
		<rect x="460" y="474" width="200" height="28" rx="6" fill="${ SIGNAL }" fill-opacity=".3" stroke="${ SIGNAL }" stroke-width="2.5"/>
		<rect x="557" y="458" width="6" height="60" rx="3" fill="${ INK }"/>
		<circle cx="560" cy="456" r="9" fill="${ INK }"/>
		<g transform="translate(1052 548)" fill="none" stroke="${ INK }" stroke-linecap="round" stroke-linejoin="round">
			<path d="M5 21C10.5 12.8 16.6 9.5 24 9.5S37.5 12.8 43 21c-5.5 8.2-11.6 11.5-19 11.5S10.5 29.2 5 21Z" stroke-width="2.6"/>
			<circle cx="24" cy="21" r="6.2" stroke-width="2.6"/>
			<circle cx="24" cy="21" r="2.6" fill="${ SIGNAL }" stroke="none"/>
			<path d="M8 41h32M8 38v3M16 39v2M24 37v4M32 39v2M40 38v3" stroke-width="2"/>
		</g>
		<text x="1040" y="584" font-family="Alexandria" font-weight="800" font-size="36" fill="${ INK }" text-anchor="end" direction="rtl">${ rtl( SITE.name ) }</text>
		<text x="100" y="584" font-family="IBM Plex Mono" font-weight="500" font-size="24" fill="${ INK2 }">${ esc( SITE.url.replace( /^https?:\/\//, '' ) ) }</text>
	</svg>`;

	return new Resvg( svg, { font: { fontFiles: fonts(), loadSystemFonts: false, defaultFontFamily: 'IBM Plex Sans Arabic' } } ).render().asPng();
}
