/** أرقام عربية مشرقية للنصوص المعروضة. */
export const toArabicDigits = ( s: string | number ) =>
	String( s ).replace( /[0-9]/g, ( d ) => '٠١٢٣٤٥٦٧٨٩'[ Number( d ) ] ).replace( /\./g, '٫' );

/**
 * العدد والمعدود بقواعد العربية.
 *
 * «٧ عبارة» خطأ يلاحظه كل قارئ عربي، ويقرأ الصفحة معه أقلّ عناية:
 * ١ مفرد، ٢ مثنّى، ٣–١٠ جمع، ١١–٩٩ مفرد منصوب، ومئة فما فوق مفرد.
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
	const d = toArabicDigits( n );
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
export const TIMES: NounForms = { one: 'مرّة واحدة', two: 'مرّتان', twoGen: 'مرّتين', few: 'مرّات', many: 'مرّةً', hundred: 'مرّة' };

/**
 * التاريخ بالتقويم الميلادي وأرقام عربية، صراحةً.
 *
 * ‎'ar-SA'‎ وحده يعطي التقويم الهجري في أغلب المتصفّحات، و‎'ar'‎ وحده
 * يعطي أرقاماً لاتينية في بعضها. الإعداد الصريح يعطي النتيجة نفسها
 * في كل مكان، على الخادم وفي المتصفّح.
 */
export function formatDate( iso: string, style: 'long' | 'short' = 'long' ): string {
	return new Intl.DateTimeFormat( 'ar-u-ca-gregory-nu-arab', {
		year: 'numeric',
		month: style === 'long' ? 'long' : 'short',
		day: 'numeric',
	} ).format( new Date( iso ) );
}
