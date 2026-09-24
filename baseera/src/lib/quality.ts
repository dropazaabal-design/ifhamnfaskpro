/**
 * جودة الاستجابة — جزء من «ميزان الثقة».
 *
 * نتيجة الاختبار لا تكون أدقّ من الإجابات التي بُنيت عليها. والمواقع
 * الشائعة تعطي نتيجة بثقة كاملة لمن نقر «موافق» على كل شيء في عشرين
 * ثانية. هنا ثلاث علامات معروفة في أدبيات «الاستجابة المتهاونة»، تُكشف
 * على جهاز المستخدم، وتُقال له بلطف ووضوح:
 *
 * ١ — السرعة: أقلّ من ثانيتين للبند في المتوسّط (Huang et al., 2012).
 * ٢ — السلسلة الطويلة: الإجابة نفسها لبنود متتالية كثيرة (Johnson, 2005).
 * ٣ — الموافقة على النقيضين: موافقة عالية على البنود وعلى عكسها معاً،
 *     وهو «انحياز القبول» (acquiescence).
 *
 * العلامات لا تُلغي النتيجة ولا تتّهم أحداً: تقول «خذها بحذر أكبر».
 */
import type { Test } from './types.ts';
import type { Answer } from './scoring.ts';
import { countNoun, STATEMENTS } from './format.ts';

export interface QualityFlag {
	id: 'speed' | 'longstring' | 'acquiescence';
	text: string;
}

export interface Quality {
	level: 'good' | 'caution';
	flags: QualityFlag[];
}

/** ثانيتان للبند: العتبة الأشهر في الأدبيات لكشف الإجابة المتهاونة. */
const MIN_MS_PER_ITEM = 2000;

export function assessQuality( test: Test, answers: Record<string, Answer> ): Quality {
	const flags: QualityFlag[] = [];
	const answered = test.items.filter( ( i ) => answers[ i.id ] !== undefined );

	if ( ! answered.length ) {
		return { level: 'good', flags };
	}

	// ١ — السرعة (لا تُطبَّق على أسئلة التفكير: الجواب السريع الصحيح ممكن).
	if ( test.kind === 'likert' ) {
		const total = answered.reduce( ( s, i ) => s + answers[ i.id ].ms, 0 );

		if ( total / answered.length < MIN_MS_PER_ITEM ) {
			flags.push( {
				id: 'speed',
				text: 'أجبت بسرعة كبيرة — أقلّ من ثانيتين للسؤال في المتوسّط. القراءة المتأنّية تجعل النتيجة أصدق.',
			} );
		}
	}

	// ٢ — السلسلة الطويلة: لا معنى لها إلا حين يحوي الاختبار بنوداً معكوسة.
	const hasReverse = test.items.some( ( i ) => i.key === -1 );

	if ( test.kind === 'likert' && hasReverse && answered.length >= 6 ) {
		let run = 1;
		let longest = 1;

		for ( let k = 1; k < answered.length; k++ ) {
			run = answers[ answered[ k ].id ].value === answers[ answered[ k - 1 ].id ].value ? run + 1 : 1;
			longest = Math.max( longest, run );
		}

		if ( longest >= Math.max( 6, Math.ceil( answered.length * 0.6 ) ) ) {
			flags.push( {
				id: 'longstring',
				text: `اخترت الإجابة نفسها لـ${ countNoun( longest, STATEMENTS ) } متتالية، مع أن بعضها مصوغ بالعكس عمداً. قد لا تعكس النتيجة رأيك الفعلي.`,
			} );
		}
	}

	// ٣ — الموافقة على النقيضين.
	if ( test.kind === 'likert' && hasReverse ) {
		const opts = test.options ?? [];
		const top = Math.max( ...opts.map( ( o ) => o.value ) );
		const bottom = Math.min( ...opts.map( ( o ) => o.value ) );
		const agreeLine = bottom + ( top - bottom ) * 0.75;

		const mean = ( key: 1 | -1 ) => {
			const set = answered.filter( ( i ) => ( i.key ?? 1 ) === key );

			return set.length ? set.reduce( ( s, i ) => s + answers[ i.id ].value, 0 ) / set.length : 0;
		};

		if ( opts.length && mean( 1 ) >= agreeLine && mean( -1 ) >= agreeLine ) {
			flags.push( {
				id: 'acquiescence',
				text: 'وافقت بقوّة على عبارات وعلى نقيضها معاً. هذا نمط معروف اسمه «انحياز القبول»، ويجعل الدرجة أقلّ دلالة.',
			} );
		}
	}

	return { level: flags.length ? 'caution' : 'good', flags };
}
