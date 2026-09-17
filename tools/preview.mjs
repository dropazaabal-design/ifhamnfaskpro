/**
 * يبني معاينة HTML قابلة للفتح في متصفح، بلا تنصيب ووردبريس.
 *
 * يأخذ مخرَج tools/render-smoke.php ويحوّل مساراته إلى مسارات محلية، فتُعرض
 * القوالب بورقة التنسيق المبنية وبـ JavaScript فعّال. مفيد لمعاينة تغيير
 * في الواجهة بسرعة قبل رفعه إلى موقع حقيقي.
 *
 * الاستخدام:
 *   npm run preview
 *   npx serve .        (أو أي خادم ملفات ساكن)
 *   افتح /.preview/index.html
 *
 * ملاحظة: المعاينة لا تستدعي ووكومرس ولا قاعدة البيانات، فلا نتائج بحث
 * فعلية فيها ولا منتجات. الغرض هو الهيكل والتنسيق والسلوك.
 */

import { execFileSync } from 'node:child_process';
import { mkdir, writeFile } from 'node:fs/promises';
import { dirname, join } from 'node:path';
import { fileURLToPath } from 'node:url';

const root = join( dirname( fileURLToPath( import.meta.url ) ), '..' );
const out = join( root, '.preview' );

await mkdir( out, { recursive: true } );

const html = execFileSync(
	'php',
	[ join( root, 'tools', 'render-smoke.php' ), '--woocommerce', '--print' ],
	{ encoding: 'utf8', maxBuffer: 16 * 1024 * 1024 }
);

const placeholder = ( w, h, fill, label ) =>
	`<svg xmlns="http://www.w3.org/2000/svg" width="${ w }" height="${ h }" viewBox="0 0 ${ w } ${ h }">` +
	`<rect width="${ w }" height="${ h }" fill="${ fill }"/>` +
	`<text x="50%" y="50%" text-anchor="middle" dominant-baseline="middle" ` +
	`font-family="sans-serif" font-size="${ Math.max( 14, Math.round( w / 34 ) ) }" fill="#6B7280">${ label }</text></svg>`;

await writeFile( join( out, 'image.svg' ), placeholder( 1600, 900, '#E6E0D8', '[ image ]' ) );

const localised = html
	.replace( /https:\/\/example\.test\/wp-content\/themes\/matjar-pro\//g, '../' )
	.replace( /https:\/\/example\.test\/i\.jpg/g, 'image.svg' )
	.replace( '<!-- wp_head -->', '<link rel="stylesheet" href="../assets/css/main.css">' )
	.replace(
		'<!-- wp_footer -->',
		'<script>window.matjarPro={restUrl:"/wp-json/wc/store/v1/",nonce:"preview",isRtl:true,' +
			'strings:{noResults:"لا توجد منتجات مطابقة",seeAll:"عرض كل النتائج"},' +
			'currency:{code:"",symbol:""}};</script>\n<script src="../assets/js/main.js"></script>'
	);

await writeFile( join( out, 'index.html' ), localised );

console.log( 'المعاينة جاهزة: .preview/index.html' );
console.log( 'شغّل خادم ملفات ساكن من جذر القالب ثم افتح /.preview/index.html' );
