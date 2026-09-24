<?php
/**
 * يولّد languages/matjar-pro.pot — `npm run pot`.
 *
 * لا xgettext ولا WP-CLI في بيئة البناء، فالاستخراج يتمّ بـ token_get_all:
 * تحليل نحوي حقيقي لا تعبيرات نمطية. الفارق ليس شكلياً — التعبير النمطي
 * يفشل على السلاسل التي تحتوي أقواساً أو علامات تنصيص مهرَّبة أو تُوصَل
 * بعامل النقطة، وكلها موجود في القالب.
 *
 * يُبلّغ أيضاً عن كل سلسلة بمعرّف ترجمة خاطئ أو ناقص، وهو أشهر سبب لبقاء
 * جزء من القالب غير مترجَم بلا أن يلاحظ أحد.
 *
 * @package MatjarPro
 */

declare( strict_types = 1 );

const MP_DOMAIN = 'matjar-pro';

/** دوال الترجمة ومواضع وسائطها. */
const MP_FUNCTIONS = array(
	'__'          => array( 'text' => 0, 'domain' => 1 ),
	'_e'          => array( 'text' => 0, 'domain' => 1 ),
	'esc_html__'  => array( 'text' => 0, 'domain' => 1 ),
	'esc_attr__'  => array( 'text' => 0, 'domain' => 1 ),
	'esc_html_e'  => array( 'text' => 0, 'domain' => 1 ),
	'esc_attr_e'  => array( 'text' => 0, 'domain' => 1 ),
	'_x'          => array( 'text' => 0, 'context' => 1, 'domain' => 2 ),
	'esc_html_x'  => array( 'text' => 0, 'context' => 1, 'domain' => 2 ),
	'esc_attr_x'  => array( 'text' => 0, 'context' => 1, 'domain' => 2 ),
	'_n'          => array( 'text' => 0, 'plural' => 1, 'domain' => 3 ),
	'_nx'         => array( 'text' => 0, 'plural' => 1, 'context' => 3, 'domain' => 4 ),
	'_n_noop'     => array( 'text' => 0, 'plural' => 1, 'domain' => 2 ),
	'_nx_noop'    => array( 'text' => 0, 'plural' => 1, 'context' => 2, 'domain' => 3 ),
);

/**
 * يفكّ تهريب سلسلة PHP حرفية.
 *
 * يعيد null إذا لم تكن حرفية بحتة (مثل سلسلة مزدوجة فيها متغيّر): سلسلة
 * تُبنى في وقت التشغيل لا يمكن استخراجها للترجمة، وإدراجها يعني إدراج
 * نصّ لن يُطابق شيئاً.
 *
 * @param string $token السلسلة كما وردت في الكود، بعلامتي تنصيصها.
 * @return string|null
 */
function mp_unquote( string $token ): ?string {
	$quote = $token[0] ?? '';
	$body  = substr( $token, 1, -1 );

	if ( "'" === $quote ) {
		return str_replace( array( '\\\\', "\\'" ), array( '\\', "'" ), $body );
	}

	if ( '"' !== $quote ) {
		return null;
	}

	// متغيّر داخل سلسلة مزدوجة: ليست حرفية.
	if ( preg_match( '/(?<!\\\\)\$/', $body ) || false !== strpos( $body, '{$' ) ) {
		return null;
	}

	return str_replace(
		array( '\\n', '\\t', '\\r', '\\"', '\\$', '\\\\' ),
		array( "\n", "\t", "\r", '"', '$', '\\' ),
		$body
	);
}

/**
 * يهرّب سلسلة لصيغة PO.
 *
 * @param string $value القيمة.
 * @return string
 */
function mp_po_escape( string $value ): string {
	return str_replace(
		array( '\\', '"', "\n", "\t", "\r" ),
		array( '\\\\', '\\"', '\\n', '\\t', '\\r' ),
		$value
	);
}

/**
 * يستخرج السلاسل من ملف واحد.
 *
 * @param string $file    مسار الملف.
 * @param string $relative المسار النسبي للمرجع.
 * @param array  $entries المدخلات، تُمرَّر بالمرجع.
 * @param array  $issues  الملاحظات، تُمرَّر بالمرجع.
 */
function mp_scan( string $file, string $relative, array &$entries, array &$issues ): void {
	$tokens = token_get_all( (string) file_get_contents( $file ) );
	$count  = count( $tokens );

	for ( $i = 0; $i < $count; $i++ ) {
		$token = $tokens[ $i ];

		if ( ! is_array( $token ) || T_STRING !== $token[0] || ! isset( MP_FUNCTIONS[ $token[1] ] ) ) {
			continue;
		}

		// استدعاء دالة لا اسم دالة معرَّفة ولا خاصية كائن.
		$previous = null;

		for ( $p = $i - 1; $p >= 0; $p-- ) {
			if ( is_array( $tokens[ $p ] ) && in_array( $tokens[ $p ][0], array( T_WHITESPACE, T_COMMENT, T_DOC_COMMENT ), true ) ) {
				continue;
			}

			$previous = $tokens[ $p ];
			break;
		}

		if ( is_array( $previous ) && in_array( $previous[0], array( T_FUNCTION, T_OBJECT_OPERATOR, T_DOUBLE_COLON ), true ) ) {
			continue;
		}

		// القوس المفتوح التالي.
		$j = $i + 1;

		while ( $j < $count && is_array( $tokens[ $j ] ) && T_WHITESPACE === $tokens[ $j ][0] ) {
			$j++;
		}

		if ( ! isset( $tokens[ $j ] ) || '(' !== $tokens[ $j ] ) {
			continue;
		}

		$spec      = MP_FUNCTIONS[ $token[1] ];
		$line      = (int) $token[2];
		$depth     = 0;
		$args      = array();
		$current   = array();
		$literal   = true;
		$has_parts = false;

		for ( $k = $j; $k < $count; $k++ ) {
			$t = $tokens[ $k ];

			if ( '(' === $t ) {
				$depth++;

				if ( 1 === $depth ) {
					continue;
				}
			}

			if ( ')' === $t ) {
				$depth--;

				if ( 0 === $depth ) {
					$args[] = array( 'literal' => $literal && $has_parts, 'value' => implode( '', $current ) );
					break;
				}
			}

			if ( 1 === $depth && ',' === $t ) {
				$args[]    = array( 'literal' => $literal && $has_parts, 'value' => implode( '', $current ) );
				$current   = array();
				$literal   = true;
				$has_parts = false;
				continue;
			}

			if ( is_array( $t ) && in_array( $t[0], array( T_WHITESPACE, T_COMMENT, T_DOC_COMMENT ), true ) ) {
				continue;
			}

			if ( is_array( $t ) && T_CONSTANT_ENCAPSED_STRING === $t[0] ) {
				$piece = mp_unquote( $t[1] );

				if ( null === $piece ) {
					$literal = false;
				} else {
					$current[] = $piece;
					$has_parts = true;
				}

				continue;
			}

			// وصل السلاسل بعامل النقطة مسموح؛ أي شيء آخر يُبطل الحرفية.
			if ( '.' !== $t ) {
				$literal = false;
			}
		}

		$get = static function ( $index ) use ( $args ) {
			return isset( $args[ $index ] ) ? $args[ $index ] : null;
		};

		$text = $get( $spec['text'] );

		if ( ! $text || ! $text['literal'] ) {
			$issues[] = sprintf( '%s:%d — سلسلة غير حرفية في %s()', $relative, $line, $token[1] );
			continue;
		}

		$domain = isset( $spec['domain'] ) ? $get( $spec['domain'] ) : null;

		if ( ! $domain || ! $domain['literal'] ) {
			$issues[] = sprintf( '%s:%d — %s() بلا معرّف ترجمة', $relative, $line, $token[1] );
			continue;
		}

		if ( MP_DOMAIN !== $domain['value'] ) {
			$issues[] = sprintf( '%s:%d — معرّف ترجمة غريب «%s»', $relative, $line, $domain['value'] );
			continue;
		}

		$context = isset( $spec['context'] ) ? $get( $spec['context'] ) : null;
		$plural  = isset( $spec['plural'] ) ? $get( $spec['plural'] ) : null;

		$key = ( $context && $context['literal'] ? $context['value'] . "\x04" : '' ) . $text['value'];

		if ( ! isset( $entries[ $key ] ) ) {
			$entries[ $key ] = array(
				'msgid'      => $text['value'],
				'context'    => $context && $context['literal'] ? $context['value'] : null,
				'plural'     => $plural && $plural['literal'] ? $plural['value'] : null,
				'references' => array(),
				'comments'   => array(),
			);
		}

		$entries[ $key ]['references'][] = $relative . ':' . $line;

		// تعليق /* translators: */ السابق للاستدعاء.
		for ( $p = $i - 1; $p >= 0 && $p > $i - 12; $p-- ) {
			if ( ! is_array( $tokens[ $p ] ) ) {
				continue;
			}

			if ( T_WHITESPACE === $tokens[ $p ][0] ) {
				continue;
			}

			if ( in_array( $tokens[ $p ][0], array( T_COMMENT, T_DOC_COMMENT ), true ) ) {
				if ( preg_match( '/translators:\s*(.+?)\s*(?:\*\/|$)/su', $tokens[ $p ][1], $match ) ) {
					$note = trim( preg_replace( '/\s+/u', ' ', $match[1] ) );

					if ( '' !== $note && ! in_array( $note, $entries[ $key ]['comments'], true ) ) {
						$entries[ $key ]['comments'][] = $note;
					}
				}
			}

			break;
		}
	}
}

/* ---------- التشغيل ---------- */

$root    = dirname( __DIR__ );
$entries = array();
$issues  = array();
$files   = array();

$iterator = new RecursiveIteratorIterator(
	new RecursiveCallbackFilterIterator(
		new RecursiveDirectoryIterator( $root, FilesystemIterator::SKIP_DOTS ),
		static function ( $current ) {
			$name = $current->getFilename();

			if ( $current->isDir() ) {
				return ! in_array( $name, array( 'node_modules', '.git', '.preview', 'languages', 'tools' ), true );
			}

			return 'php' === strtolower( $current->getExtension() );
		}
	)
);

foreach ( $iterator as $file ) {
	$files[] = $file->getPathname();
}

sort( $files );

foreach ( $files as $file ) {
	mp_scan( $file, ltrim( str_replace( $root, '', $file ), '/\\' ), $entries, $issues );
}

ksort( $entries );

$out   = array();
$out[] = '# Copyright (C) ' . gmdate( 'Y' ) . ' Matjar Pro';
$out[] = '# This file is distributed under the GNU General Public License v2 or later.';
$out[] = 'msgid ""';
$out[] = 'msgstr ""';
$out[] = '"Project-Id-Version: Matjar Pro 0.1.0\n"';
$out[] = '"Report-Msgid-Bugs-To: \n"';
$out[] = '"POT-Creation-Date: ' . gmdate( 'Y-m-d H:i' ) . '+0000\n"';
$out[] = '"MIME-Version: 1.0\n"';
$out[] = '"Content-Type: text/plain; charset=UTF-8\n"';
$out[] = '"Content-Transfer-Encoding: 8bit\n"';
$out[] = '"PO-Revision-Date: YEAR-MO-DA HO:MI+ZONE\n"';
$out[] = '"Last-Translator: FULL NAME <EMAIL@ADDRESS>\n"';
$out[] = '"Language-Team: LANGUAGE <LL@li.org>\n"';
$out[] = '"Plural-Forms: nplurals=INTEGER; plural=EXPRESSION;\n"';
$out[] = '"X-Generator: matjar-pro tools/make-pot.php\n"';
$out[] = '"X-Domain: ' . MP_DOMAIN . '\n"';
$out[] = '';

foreach ( $entries as $entry ) {
	foreach ( $entry['comments'] as $comment ) {
		$out[] = '#. translators: ' . $comment;
	}

	$out[] = '#: ' . implode( ' ', array_unique( $entry['references'] ) );

	if ( null !== $entry['context'] ) {
		$out[] = 'msgctxt "' . mp_po_escape( $entry['context'] ) . '"';
	}

	$out[] = 'msgid "' . mp_po_escape( $entry['msgid'] ) . '"';

	if ( null !== $entry['plural'] ) {
		$out[] = 'msgid_plural "' . mp_po_escape( $entry['plural'] ) . '"';
		$out[] = 'msgstr[0] ""';
		$out[] = 'msgstr[1] ""';
	} else {
		$out[] = 'msgstr ""';
	}

	$out[] = '';
}

$target = $root . '/languages/' . MP_DOMAIN . '.pot';

if ( ! is_dir( dirname( $target ) ) ) {
	mkdir( dirname( $target ), 0755, true );
}

file_put_contents( $target, implode( "\n", $out ) );

printf( "استُخرجت %d سلسلة إلى languages/%s.pot\n", count( $entries ), MP_DOMAIN );

if ( ! empty( $issues ) ) {
	printf( "\n%d ملاحظة:\n", count( $issues ) );

	foreach ( $issues as $issue ) {
		echo '  - ' . $issue . "\n";
	}

	exit( 1 );
}

echo "لا ملاحظات: كل سلسلة حرفية ومعرّف الترجمة صحيح في كل استدعاء.\n";
exit( 0 );
