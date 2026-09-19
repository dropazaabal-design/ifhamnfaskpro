/**
 * ينسخ ملفات الخطوط المطلوبة فقط من node_modules إلى assets/fonts.
 *
 * لا نشحن كل ما تحمله حزم الخطوط: عائلة IBM Plex Sans Arabic وحدها تحمل
 * سبعة أوزان في أربعة نطاقات حروف. القالب يحتاج ثلاثة أوزان في نطاقين،
 * فتُنسخ هي فقط، وتُترك بقية الملفات في node_modules.
 */

import { mkdir, copyFile, access, writeFile } from 'node:fs/promises';
import { dirname, join, basename } from 'node:path';
import { fileURLToPath } from 'node:url';

const root = join( dirname( fileURLToPath( import.meta.url ) ), '..' );
const target = join( root, 'assets', 'fonts' );

const wanted = [
	// IBM Plex Sans Arabic — ساكن، ثلاثة أوزان × نطاقان.
	'@fontsource/ibm-plex-sans-arabic/files/ibm-plex-sans-arabic-arabic-400-normal.woff2',
	'@fontsource/ibm-plex-sans-arabic/files/ibm-plex-sans-arabic-arabic-600-normal.woff2',
	'@fontsource/ibm-plex-sans-arabic/files/ibm-plex-sans-arabic-arabic-700-normal.woff2',
	'@fontsource/ibm-plex-sans-arabic/files/ibm-plex-sans-arabic-latin-400-normal.woff2',
	'@fontsource/ibm-plex-sans-arabic/files/ibm-plex-sans-arabic-latin-600-normal.woff2',
	'@fontsource/ibm-plex-sans-arabic/files/ibm-plex-sans-arabic-latin-700-normal.woff2',
	// Cairo — متغيّر، ملف واحد لكل نطاق يغطّي كل الأوزان.
	'@fontsource-variable/cairo/files/cairo-arabic-wght-normal.woff2',
	'@fontsource-variable/cairo/files/cairo-latin-wght-normal.woff2',
];

const licences = [
	[ '@fontsource/ibm-plex-sans-arabic/LICENSE', 'LICENSE-ibm-plex-sans-arabic.txt' ],
	[ '@fontsource-variable/cairo/LICENSE', 'LICENSE-cairo.txt' ],
];

const exists = async ( path ) => {
	try {
		await access( path );
		return true;
	} catch {
		return false;
	}
};

await mkdir( target, { recursive: true } );

let copied = 0;
const missing = [];

for ( const entry of wanted ) {
	const source = join( root, 'node_modules', entry );

	if ( ! ( await exists( source ) ) ) {
		missing.push( entry );
		continue;
	}

	await copyFile( source, join( target, basename( entry ) ) );
	copied += 1;
}

for ( const [ entry, name ] of licences ) {
	const source = join( root, 'node_modules', entry );

	if ( await exists( source ) ) {
		await copyFile( source, join( target, name ) );
	}
}

await writeFile(
	join( target, 'README.md' ),
	[
		'# ملفات الخطوط',
		'',
		'هذه الملفات مُولَّدة بـ `npm run fonts` من حزم Fontsource، وتُلتزم في',
		'المستودع عن قصد حتى يعمل القالب عند المشتري بلا تشغيل npm.',
		'',
		'- IBM Plex Sans Arabic: ساكن، الأوزان 400 و600 و700، نطاقا عربي ولاتيني.',
		'- Cairo: متغيّر، ملف واحد لكل نطاق يغطّي الأوزان 200 إلى 1000.',
		'',
		'كلتا العائلتين برخصة SIL Open Font License 1.1، ونصّها في ملفي',
		'`LICENSE-*.txt` بجانب هذا الملف.',
		'',
		'لا يُحمَّل على الزائر إلا ملفات العائلة المختارة من لوحة التحكم.',
		'',
	].join( '\n' )
);

console.log( `تم نسخ ${ copied } من ${ wanted.length } ملف خط إلى assets/fonts` );

if ( missing.length ) {
	console.error( 'ملفات غير موجودة — شغّل npm install أولاً:' );
	missing.forEach( ( entry ) => console.error( `  - ${ entry }` ) );
	process.exitCode = 1;
}
