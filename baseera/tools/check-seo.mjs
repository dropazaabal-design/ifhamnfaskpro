/**
 * فحص SEO على الموقع المبني فعلاً (dist/)، لا على القوالب.
 *
 * لكل صفحة: لغة واتّجاه، وعنوان واحد، ووصف بطول مفيد، ورابط مرجعي مطلق
 * يطابق مسارها، وصورة معاينة موجودة، وبيانات منظَّمة صالحة، ومعرّفات
 * بلا تكرار، وكل رابط داخلي يصل إلى صفحة موجودة. ثم خريطة الموقع:
 * تحوي كل صفحة قابلة للفهرسة، ولا تحوي غيرها.
 */
import { readFileSync, readdirSync, statSync, existsSync } from 'node:fs';
import { join, relative } from 'node:path';

const DIST = 'dist';
const SITE = 'https://baseera.kitabwbs.com';
const errors = [];
const warn = [];

const walk = ( dir ) =>
	readdirSync( dir ).flatMap( ( f ) => {
		const p = join( dir, f );

		return statSync( p ).isDirectory() ? walk( p ) : [ p ];
	} );

const files = walk( DIST ).filter( ( f ) => f.endsWith( '.html' ) );
const pathOf = ( f ) => '/' + relative( DIST, f ).replace( /index\.html$/, '' ).replace( /\\/g, '/' );
const exists = ( p ) => {
	const clean = decodeURI( p.split( /[?#]/ )[ 0 ] );

	return existsSync( join( DIST, clean ) ) && ( statSync( join( DIST, clean ) ).isFile() || existsSync( join( DIST, clean, 'index.html' ) ) );
};

const indexable = [];

for ( const f of files ) {
	const html = readFileSync( f, 'utf8' );
	const path = pathOf( f );
	const err = ( m ) => errors.push( `${ path } — ${ m }` );
	const noindex = /<meta name="robots" content="noindex/.test( html );

	if ( ! /<html lang="ar" dir="rtl">/.test( html ) ) err( 'ينقص lang="ar" dir="rtl"' );

	const h1 = ( html.match( /<h1[\s>]/g ) ?? [] ).length;
	if ( h1 !== 1 ) err( `${ h1 } عنوان h1 (يجب واحد)` );

	const title = html.match( /<title>([^<]*)<\/title>/ )?.[ 1 ] ?? '';
	if ( ! title ) err( 'بلا <title>' );
	else if ( title.length > 75 ) warn.push( `${ path } — العنوان ${ title.length } حرفاً، قد يُقتطع في نتائج البحث` );

	const desc = html.match( /<meta name="description" content="([^"]*)"/ )?.[ 1 ] ?? '';
	if ( ! noindex && ( desc.length < 60 || desc.length > 170 ) ) err( `الوصف ${ desc.length } حرفاً (المفيد 60–170)` );

	const canon = html.match( /<link rel="canonical" href="([^"]*)"/ )?.[ 1 ];
	if ( ! canon ) err( 'بلا canonical' );
	else if ( ! noindex && path !== '/404.html' && canon !== SITE + path ) err( `canonical ${ canon } لا يطابق ${ SITE + path }` );

	const og = html.match( /<meta property="og:image" content="([^"]*)"/ )?.[ 1 ];
	if ( ! og ) err( 'بلا og:image' );
	else if ( ! noindex && ! exists( og.replace( SITE, '' ) ) ) err( `صورة المعاينة غير موجودة: ${ og }` );

	for ( const [ , json ] of html.matchAll( /<script type="application\/ld\+json">([\s\S]*?)<\/script>/g ) ) {
		try {
			const data = JSON.parse( json );
			if ( data[ '@context' ] !== 'https://schema.org' ) err( 'JSON-LD بلا @context' );
			const types = ( data[ '@graph' ] ?? [ data ] ).map( ( n ) => n[ '@type' ] );
			if ( types.includes( 'FAQPage' ) && ! /<details>/.test( html ) ) err( 'FAQPage في البيانات بلا أسئلة ظاهرة في الصفحة' );
		} catch ( e ) {
			err( `JSON-LD غير صالح: ${ e.message }` );
		}
	}

	const ids = [ ...html.matchAll( /\sid="([^"]+)"/g ) ].map( ( m ) => m[ 1 ] );
	const dup = ids.filter( ( id, i ) => ids.indexOf( id ) !== i );
	if ( dup.length ) err( `معرّفات مكرّرة: ${ [ ...new Set( dup ) ].join( '، ' ) }` );

	for ( const [ , href ] of html.matchAll( /<a [^>]*href="(\/[^"]*)"/g ) ) {
		if ( ! exists( href ) ) err( `رابط داخلي مكسور: ${ href }` );
	}

	for ( const [ img ] of html.matchAll( /<img\b[^>]*>/g ) ) {
		if ( ! /\salt="/.test( img ) ) err( `صورة بلا alt: ${ img.slice( 0, 60 ) }` );
	}

	if ( ! noindex && path !== '/404.html' ) indexable.push( SITE + path );
}

// خريطة الموقع.
const smFiles = readdirSync( DIST ).filter( ( f ) => /^sitemap-\d+\.xml$/.test( f ) );
const inMap = new Set( smFiles.flatMap( ( f ) => [ ...readFileSync( join( DIST, f ), 'utf8' ).matchAll( /<loc>([^<]+)<\/loc>/g ) ].map( ( m ) => m[ 1 ] ) ) );

for ( const u of indexable ) if ( ! inMap.has( u ) ) errors.push( `خريطة الموقع لا تحوي ${ u }` );
for ( const u of inMap ) if ( ! indexable.includes( u ) ) errors.push( `خريطة الموقع تحوي صفحة غير قابلة للفهرسة: ${ u }` );

const robots = readFileSync( join( DIST, 'robots.txt' ), 'utf8' );
if ( ! robots.includes( `Sitemap: ${ SITE }/sitemap-index.xml` ) ) errors.push( 'robots.txt لا يشير إلى خريطة الموقع' );

console.log( `فُحصت ${ files.length } صفحة · ${ indexable.length } قابلة للفهرسة · ${ inMap.size } في خريطة الموقع` );
warn.forEach( ( w ) => console.log( `  ⚠ ${ w }` ) );

if ( errors.length ) {
	console.error( `\n${ errors.length } خطأً:` );
	errors.forEach( ( e ) => console.error( `  ✗ ${ e }` ) );
	process.exit( 1 );
}

console.log( 'كل صفحة لها عنوان واحد ووصف وcanonical وصورة معاينة وبيانات منظَّمة صالحة، ولا رابط داخلي مكسور.' );
