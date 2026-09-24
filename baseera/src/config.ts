/**
 * إعدادات المنصّة — كل ما يتغيّر بين بيئة وأخرى هنا وحده.
 *
 * لا مفتاح سرّي في هذا الملف: معرّف ناشر AdSense علنيٌّ بطبيعته، يظهر في
 * ads.txt وفي كل صفحة. والقيم الفارغة تُطفئ ميزاتها بدل أن تكسرها.
 */
export const SITE = {
	name: 'بصيرة',
	tagline: 'اعرف نفسك بعُمق — وبصدق عن حدود ما تعرفه',
	/** العنوان النهائي. تغييره يغيّر canonical وخريطة الموقع وصور المعاينة. */
	url: 'https://baseera.kitabwbs.com',
	locale: 'ar',
	publisher: 'كتاب وبس',
	publisherUrl: 'https://www.kitabwbs.com',
	twitter: '@kitabwbs',
	contactEmail: 'hello@kitabwbs.com',
} as const;

/** المتجر الذي تموّل منتجاتُه المنصّة. */
export const STORE = {
	url: 'https://www.kitabwbs.com',
	discountCode: 'وعي25',
	/** يُلحق بكل رابط منتج ليقيس المتجر ما جاء من بصيرة. */
	utmSource: 'baseera',
} as const;

/**
 * AdSense.
 *
 * اتركه فارغاً فلا يُحمَّل سكربت ولا تُحجز مساحة: المنصّة تعمل بلا إعلانات
 * حتى يُقبل الحساب. وحين يُعبّأ يظهر ads.txt تلقائياً في جذر الموقع.
 */
export const ADSENSE = {
	/** مثل ca-pub-1234567890123456 */
	client: '',
	/** معرّفات الوحدات من لوحة AdSense. */
	slots: {
		inArticle: '',
		afterResult: '',
		sidebar: '',
	},
} as const;

/** نموذج النشرة البريدية: عنوان POST لأي مزوّد (اتركه فارغاً لإخفائه). */
export const NEWSLETTER = {
	action: '',
} as const;
