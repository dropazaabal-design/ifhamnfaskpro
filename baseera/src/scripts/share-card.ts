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
			document.fonts.load( '700 72px "Reem Kufi"' ),
			document.fonts.load( '800 40px "Tajawal"' ),
			document.fonts.load( '500 34px "Tajawal"' ),
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

	const g = ctx.createLinearGradient( 0, 0, 0, H );
	g.addColorStop( 0, '#0b1224' );
	g.addColorStop( 1, '#16254a' );
	ctx.fillStyle = g;
	ctx.fillRect( 0, 0, W, H );

	const glow = ctx.createRadialGradient( W / 2, 260, 0, W / 2, 260, 520 );
	glow.addColorStop( 0, 'rgba(96,165,250,.28)' );
	glow.addColorStop( 1, 'rgba(96,165,250,0)' );
	ctx.fillStyle = glow;
	ctx.fillRect( 0, 0, W, 800 );

	ctx.direction = 'rtl';
	ctx.textAlign = 'center';

	// العين.
	ctx.strokeStyle = '#60a5fa';
	ctx.lineWidth = 7;
	ctx.beginPath();
	ctx.moveTo( W / 2 - 80, 240 );
	ctx.quadraticCurveTo( W / 2, 150, W / 2 + 80, 240 );
	ctx.quadraticCurveTo( W / 2, 330, W / 2 - 80, 240 );
	ctx.stroke();
	ctx.beginPath();
	ctx.arc( W / 2, 240, 26, 0, Math.PI * 2 );
	ctx.stroke();
	ctx.fillStyle = '#60a5fa';
	ctx.beginPath();
	ctx.arc( W / 2, 240, 9, 0, Math.PI * 2 );
	ctx.fill();

	ctx.fillStyle = '#eef2fb';
	ctx.font = '700 60px "Reem Kufi", "Tajawal", sans-serif';
	ctx.fillText( 'بصيرة', W / 2, 400 );

	ctx.font = '800 54px "Tajawal", sans-serif';
	const tl = wrap( ctx, title, W - 180 );
	tl.slice( 0, 3 ).forEach( ( l, i ) => ctx.fillText( l, W / 2, 520 + i * 72 ) );

	let y = 520 + Math.min( tl.length, 3 ) * 72 + 70;
	const shown = scales.slice( 0, 5 );
	const rowH = shown.length > 3 ? 190 : 240;
	const x0 = 110;
	const x1 = W - 110;

	for ( const s of shown ) {
		const mean = s.score % 1 !== 0 || s.max <= 5;

		ctx.textAlign = 'right';
		ctx.fillStyle = '#eef2fb';
		ctx.font = '800 40px "Tajawal", sans-serif';
		ctx.fillText( s.name, x1, y );

		ctx.textAlign = 'left';
		ctx.fillStyle = '#93c5fd';
		ctx.font = '800 44px "Tajawal", sans-serif';
		ctx.fillText( fmt( s.score, mean ), x0, y );

		const by = y + 38;
		roundRect( ctx, x0, by, x1 - x0, 20, 10 );
		ctx.fillStyle = 'rgba(255,255,255,.09)';
		ctx.fill();

		// اليمين هو الأدنى: الشريط يُقرأ بالعربية.
		const at = ( v: number ) => x1 - ( ( v - s.min ) / ( s.max - s.min ) ) * ( x1 - x0 );

		if ( ! s.uncalibrated && s.high > s.low ) {
			const a = at( s.high );
			const b = at( s.low );
			roundRect( ctx, a, by, Math.max( 20, b - a ), 20, 10 );
			ctx.fillStyle = 'rgba(96,165,250,.38)';
			ctx.fill();
		}

		ctx.beginPath();
		ctx.arc( at( s.score ), by + 10, 17, 0, Math.PI * 2 );
		ctx.fillStyle = '#60a5fa';
		ctx.fill();
		ctx.lineWidth = 5;
		ctx.strokeStyle = '#0b1224';
		ctx.stroke();

		ctx.textAlign = 'right';
		ctx.fillStyle = '#cbd5e1';
		ctx.font = '500 32px "Tajawal", sans-serif';
		ctx.fillText( s.band.label, x1, by + 78 );

		y += rowH;
	}

	ctx.textAlign = 'center';
	ctx.fillStyle = '#cbd5e1';
	ctx.font = '500 34px "Tajawal", sans-serif';
	ctx.fillText( 'نتيجة تقديرية لها هامش خطأ — لا حكم نهائي', W / 2, H - 230 );
	ctx.fillStyle = '#93c5fd';
	ctx.font = '800 38px "Tajawal", sans-serif';
	ctx.fillText( 'الشريط الفاتح: النطاق المرجّح لدرجتك', W / 2, H - 175 );

	ctx.fillStyle = '#eef2fb';
	ctx.font = '700 40px "Space Grotesk", sans-serif';
	ctx.direction = 'ltr';
	ctx.fillText( site.replace( /^https?:\/\//, '' ), W / 2, H - 90 );

	return new Promise( ( resolve ) => canvas.toBlob( ( b ) => resolve( b ), 'image/png' ) );
}
