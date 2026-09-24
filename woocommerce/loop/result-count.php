<?php
/**
 * عدد النتائج.
 *
 * قالب ووكومرس الأصلي يُخرج <p>، وموضعه في archive-product.php داخل <p>
 * أخرى. فقرة داخل فقرة يُغلقها المتصفح فوراً، فيقع النص خارج العنصر الذي
 * يحمل تنسيقه، وتقرأه وحدة الفلترة فارغاً. لذلك يُخرج هنا <span>.
 *
 * @package MatjarPro
 */

defined( 'ABSPATH' ) || exit;

if ( 1 === (int) $total ) {
	echo '<span class="woocommerce-result-count">' . esc_html__( 'منتج واحد', 'matjar-pro' ) . '</span>';

	return;
}

if ( $per_page < 1 || $per_page >= $total ) {
	printf(
		'<span class="woocommerce-result-count">%s</span>',
		esc_html(
			sprintf(
				/* translators: %s: عدد المنتجات. */
				_n( 'منتج واحد', '%s منتجات', $total, 'matjar-pro' ),
				number_format_i18n( $total )
			)
		)
	);

	return;
}

$mp_first = ( $per_page * ( $current - 1 ) ) + 1;
$mp_last  = min( $total, $per_page * $current );

printf(
	'<span class="woocommerce-result-count">%s</span>',
	esc_html(
		sprintf(
			/* translators: 1: أول منتج معروض. 2: آخر منتج معروض. 3: إجمالي المنتجات. */
			__( '%1$s–%2$s من %3$s منتجاً', 'matjar-pro' ),
			number_format_i18n( $mp_first ),
			number_format_i18n( $mp_last ),
			number_format_i18n( $total )
		)
	)
);
