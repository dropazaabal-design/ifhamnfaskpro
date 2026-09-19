<?php
/**
 * مولّد صور المحتوى التجريبي.
 *
 * لا صورة فوتوغرافية واحدة في هذه الحزمة، وهذا قرار لا نقص: صور المنتجات
 * الحقيقية مملوكة لأصحابها، وشحنها مع قالب تجاري يورّث كل مشترٍ نزاعَ
 * حقوق لم يطلبه. فالبديل رسوم مسطّحة نملكها بالكامل، بلغة القالب اللونية
 * نفسها — باردة رصينة — فتبدو قراراً تصميمياً لا حشواً مؤقّتاً.
 *
 * والرسم يقع على ثلاثة أضعاف المقاس ثم يُصغَّر: GD لا ينعّم الحواف في
 * المضلّعات، والتصغير ثنائي المكعّب يعطي التنعيم مجاناً.
 *
 * التشغيل: php tools/make-demo-images.php
 *
 * @package MatjarPro
 */

$out = dirname( __DIR__ ) . '/assets/demo';

if ( ! is_dir( $out ) ) {
	mkdir( $out, 0755, true );
}

const W = 900;
const H = 1125;
const S = 3;

/**
 * يحوّل ستّ عشري إلى مصفوفة قنوات.
 */
function mp_rgb( $hex ) {
	$hex = ltrim( $hex, '#' );

	return array(
		hexdec( substr( $hex, 0, 2 ) ),
		hexdec( substr( $hex, 2, 2 ) ),
		hexdec( substr( $hex, 4, 2 ) ),
	);
}

/**
 * يمزج لونين بنسبة.
 */
function mp_mix( $a, $b, $t ) {
	$a = mp_rgb( $a );
	$b = mp_rgb( $b );

	return array(
		(int) round( $a[0] + ( $b[0] - $a[0] ) * $t ),
		(int) round( $a[1] + ( $b[1] - $a[1] ) * $t ),
		(int) round( $a[2] + ( $b[2] - $a[2] ) * $t ),
	);
}

function mp_alloc( $im, $rgb ) {
	return imagecolorallocate( $im, $rgb[0], $rgb[1], $rgb[2] );
}

/**
 * مستطيل بزوايا دائرية.
 */
function mp_round_rect( $im, $x1, $y1, $x2, $y2, $r, $color ) {
	$r = min( $r, ( $x2 - $x1 ) / 2, ( $y2 - $y1 ) / 2 );
	imagefilledrectangle( $im, $x1 + $r, $y1, $x2 - $r, $y2, $color );
	imagefilledrectangle( $im, $x1, $y1 + $r, $x2, $y2 - $r, $color );
	imagefilledellipse( $im, $x1 + $r, $y1 + $r, $r * 2, $r * 2, $color );
	imagefilledellipse( $im, $x2 - $r, $y1 + $r, $r * 2, $r * 2, $color );
	imagefilledellipse( $im, $x1 + $r, $y2 - $r, $r * 2, $r * 2, $color );
	imagefilledellipse( $im, $x2 - $r, $y2 - $r, $r * 2, $r * 2, $color );
}

/**
 * حلقة: دائرة ممتلئة ثم دائرة بلون الأرضية في وسطها.
 */
function mp_ring( $im, $cx, $cy, $outer, $inner, $color, $hole ) {
	imagefilledellipse( $im, $cx, $cy, $outer, $outer, $color );
	imagefilledellipse( $im, $cx, $cy, $inner, $inner, $hole );
}

function mp_poly( $im, $points, $color ) {
	$flat = array();

	foreach ( $points as $p ) {
		$flat[] = (int) $p[0];
		$flat[] = (int) $p[1];
	}

	imagefilledpolygon( $im, $flat, $color );
}

/**
 * الأشكال: كل واحد يرسم صورة ظلّية على لوح مُكبَّر.
 *
 * الإحداثيات كلّها بمقياس S، ومركز اللوح W*S/2.
 */
function mp_shape( $im, $kind, $ink, $tint, $bg ) {
	$cx = (int) ( W * S / 2 );
	$cy = (int) ( H * S / 2 );

	switch ( $kind ) {
		case 'abaya':
			// كُمّان طويلان أولاً ليختفي وصلهما تحت الجسم.
			mp_poly( $im, array(
				array( $cx - 240, $cy - 600 ), array( $cx - 470, $cy - 470 ),
				array( $cx - 560, $cy + 430 ), array( $cx - 390, $cy + 430 ),
				array( $cx - 330, $cy - 330 ),
			), $ink );
			mp_poly( $im, array(
				array( $cx + 240, $cy - 600 ), array( $cx + 470, $cy - 470 ),
				array( $cx + 560, $cy + 430 ), array( $cx + 390, $cy + 430 ),
				array( $cx + 330, $cy - 330 ),
			), $ink );
			// جسم منسدل: كتفان ضيّقان وذيل واسع.
			mp_poly( $im, array(
				array( $cx - 250, $cy - 620 ), array( $cx + 250, $cy - 620 ),
				array( $cx + 620, $cy + 660 ), array( $cx - 620, $cy + 660 ),
			), $ink );
			// فتحة العنق.
			imagefilledellipse( $im, $cx, $cy - 618, 230, 160, $bg );
			// شقّ أمامي.
			imagefilledrectangle( $im, $cx - 16, $cy - 500, $cx + 16, $cy + 660, $tint );
			break;

		case 'perfume':
			mp_round_rect( $im, $cx - 300, $cy - 200, $cx + 300, $cy + 560, 70, $ink );
			// كتفا القارورة.
			mp_poly( $im, array(
				array( $cx - 300, $cy - 160 ), array( $cx - 120, $cy - 380 ),
				array( $cx + 120, $cy - 380 ), array( $cx + 300, $cy - 160 ),
			), $ink );
			imagefilledrectangle( $im, $cx - 110, $cy - 470, $cx + 110, $cy - 360, $ink );
			mp_round_rect( $im, $cx - 165, $cy - 660, $cx + 165, $cy - 450, 34, $ink );
			// ملصق.
			mp_round_rect( $im, $cx - 190, $cy + 70, $cx + 190, $cy + 330, 24, $tint );
			break;

		case 'headphones':
			mp_ring( $im, $cx, $cy - 60, 900, 740, $ink, $bg );
			imagefilledrectangle( $im, $cx - 460, $cy - 60, $cx + 460, $cy + 420, $bg );
			mp_round_rect( $im, $cx - 500, $cy - 170, $cx - 250, $cy + 290, 100, $ink );
			mp_round_rect( $im, $cx + 250, $cy - 170, $cx + 500, $cy + 290, 100, $ink );
			mp_round_rect( $im, $cx - 440, $cy - 90, $cx - 310, $cy + 210, 60, $tint );
			mp_round_rect( $im, $cx + 310, $cy - 90, $cx + 440, $cy + 210, 60, $tint );
			break;

		case 'watch':
			mp_round_rect( $im, $cx - 150, $cy - 760, $cx + 150, $cy - 200, 60, $ink );
			mp_round_rect( $im, $cx - 150, $cy + 200, $cx + 150, $cy + 760, 60, $ink );
			mp_round_rect( $im, $cx - 290, $cy - 380, $cx + 290, $cy + 380, 150, $ink );
			mp_round_rect( $im, $cx - 220, $cy - 310, $cx + 220, $cy + 310, 110, $tint );
			imagefilledrectangle( $im, $cx + 290, $cy - 80, $cx + 340, $cy + 60, $ink );
			break;

		case 'bag':
			mp_ring( $im, $cx, $cy - 180, 620, 500, $ink, $bg );
			imagefilledrectangle( $im, $cx - 330, $cy - 170, $cx + 330, $cy + 200, $bg );
			mp_round_rect( $im, $cx - 460, $cy - 160, $cx + 460, $cy + 560, 80, $ink );
			imagefilledrectangle( $im, $cx - 460, $cy + 60, $cx + 460, $cy + 130, $tint );
			mp_round_rect( $im, $cx - 70, $cy + 30, $cx + 70, $cy + 160, 30, $tint );
			break;

		case 'glasses':
			mp_ring( $im, $cx - 300, $cy, 470, 350, $ink, $bg );
			mp_ring( $im, $cx + 300, $cy, 470, 350, $ink, $bg );
			imagefilledrectangle( $im, $cx - 80, $cy - 50, $cx + 80, $cy + 10, $ink );
			imagefilledrectangle( $im, $cx - 720, $cy - 40, $cx - 520, $cy + 10, $ink );
			imagefilledrectangle( $im, $cx + 520, $cy - 40, $cx + 720, $cy + 10, $ink );
			break;

		case 'phone':
			mp_round_rect( $im, $cx - 300, $cy - 620, $cx + 300, $cy + 620, 90, $ink );
			mp_round_rect( $im, $cx - 250, $cy - 540, $cx + 250, $cy + 540, 60, $tint );
			mp_round_rect( $im, $cx - 70, $cy - 580, $cx + 70, $cy - 530, 25, $ink );
			break;

		case 'speaker':
			mp_round_rect( $im, $cx - 330, $cy - 560, $cx + 330, $cy + 560, 160, $ink );
			mp_ring( $im, $cx, $cy - 180, 380, 240, $tint, $ink );
			mp_ring( $im, $cx, $cy + 260, 300, 190, $tint, $ink );
			break;

		case 'scarf':
			// شيلة مطويّة على نفسها: لوح عريض ينزل، وطيّة أفتح تعود صاعدة.
			mp_poly( $im, array(
				array( $cx - 560, $cy - 620 ), array( $cx + 140, $cy - 620 ),
				array( $cx + 360, $cy + 250 ), array( $cx + 190, $cy + 660 ),
				array( $cx - 210, $cy + 660 ), array( $cx - 360, $cy + 180 ),
			), $ink );
			mp_poly( $im, array(
				array( $cx + 140, $cy - 620 ), array( $cx + 560, $cy - 620 ),
				array( $cx + 470, $cy - 150 ), array( $cx + 250, $cy + 60 ),
				array( $cx + 300, $cy - 260 ),
			), $tint );
			// هُدب الطرف.
			for ( $i = -3; $i <= 3; $i++ ) {
				imagefilledrectangle( $im, $cx + $i * 58 - 16, $cy + 640, $cx + $i * 58 + 16, $cy + 760, $ink );
			}
			break;

		case 'powerbank':
			mp_round_rect( $im, $cx - 280, $cy - 500, $cx + 280, $cy + 500, 70, $ink );
			mp_round_rect( $im, $cx - 170, $cy - 320, $cx + 170, $cy - 200, 40, $tint );
			mp_round_rect( $im, $cx - 170, $cy - 120, $cx + 170, $cy, 40, $tint );
			mp_round_rect( $im, $cx - 170, $cy + 80, $cx + 170, $cy + 200, 40, $tint );
			mp_round_rect( $im, $cx - 90, $cy + 320, $cx + 90, $cy + 400, 26, $tint );
			break;

		case 'wallet':
			mp_round_rect( $im, $cx - 520, $cy - 340, $cx + 520, $cy + 340, 70, $ink );
			imagefilledrectangle( $im, $cx - 520, $cy - 40, $cx + 520, $cy + 40, $tint );
			mp_round_rect( $im, $cx + 200, $cy - 130, $cx + 460, $cy + 130, 40, $tint );
			break;

		case 'earbuds':
			mp_round_rect( $im, $cx - 440, $cy - 300, $cx + 440, $cy + 340, 180, $ink );
			imagefilledellipse( $im, $cx - 190, $cy + 20, 320, 320, $tint );
			imagefilledellipse( $im, $cx + 190, $cy + 20, 320, 320, $tint );
			imagefilledrectangle( $im, $cx - 60, $cy + 280, $cx + 60, $cy + 340, $tint );
			break;

		case 'kaftan':
			mp_poly( $im, array(
				array( $cx - 260, $cy - 640 ), array( $cx + 260, $cy - 640 ),
				array( $cx + 560, $cy + 660 ), array( $cx - 560, $cy + 660 ),
			), $ink );
			imagefilledellipse( $im, $cx, $cy - 630, 250, 170, $bg );
			mp_poly( $im, array(
				array( $cx - 250, $cy - 600 ), array( $cx - 620, $cy - 300 ),
				array( $cx - 560, $cy - 180 ), array( $cx - 230, $cy - 400 ),
			), $ink );
			mp_poly( $im, array(
				array( $cx + 250, $cy - 600 ), array( $cx + 620, $cy - 300 ),
				array( $cx + 560, $cy - 180 ), array( $cx + 230, $cy - 400 ),
			), $ink );
			// تطريز.
			for ( $i = 0; $i < 5; $i++ ) {
				imagefilledellipse( $im, $cx, $cy - 380 + $i * 190, 90, 90, $tint );
			}
			break;

		case 'oud':
			// عود وبخور: قطع خشبية مكدّسة.
			mp_round_rect( $im, $cx - 420, $cy + 120, $cx + 420, $cy + 320, 60, $ink );
			mp_round_rect( $im, $cx - 300, $cy - 120, $cx + 340, $cy + 90, 60, $ink );
			mp_round_rect( $im, $cx - 200, $cy - 340, $cx + 180, $cy - 150, 55, $ink );
			mp_round_rect( $im, $cx - 120, $cy - 520, $cx + 100, $cy - 370, 45, $tint );
			break;

		case 'candle':
			mp_round_rect( $im, $cx - 300, $cy - 200, $cx + 300, $cy + 520, 60, $ink );
			imagefilledellipse( $im, $cx, $cy - 200, 600, 180, $tint );
			mp_poly( $im, array(
				array( $cx, $cy - 560 ), array( $cx + 90, $cy - 380 ),
				array( $cx, $cy - 260 ), array( $cx - 90, $cy - 380 ),
			), $ink );
			break;

		case 'lamp':
			mp_poly( $im, array(
				array( $cx - 380, $cy - 40 ), array( $cx - 230, $cy - 480 ),
				array( $cx + 230, $cy - 480 ), array( $cx + 380, $cy - 40 ),
			), $ink );
			imagefilledrectangle( $im, $cx - 40, $cy - 40, $cx + 40, $cy + 430, $ink );
			mp_round_rect( $im, $cx - 320, $cy + 430, $cx + 320, $cy + 540, 50, $ink );
			imagefilledellipse( $im, $cx, $cy - 250, 220, 220, $tint );
			break;

		default:
			imagefilledellipse( $im, $cx, $cy, 900, 900, $ink );
	}
}

/**
 * يبني صورة واحدة ويكتبها WebP.
 */
function mp_make( $file, $kind, $ground, $accent ) {
	$im = imagecreatetruecolor( W * S, H * S );

	// أرضية متدرّجة رأسياً: فاتحة أعلى، أعمق قليلاً أسفل.
	for ( $y = 0; $y < H * S; $y++ ) {
		$t = $y / ( H * S );
		$c = mp_alloc( $im, mp_mix( $ground, $accent, 0.10 + $t * 0.14 ) );
		imageline( $im, 0, $y, W * S - 1, $y, $c );
	}

	$bg_at_center = mp_alloc( $im, mp_mix( $ground, $accent, 0.17 ) );

	// هالة خلف المنتج تفصله عن الأرضية بلا إطار.
	$halo = mp_alloc( $im, mp_mix( $ground, '#FFFFFF', 0.55 ) );
	imagefilledellipse( $im, (int) ( W * S / 2 ), (int) ( H * S / 2 ), 2280, 2280, $halo );

	$ink  = mp_alloc( $im, mp_rgb( $accent ) );
	$tint = mp_alloc( $im, mp_mix( $accent, '#FFFFFF', 0.72 ) );

	mp_shape( $im, $kind, $ink, $tint, $halo );

	/*
	 * تقريب المشهد قبل التصغير.
	 *
	 * بلا هذا يجلس المنتج صغيراً وسط فراغ فيُقرأ أيقونةً لا سلعة. والقصّ
	 * من المركز ثم التصغير ثنائي المكعّب يكبّر الموضوع والهالة معاً ويُبقي
	 * الحواف ناعمة، فلا حاجة لإعادة كتابة إحداثيات كل شكل.
	 */
	$zoom = 1.32;
	$sw   = (int) round( W * S / $zoom );
	$sh   = (int) round( H * S / $zoom );

	$small = imagecreatetruecolor( W, H );
	imagecopyresampled(
		$small, $im,
		0, 0,
		(int) round( ( W * S - $sw ) / 2 ), (int) round( ( H * S - $sh ) / 2 ),
		W, H, $sw, $sh
	);

	imagewebp( $small, $file, 82 );
	imagedestroy( $im );
	imagedestroy( $small );

	return filesize( $file );
}

/*
 * كل منتج له صورتان: واجهة بلون، وزاوية ثانية بلون أعمق — حتى يختبر
 * المشتري المعرِض بصورتين لا بصورة واحدة مكرّرة.
 */
$items = array(
	'abaya-classic'   => array( 'abaya',      '#EEF0F4', '#1B2A4A' ),
	'abaya-linen'     => array( 'abaya',      '#F1F0EC', '#3A4454' ),
	'kaftan-royal'    => array( 'kaftan',     '#EDF1F0', '#14514A' ),
	'kaftan-pearl'    => array( 'kaftan',     '#F2F1F4', '#3E3A55' ),
	'scarf-silk'      => array( 'scarf',      '#F0EEF2', '#4A3D63' ),
	'scarf-chiffon'   => array( 'scarf',      '#EFF2F3', '#1F4E5F' ),
	'perfume-oud'     => array( 'perfume',    '#F1EFEB', '#2B2A33' ),
	'perfume-musk'    => array( 'perfume',    '#EFF1F4', '#243B6B' ),
	'oud-wood'        => array( 'oud',        '#F2F0EC', '#32302E' ),
	'candle-amber'    => array( 'candle',     '#F1F1EE', '#2E3B36' ),
	'headphones-pro'  => array( 'headphones', '#EDEFF2', '#17171A' ),
	'earbuds-air'     => array( 'earbuds',    '#F0F1F3', '#26303F' ),
	'watch-smart'     => array( 'watch',      '#EEEFF1', '#111114' ),
	'phone-stand'     => array( 'phone',      '#EFF0F2', '#1D2733' ),
	'speaker-mini'    => array( 'speaker',    '#EEF0F3', '#202024' ),
	'powerbank-slim'  => array( 'powerbank',  '#F0F1F3', '#1A2130' ),
	'bag-tote'        => array( 'bag',        '#F1EFEE', '#4A3B38' ),
	'wallet-card'     => array( 'wallet',     '#F0F0EE', '#2F3A34' ),
	'glasses-sun'     => array( 'glasses',    '#EFF1F2', '#1C2430' ),
	'lamp-desk'       => array( 'lamp',       '#F0F1F0', '#22333B' ),
);

$total = 0;
$count = 0;

foreach ( $items as $slug => $spec ) {
	list( $kind, $ground, $accent ) = $spec;

	$total += mp_make( "$out/$slug.webp", $kind, $ground, $accent );
	$count++;

	// زاوية ثانية: الأرضية تأخذ مسحة من لون المنتج فتتغيّر الصورة فعلاً.
	$total += mp_make( "$out/$slug-2.webp", $kind, mp_rgb_hex( mp_mix( $ground, $accent, 0.14 ) ), $accent );
	$count++;
}

function mp_rgb_hex( $rgb ) {
	return sprintf( '#%02X%02X%02X', $rgb[0], $rgb[1], $rgb[2] );
}

printf( "%d صورة · %s إجمالاً · متوسّط %s\n", $count, size_format_stub( $total ), size_format_stub( (int) ( $total / $count ) ) );

function size_format_stub( $b ) {
	return $b > 1024 * 1024 ? round( $b / 1048576, 2 ) . ' م.ب' : round( $b / 1024, 1 ) . ' ك.ب';
}
