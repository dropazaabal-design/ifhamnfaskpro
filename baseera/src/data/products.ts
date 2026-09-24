/**
 * المنتجات الرقمية التي تموّل المنصّة.
 *
 * ثلاث قواعد للتسويق هنا، لأن التسويق الذي يكذب يهدم ما بنته الاختبارات:
 *
 * ١ — الإفصاح: كل توصية مكتوب بجوارها أنها من الجهة التي تموّل المنصّة.
 * ٢ — الصلة: التوصية مرتبطة بموضوع الاختبار، لا بنتيجتك «السيّئة». لا
 *     نقول لأحد «نتيجتك منخفضة، اشترِ هذا» — هذا استغلال للقلق.
 * ٣ — القياس: كل رابط يحمل UTM، فيعرف المتجر ما جاء من بصيرة وما حوّله.
 */
import { STORE } from '../config.ts';

export interface Product {
	id: string;
	name: string;
	pitch: string;
	/** المواضيع التي يغطّيها، تطابق product.topic في الاختبارات. */
	topics: string[];
	url: string;
	format: string;
}

export const products: Product[] = [
	{
		id: 'visual-library',
		name: 'مكتبة الوعي البصري',
		pitch: 'خمسون خريطة بصرية في المال والإنتاجية والعادات وعلم النفس — كل قاعدة تُقرأ في دقيقتين.',
		topics: [ 'المال', 'الإنتاجية', 'العادات', 'علم النفس' ],
		url: STORE.url,
		format: 'خرائط بصرية رقمية',
	},
];

/** رابط المنتج مع وسوم قياس الحملة. */
export function productLink( product: Product, campaign: string, medium = 'result' ): string {
	const u = new URL( product.url );
	u.searchParams.set( 'utm_source', STORE.utmSource );
	u.searchParams.set( 'utm_medium', medium );
	u.searchParams.set( 'utm_campaign', campaign );

	return u.toString();
}

export function productFor( topic: string | undefined ): Product | undefined {
	return topic ? products.find( ( p ) => p.topics.includes( topic ) ) : undefined;
}
