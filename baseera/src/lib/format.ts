/**
 * الأرقام الغربية 1234567890 في كل ما يُعرض.
 *
 * جمهور بصيرة الأوسع (جيل Z وألفا في الخليج ومصر) يقرأ الأرقام الغربية على
 * هاتفه في كل تطبيق، والمشرقية تبطّئ القراءة. والدالّة تحوّل أيّ رقم مشرقي
 * يتسرّب من مصدر آخر، لا تكتفي بتمرير الرقم.
 */
export const num = ( s: string | number ) =>
	String( s )
		.replace( /[٠-٩]/g, ( d ) => String( '٠١٢٣٤٥٦٧٨٩'.indexOf( d ) ) )
		.replace( /(?<=\d)٫(?=\d)/g, '.' )
		.replace( /(?<=\d)٬(?=\d)/g, ',' );

/**
 * العدد والمعدود بقواعد العربية.
 *
 * «7 عبارة» خطأ يلاحظه كل قارئ عربي، ويقرأ الصفحة معه أقلّ عناية:
 * 1 مفرد، 2 مثنّى، 3–10 جمع، 11–99 مفرد منصوب، ومئة فما فوق مفرد.
 */
export interface NounForms {
	one: string;
	two: string;
	/** المثنّى مجروراً أو منصوباً: «نحو دقيقتين» لا «نحو دقيقتان». */
	twoGen: string;
	few: string;
	many: string;
	hundred: string;
}

export function countNoun( n: number, forms: NounForms, grammaticalCase: 'nom' | 'gen' = 'nom' ): string {
	const d = num( n );
	const mod = n % 100;

	if ( n === 1 ) {
		return forms.one;
	}

	if ( n === 2 ) {
		return grammaticalCase === 'gen' ? forms.twoGen : forms.two;
	}

	if ( mod >= 3 && mod <= 10 ) {
		return `${ d } ${ forms.few }`;
	}

	if ( mod >= 11 && mod <= 99 ) {
		return `${ d } ${ forms.many }`;
	}

	return `${ d } ${ forms.hundred }`;
}

export const QUESTIONS: NounForms = { one: 'سؤال واحد', two: 'سؤالان', twoGen: 'سؤالين', few: 'أسئلة', many: 'سؤالاً', hundred: 'سؤال' };
export const STATEMENTS: NounForms = { one: 'عبارة واحدة', two: 'عبارتان', twoGen: 'عبارتين', few: 'عبارات', many: 'عبارةً', hundred: 'عبارة' };
export const MINUTES: NounForms = { one: 'دقيقة واحدة', two: 'دقيقتان', twoGen: 'دقيقتين', few: 'دقائق', many: 'دقيقةً', hundred: 'دقيقة' };
export const TESTS: NounForms = { one: 'اختبار واحد', two: 'اختباران', twoGen: 'اختبارين', few: 'اختبارات', many: 'اختباراً', hundred: 'اختبار' };
export const DIGITS: NounForms = { one: 'رقم واحد', two: 'رقمان', twoGen: 'رقمين', few: 'أرقام', many: 'رقماً', hundred: 'رقم' };
export const TRAPS: NounForms = { one: 'فخّ واحد', two: 'فخّان', twoGen: 'فخّين', few: 'فخاخ', many: 'فخّاً', hundred: 'فخّ' };
export const TIMES: NounForms = { one: 'مرّة واحدة', two: 'مرّتان', twoGen: 'مرّتين', few: 'مرّات', many: 'مرّةً', hundred: 'مرّة' };

/**
 * التاريخ بالتقويم الميلادي وأرقام غربية، صراحةً.
 *
 * ‎'ar-SA'‎ وحده يعطي التقويم الهجري في أغلب المتصفّحات، و‎'ar'‎ وحده
 * يعطي أرقاماً مشرقية في بعضها. الإعداد الصريح يعطي النتيجة نفسها
 * في كل مكان، على الخادم وفي المتصفّح.
 */
export function formatDate( iso: string, style: 'long' | 'short' = 'long' ): string {
	return new Intl.DateTimeFormat( 'ar-u-ca-gregory-nu-latn', {
		year: 'numeric',
		month: style === 'long' ? 'long' : 'short',
		day: 'numeric',
	} ).format( new Date( iso ) );
}
