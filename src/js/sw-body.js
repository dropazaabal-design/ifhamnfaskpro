/**
 * جسم عامل الخدمة. تسبقه ثوابت يطبعها inc/pwa.php:
 * MP_VERSION و MP_SHELL و MP_OFFLINE و MP_SKIP.
 */

const MP_SHELL_CACHE = 'mp-shell-' + MP_VERSION;
const MP_PAGE_CACHE = 'mp-pages-' + MP_VERSION;
const MP_IMAGE_CACHE = 'mp-images-' + MP_VERSION;
const MP_IMAGE_MAX = 60;

self.addEventListener( 'install', ( event ) => {
	event.waitUntil(
		caches
			.open( MP_SHELL_CACHE )
			// addAll يفشل كلّه إذا فشل واحد: كل أصل على حدة حتى لا يسقط
			// التثبيت بسبب ملف خطّ واحد.
			.then( ( cache ) => Promise.all( MP_SHELL.map( ( url ) => cache.add( url ).catch( () => null ) ) ) )
			.then( () => self.skipWaiting() )
	);
} );

self.addEventListener( 'activate', ( event ) => {
	const keep = [ MP_SHELL_CACHE, MP_PAGE_CACHE, MP_IMAGE_CACHE ];

	event.waitUntil(
		caches
			.keys()
			.then( ( names ) => Promise.all(
				names.filter( ( name ) => name.startsWith( 'mp-' ) && ! keep.includes( name ) )
					.map( ( name ) => caches.delete( name ) )
			) )
			.then( () => self.clients.claim() )
	);
} );

/**
 * هل هذه الاستجابة خاصّة بزائر بعينه.
 *
 * كان الفحص يعتمد على cookieStore وحده، وهي غير موجودة في Safari ولا
 * Firefox — فكان يُعيد «لا جلسة» عليهما ويُخزّن صفحةَ زائرٍ مسجّل. أي أن
 * حرسي كان يعمل في متصفّح واحد ويسقط في الباقي.
 *
 * فصار الاعتماد على ترويسة الاستجابة أوّلاً: ووردبريس وووكومرس يرسلان
 * no-store أو private أو no-cache لكل صفحة تحمل حالة — وهي ترويسة قياسية
 * تصل في كل متصفّح. وcookieStore تبقى فحصاً إضافياً حيث تتوفّر.
 *
 * @param {Response} response الاستجابة.
 * @return {Promise<boolean>} صحيح إن وجب ألّا تُخزَّن.
 */
const isPrivate = async ( response ) => {
	const control = ( response.headers.get( 'Cache-Control' ) || '' ).toLowerCase();

	if ( control.includes( 'no-store' ) || control.includes( 'private' ) || control.includes( 'no-cache' ) ) {
		return true;
	}

	if ( ! self.cookieStore ) {
		return false;
	}

	try {
		const cookies = await self.cookieStore.getAll();

		return cookies.some( ( cookie ) =>
			cookie.name.startsWith( 'wordpress_logged_in' ) ||
			cookie.name.startsWith( 'woocommerce_items_in_cart' ) ||
			cookie.name.startsWith( 'woocommerce_cart_hash' ) ||
			cookie.name.startsWith( 'wp_woocommerce_session' )
		);
	} catch ( error ) {
		// تعذّرت القراءة: يُفترض الأسوأ ولا يُخزَّن شيء.
		return true;
	}
};

/**
 * هل هذا الطلب خارج نطاق عامل الخدمة.
 *
 * @param {Request} request الطلب.
 * @param {URL}     url     عنوانه.
 * @return {boolean} صحيح إن وجب تركه للمتصفح.
 */
const isExcluded = ( request, url ) => {
	if ( 'GET' !== request.method || url.origin !== self.location.origin ) {
		return true;
	}

	if ( url.searchParams.has( 'wc-ajax' ) || url.searchParams.has( 'preview' ) || url.searchParams.has( 'customize_changeset_uuid' ) ) {
		return true;
	}

	return MP_SKIP.some( ( path ) => url.pathname === path || url.pathname.startsWith( path + '/' ) );
};

/**
 * يقصّ ذاكرة الصور حتى لا تنمو بلا حدّ.
 */
const trimImages = async () => {
	const cache = await caches.open( MP_IMAGE_CACHE );
	const keys = await cache.keys();

	for ( let i = 0; i < keys.length - MP_IMAGE_MAX; i++ ) {
		await cache.delete( keys[ i ] );
	}
};

self.addEventListener( 'fetch', ( event ) => {
	const request = event.request;
	const url = new URL( request.url );

	if ( isExcluded( request, url ) ) {
		return;
	}

	// صفحات HTML: الشبكة أولاً دائماً. المخزّن للانقطاع وحده، فلا يرى
	// زائرٌ متّصلٌ سعراً أو مخزوناً قديماً.
	if ( request.mode === 'navigate' ) {
		event.respondWith(
			fetch( request )
				.then( async ( response ) => {
					if ( response.ok && ! ( await isPrivate( response ) ) ) {
						const copy = response.clone();
						caches.open( MP_PAGE_CACHE ).then( ( cache ) => cache.put( request, copy ) );
					}

					return response;
				} )
				.catch( async () => ( await caches.match( request ) ) || caches.match( MP_OFFLINE ) )
		);

		return;
	}

	const isImage = 'image' === request.destination;
	const isAsset = [ 'style', 'script', 'font' ].includes( request.destination );

	if ( ! isImage && ! isAsset ) {
		return;
	}

	// الأصول الثابتة: المخزّن أولاً وتحديث صامت في الخلفية.
	event.respondWith(
		caches.match( request ).then( ( cached ) => {
			const network = fetch( request )
				.then( ( response ) => {
					if ( response.ok ) {
						const copy = response.clone();
						const name = isImage ? MP_IMAGE_CACHE : MP_SHELL_CACHE;

						caches.open( name ).then( ( cache ) => {
							cache.put( request, copy );

							if ( isImage ) {
								trimImages();
							}
						} );
					}

					return response;
				} )
				.catch( () => cached );

			return cached || network;
		} )
	);
} );
