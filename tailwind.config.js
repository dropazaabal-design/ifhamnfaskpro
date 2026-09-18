/**
 * إعداد Tailwind لقالب Matjar Pro.
 *
 * مبدأان يحكمان هذا الملف:
 *
 * 1. لا لون سِتّ عشري هنا. كل لون يشير إلى متغيّر CSS يطبعه PHP من
 *    الـ Customizer، بصيغة قنوات RGB «194 65 12»، وهي الصيغة الوحيدة التي
 *    تُبقي مُعدِّلات الشفافية (bg-cta/10) عاملة فوق المتغيّرات.
 *
 * 2. لا إضافة rtl ولا ملف تنسيق معاكس. نستخدم خصائص CSS المنطقية التي
 *    يوفّرها Tailwind أصلاً (ps/pe، ms/me، start/end، text-start)، فينقلب
 *    التصميم مع اتجاه الصفحة دون سطر إضافي.
 */

/** @type {import('tailwindcss').Config} */
module.exports = {
	content: [
		'./*.php',
		'./inc/**/*.php',
		'./template-parts/**/*.php',
		'./woocommerce/**/*.php',
		'./src/js/**/*.js',
	],
	/**
	 * أصناف لا نملك مصدرها: يطبعها ووردبريس وووكومرس في HTML لا يمرّ عبر
	 * ملفات القالب، فلا يجدها ماسح Tailwind ويقتطع قواعدها. أخطرها
	 * screen-reader-text: بدون قاعدتها تظهر النصوص المخصّصة لقارئ الشاشة
	 * مكشوفة في الصفحة.
	 */
	safelist: [
		'screen-reader-text',
		'woocommerce-message',
		'woocommerce-info',
		'woocommerce-error',
		'woocommerce-notice',
		'woocommerce-form__label-for-checkbox',
		'form-row',
		'input-text',
		'required',
		// يُبنى في مرشِّح PHP لا في القالب، فلا يراه مُحلّل Tailwind.
		'mp-optional',
		'star-rating',
		'stars',
		'price',
		'button',
		'select2-container',
		'select2-selection--single',
		// السلة والدفع: كلها من مخرَج ووكومرس لا من قوالبنا.
		'wc_payment_methods',
		'wc_payment_method',
		// معرّف بوابة الدفع عند الاستلام: ووكومرس يبنيه، فلا يراه المُحلّل.
		'payment_method_cod',
		'payment_box',
		'woocommerce-checkout-payment',
		'woocommerce-terms-and-conditions-wrapper',
		'cart_totals',
		'checkout-button',
		'wc-proceed-to-checkout',
		'shop_table',
		'form-row-first',
		'form-row-last',
		'validate-required',
		'variation',
		'qty',
		'woocommerce-Reviews',
		'commentlist',
		'comment_container',
		'avatar',
		'meta',
		'description',
		'star-rating',
		'woocommerce-ordering',
		'woocommerce-pagination',
		'related',
		'upsells',
		'product_title',
		'stock',
		'in-stock',
		'out-of-stock',
		'woocommerce-variation-price',
		'woocommerce-variation-availability',
		// صفحة الحساب
		'woocommerce-table--order-details',
		'woocommerce-table--customer-details',
		'woocommerce-customer-details',
		'woocommerce-order-details__title',
		'woocommerce-column__title',
		'woocommerce-MyAccount-navigation-link',
		'is-active',
	],
	theme: {
		extend: {
			colors: {
				ink: 'rgb(var(--mp-ink) / <alpha-value>)',
				cream: 'rgb(var(--mp-bg) / <alpha-value>)',
				surface: 'rgb(var(--mp-surface) / <alpha-value>)',
				line: 'rgb(var(--mp-border) / <alpha-value>)',
				body: 'rgb(var(--mp-text) / <alpha-value>)',
				faint: 'rgb(var(--mp-muted) / <alpha-value>)',
				cta: {
					DEFAULT: 'rgb(var(--mp-cta) / <alpha-value>)',
					hover: 'rgb(var(--mp-cta-hover) / <alpha-value>)',
					fg: 'rgb(var(--mp-cta-fg) / <alpha-value>)',
				},
				accent: {
					DEFAULT: 'rgb(var(--mp-accent) / <alpha-value>)',
					ink: 'rgb(var(--mp-accent-ink) / <alpha-value>)',
				},
				amber: 'rgb(var(--mp-amber) / <alpha-value>)',
				sale: 'rgb(var(--mp-sale) / <alpha-value>)',
				success: 'rgb(var(--mp-success) / <alpha-value>)',
				info: 'rgb(var(--mp-info) / <alpha-value>)',
			},
			fontFamily: {
				sans: ['var(--mp-font-sans)'],
			},
			fontSize: {
				// مقاسات مضبوطة للعربية: ارتفاع السطر أوسع من افتراضي Tailwind،
				// لأن التشكيل والنقاط تحتاج مجالاً رأسياً أكبر من اللاتينية.
				xs: ['0.6875rem', { lineHeight: '1.6' }],
				sm: ['0.8125rem', { lineHeight: '1.75' }],
				base: ['0.9375rem', { lineHeight: '1.9' }],
				lg: ['1.0625rem', { lineHeight: '1.75' }],
				xl: ['1.3125rem', { lineHeight: '1.55' }],
				'2xl': ['1.625rem', { lineHeight: '1.4' }],
				'3xl': ['2.125rem', { lineHeight: '1.3' }],
				'4xl': ['2.625rem', { lineHeight: '1.22' }],
			},
			borderRadius: {
				lg: '0.625rem',
				xl: '0.75rem',
				'2xl': '1rem',
			},
			boxShadow: {
				// مشتقّ من حبر اللوحة: ظلّ كحلي تحت لوحة دافئة يُقرأ غريباً.
				bar: '0 -6px 20px -12px rgb(var(--mp-shadow) / 0.28)',
				card: '0 2px 10px -6px rgb(var(--mp-shadow) / 0.18)',
			},
			maxWidth: {
				'screen-xl': '1280px',
			},
			transitionDuration: {
				DEFAULT: '160ms',
			},
		},
	},
	corePlugins: {
		// القالب لا يستخدم float ولا clear إطلاقاً، وهما أكبر مصدر لأخطاء RTL.
		float: false,
		clear: false,
	},
	plugins: [],
};
