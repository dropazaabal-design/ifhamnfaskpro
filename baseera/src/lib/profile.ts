/**
 * ملفّي — سجلّ النتائج على جهاز صاحبها وحده.
 *
 * لا خادم ولا حساب: localStorage في متصفّح الزائر. هذا ليس توفيراً، بل
 * موقف: نتائج اختبار قلق أو تقدير ذات لا مكان لها في قاعدة بيانات أحد.
 *
 * والإضافة العلمية هنا مؤشّر التغيّر الموثوق (Jacobson & Truax, 1991):
 * حين تعيد اختباراً، الفرق بين النتيجتين يُقارَن بهامش خطأ المقياس، فيقال
 * لك إن كان التغيّر حقيقياً أم ضجيجاً. المواقع تقول «تحسّنت 3 نقاط!» ولو
 * كان هامش الخطأ 5.
 */
import type { ScaleResult } from './scoring.ts';

const KEY = 'baseera-profile';
const MAX = 120;

export interface SavedResult {
	slug: string;
	title: string;
	at: string;
	quality: 'good' | 'caution';
	scales: Pick<ScaleResult, 'id' | 'name' | 'score' | 'min' | 'max' | 'low' | 'high' | 'sem' | 'higherIs' | 'uncalibrated'> & { band: string }[];
}

export function readProfile(): SavedResult[] {
	try {
		const raw = localStorage.getItem( KEY );
		const list = raw ? JSON.parse( raw ) : [];

		return Array.isArray( list ) ? list : [];
	} catch {
		return [];
	}
}

export function saveResult( entry: SavedResult ): boolean {
	try {
		const list = readProfile();
		list.push( entry );
		localStorage.setItem( KEY, JSON.stringify( list.slice( -MAX ) ) );

		return true;
	} catch {
		return false;
	}
}

export function clearProfile(): void {
	try {
		localStorage.removeItem( KEY );
	} catch {
		// لا شيء محفوظ أصلاً.
	}
}

export function previousOf( slug: string, before?: string ): SavedResult | undefined {
	const list = readProfile().filter( ( r ) => r.slug === slug && ( ! before || r.at < before ) );

	return list[ list.length - 1 ];
}

export interface Change {
	delta: number;
	/** مؤشّر التغيّر الموثوق. |RCI| ≥ 1.96 تغيّر يتجاوز خطأ القياس بثقة 95%. */
	rci: number | null;
	reliable: boolean;
	direction: 'better' | 'worse' | 'neutral';
}

/**
 * RCI = (س2 − س1) ÷ (√2 × SEM).
 *
 * √2 لأن للقياسين كليهما خطأً مستقلّاً. وحين لا يكون للمقياس ثبات معروف
 * (بنود غير معايَرة)، لا يُحسب المؤشّر ولا يُدّعى شيء.
 */
export function reliableChange( before: number, after: number, sem: number, higherIs: string ): Change {
	const delta = after - before;

	if ( ! sem ) {
		return { delta, rci: null, reliable: false, direction: 'neutral' };
	}

	const rci = delta / ( Math.SQRT2 * sem );
	const reliable = Math.abs( rci ) >= 1.96;
	let direction: Change['direction'] = 'neutral';

	if ( reliable && higherIs !== 'neutral' ) {
		direction = ( delta > 0 ) === ( higherIs === 'better' ) ? 'better' : 'worse';
	}

	return { delta, rci, reliable, direction };
}
