import { settings } from './util.js';

/**
 * تسجيل عامل الخدمة.
 *
 * بعد load لا قبله: التسجيل يفتح خيطاً ويبدأ تخزيناً مسبقاً، وتقديمه على
 * أول رسم يزاحم تحميل الصورة الأولى — وهي أثقل ما في صفحة منتج.
 *
 * والعنوان يأتي من PHP لا يُبنى هنا، لأنه يتغيّر مع تركيب الروابط الدائمة.
 */
export default function pwa() {
	const url = settings().swUrl;

	if ( ! url || ! ( 'serviceWorker' in navigator ) ) {
		return;
	}

	window.addEventListener(
		'load',
		() => {
			// النطاق «/» تسمح به ترويسة Service-Worker-Allowed التي يرسلها
			// PHP، فيعمل حتى حين يكون عنوان الملفّ متغيّر استعلام.
			navigator.serviceWorker.register( url, { scope: '/' } ).catch( () => {
				// لا تسجيل: الموقع يعمل كما هو، بلا طبقة دون اتصال.
			} );
		},
		{ once: true }
	);
}
