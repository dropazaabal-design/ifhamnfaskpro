/**
 * تقسيم النصّ للعرض المختصر.
 *
 * جمهور بصيرة الأوسع يقرأ السطر الأوّل ويقرّر: هل يكمل؟ لذلك يُعرض أوّل
 * جملة، والباقي خلف «اقرأ أكثر». لا شيء يُحذف: النصّ كلّه في الصفحة،
 * ومحرّك البحث يقرأ المحتوى المطويّ كاملاً.
 */
export function splitLead( text: string ): [ string, string ] {
	const m = text.match( /^(.+?[.!؟])\s+([\s\S]+)$/ );

	return m ? [ m[ 1 ], m[ 2 ] ] : [ text, '' ];
}

/**
 * عنوان قصير لبند طويل: ما قبل النقطتين إن وُجدتا مبكّراً.
 *
 * بنود «ما لا يستطيع قوله» مكتوبة غالباً «ادّعاء: شرح». الادّعاء وحده
 * يكفي للمسح بالعين، والشرح لمن يريده.
 */
export function splitHeadline( text: string ): [ string, string ] {
	const i = text.indexOf( ':' );

	if ( i > 8 && i < 90 ) {
		return [ text.slice( 0, i ), text.slice( i + 1 ).trim() ];
	}

	return splitLead( text );
}

/** زمن القراءة بالدقائق: نحو 180 كلمة عربية في الدقيقة على الهاتف. */
export function readingMinutes( ...parts: string[] ): number {
	const words = parts.join( ' ' ).split( /\s+/ ).filter( Boolean ).length;

	return Math.max( 1, Math.round( words / 180 ) );
}
