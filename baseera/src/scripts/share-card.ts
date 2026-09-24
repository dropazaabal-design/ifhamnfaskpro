/**
 * صورة النتيجة بمقاس القصص (1080×1920) — سناب شات وتيك توك وحالة واتساب.
 *
 * تُرسم في المتصفّح بـ Canvas لسببين: المتصفّح يشكّل العربية ويصلها
 * صحيحةً بلا مكتبة، والنتيجة لا تغادر الجهاز لتُرسم على خادم.
 *
 * الصورة تحمل نطاق الثقة وجملة «تقدير لا حكم»: من يرى القصّة يرى الصدق
 * نفسه الذي رآه صاحبها.
 */
import type { ScaleResult } from '../lib/scoring.ts';
import { num } from '../lib/format.ts';

const W = 1080;
const H = 1920;

function wrap( ctx: CanvasRenderingContext2D, text: string, maxWidth: number ): string[] {
	const words = text.split( /\s+/ );
	const lines: string[] = [];
	let line = '';

	for ( const w of words ) {
		const test = line ? `${ line } ${ w }` : w;

		if ( ctx.measureText( test ).width > maxWidth && line ) {
			lines.push( line );
			line = w;
		} else {
			line = test;
		}
	}

	if ( line ) {
		lines.push( line );
	}

	return lines;
}

function roundRect( ctx: CanvasRenderingContext2D, x: number, y: number, w: number, h: number, r: number ) {
	ctx.beginPath();
	ctx.moveTo( x + r, y );
	ctx.arcTo( x + w, y, x + w, y + h, r );
	ctx.arcTo( x + w, y + h, x, y + h, r );
	ctx.arcTo( x, y + h, x, y, r );
	ctx.arcTo( x, y, x + w, y, r );
	ctx.closePath();
}

const fmt = ( n: number, mean: boolean ) => num( mean ? n.toFixed( 2 ) : String( Math.round( n ) ) );

export async function drawShareCard( title: string, scales: ScaleResult[], site: string ): Promise<Blob | null> {
	try {
		await Promise.all( [
			document.fonts.load( '800 72px "Alexandria"' ),
			document.fonts.load( '700 40px "IBM Plex Sans Arabic"' ),
			document.fonts.load( '500 34px "IBM Plex Sans Arabic"' ),
			document.fonts.load( '600 44px "IBM Plex Mono"' ),
		] );
	} catch {
		// الخطّ الاحتياطي يكفي إن تعذّر التحميل.
	}

	const canvas = document.createElement( 'canvas' );
	canvas.width = W;
	canvas.height = H;
	const ctx = canvas.getContext( '2d' );

	if ( ! ctx ) {
		return null;
	}

	// V2: حبر داكن، وورق للنصّ، وبرتقالي لهامش الخطأ وحده.
	const INK = '#0b1312';
	const PAPER = '#e8f0ee';
	const MUTED = '#a2b4b0';
	const SIGNAL = '#ff9a4d';
	const DISPLAY = '"Alexandria", "IBM Plex Sans Arabic", sans-serif';
	const BODY = '"IBM Plex Sans Arabic", sans-serif';
	const MONO = '"IBM Plex Mono", monospace';

	ctx.fillStyle = INK;
	ctx.fillRect( 0, 0, W, H );

	ctx.direction = 'rtl';
	ctx.textAlign = 'center';

	// العين ومسطرتها، بالمقاس الكبير.
	const cx = W / 2;
	ctx.strokeStyle = PAPER;
	ctx.lineWidth = 8;
	ctx.lineJoin = 'round';
	ctx.lineCap = 'round';
	ctx.beginPath();
	ctx.moveTo( cx - 95, 220 );
	ctx.quadraticCurveTo( cx, 115, cx + 95, 220 );
	ctx.quadraticCurveTo( cx, 325, cx - 95, 220 );
	ctx.stroke();
	ctx.beginPath();
	ctx.arc( cx, 220, 30, 0, Math.PI * 2 );
	ctx.stroke();
	ctx.fillStyle = SIGNAL;
	ctx.beginPath();
	ctx.arc( cx, 220, 12, 0, Math.PI * 2 );
	ctx.fill();
	ctx.lineWidth = 6;
	ctx.beginPath();
	ctx.moveTo( cx - 85, 330 );
	ctx.lineTo( cx + 85, 330 );
	for ( const [ dx, h ] of [ [ -85, 14 ], [ -42, 8 ], [ 0, 18 ], [ 42, 8 ], [ 85, 14 ] ] ) {
		ctx.moveTo( cx + dx, 330 );
		ctx.lineTo( cx + dx, 330 - h );
	}
	ctx.stroke();

	ctx.fillStyle = PAPER;
	ctx.font = `800 64px ${ DISPLAY }`;
	ctx.fillText( 'بصيرة', cx, 440 );

	ctx.font = `700 52px ${ BODY }`;
	const tl = wrap( ctx, title, W - 180 );
	tl.slice( 0, 3 ).forEach( ( l, i ) => ctx.fillText( l, cx, 560 + i * 70 ) );

	let y = 560 + Math.min( tl.length, 3 ) * 70 + 80;
	const shown = scales.slice( 0, 5 );
	const rowH = shown.length > 3 ? 200 : 250;
	const x0 = 110;
	const x1 = W - 110;

	for ( const s of shown ) {
		ctx.textAlign = 'right';
		ctx.fillStyle = PAPER;
		ctx.font = `700 40px ${ BODY }`;
		ctx.fillText( s.name, x1, y );

		ctx.textAlign = 'left';
		ctx.direction = 'ltr';
		ctx.fillStyle = PAPER;
		ctx.font = `600 48px ${ MONO }`;
		ctx.fillText( fmt( s.score, s.mean ), x0, y );
		ctx.direction = 'rtl';

		const by = y + 34;
		const bh = 40;
		roundRect( ctx, x0, by, x1 - x0, bh, 10 );
		ctx.fillStyle = 'rgba(232,240,238,.08)';
		ctx.fill();

		// التدريج: عُشر المدى بين كل علامتين.
		ctx.fillStyle = 'rgba(232,240,238,.28)';
		for ( let i = 0; i <= 10; i++ ) {
			const x = x1 - ( i / 10 ) * ( x1 - x0 );
			const h = i % 5 === 0 ? 16 : 9;
			ctx.fillRect( x - 1, by + bh - h, 2, h );
		}

		// اليمين هو الأدنى: الشريط يُقرأ بالعربية.
		const at = ( v: number ) => x1 - ( ( v - s.min ) / ( s.max - s.min ) ) * ( x1 - x0 );

		if ( ! s.uncalibrated && s.high > s.low ) {
			const a = at( s.high );
			const b = at( s.low );
			roundRect( ctx, a, by + 5, Math.max( 16, b - a ), bh - 10, 7 );
			ctx.fillStyle = 'rgba(255,154,77,.32)';
			ctx.fill();
			ctx.lineWidth = 3;
			ctx.strokeStyle = SIGNAL;
			ctx.stroke();
		}

		roundRect( ctx, at( s.score ) - 3.5, by - 12, 7, bh + 24, 3.5 );
		ctx.fillStyle = PAPER;
		ctx.fill();

		ctx.textAlign = 'right';
		ctx.fillStyle = MUTED;
		ctx.font = `500 32px ${ BODY }`;
		ctx.fillText( s.band.label, x1, by + bh + 58 );

		y += rowH;
	}

	ctx.textAlign = 'center';
	ctx.fillStyle = MUTED;
	ctx.font = `500 34px ${ BODY }`;
	ctx.fillText( 'نتيجة تقديرية لها هامش خطأ — لا حكم نهائي', cx, H - 230 );
	// لا يُذكر الشريط البرتقالي إلا إن رُسم فعلاً.
	const banded = shown.some( ( s ) => ! s.uncalibrated && s.high > s.low );
	ctx.fillStyle = banded ? SIGNAL : MUTED;
	ctx.font = `700 36px ${ BODY }`;
	ctx.fillText( banded ? 'الشريط البرتقالي: النطاق المرجّح لدرجتك' : 'بنود لم يُقَس ثباتها بعد: لا هامش مُخترَع', cx, H - 175 );

	ctx.fillStyle = PAPER;
	ctx.font = `500 38px ${ MONO }`;
	ctx.direction = 'ltr';
	ctx.fillText( site.replace( /^https?:\/\//, '' ), cx, H - 90 );

	return new Promise( ( resolve ) => canvas.toBlob( ( b ) => resolve( b ), 'image/png' ) );
}
