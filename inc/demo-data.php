<?php
/**
 * بيانات المتجر التجريبي.
 *
 * مفصولة عن المستورد لأنها بيانات لا منطق: التاجر قد يستبدلها بكتالوجه
 * قبل التسليم لعميله، والمستورد لا يتغيّر.
 *
 * الأسعار أرقام مجرّدة بلا عملة: ووكومرس يُنسّقها بعملة المتجر، والقالب
 * لا يثبّت عملة في أي موضع.
 *
 * @package MatjarPro
 */

defined( 'ABSPATH' ) || exit;

/**
 * أقسام المتجر التجريبي.
 *
 * @return array<string,array{name: string, description: string, image: string}>
 */
function matjar_pro_demo_categories() {
	return array(
		'abayat'      => array(
			'name'        => __( 'عبايات وفساتين', 'matjar-pro' ),
			'description' => __( 'قصّات يومية وسهرة بأقمشة تتنفّس.', 'matjar-pro' ),
			'image'       => 'abaya-classic',
		),
		'perfumes'    => array(
			'name'        => __( 'عطور وبخور', 'matjar-pro' ),
			'description' => __( 'عود ومسك وشموع، بتركيز عالٍ وثبات طويل.', 'matjar-pro' ),
			'image'       => 'perfume-oud',
		),
		'electronics' => array(
			'name'        => __( 'إلكترونيات', 'matjar-pro' ),
			'description' => __( 'صوت وشحن وساعات، بضمان سنة.', 'matjar-pro' ),
			'image'       => 'headphones-pro',
		),
		'accessories' => array(
			'name'        => __( 'إكسسوارات', 'matjar-pro' ),
			'description' => __( 'حقائب ومحافظ ونظارات تُكمل الإطلالة.', 'matjar-pro' ),
			'image'       => 'bag-tote',
		),
	);
}

/**
 * خصائص المنتجات المتغيّرة.
 *
 * @return array<string,array{label: string, terms: array<string,string>}>
 */
function matjar_pro_demo_attributes() {
	return array
	(
		'size'  => array(
			'label' => __( 'المقاس', 'matjar-pro' ),
			'terms' => array(
				's'  => __( 'S', 'matjar-pro' ),
				'm'  => __( 'M', 'matjar-pro' ),
				'l'  => __( 'L', 'matjar-pro' ),
				'xl' => __( 'XL', 'matjar-pro' ),
			),
		),
		'color' => array(
			'label' => __( 'اللون', 'matjar-pro' ),
			'terms' => array(
				'black'  => __( 'أسود', 'matjar-pro' ),
				'navy'   => __( 'كحلي', 'matjar-pro' ),
				'gray'   => __( 'رمادي', 'matjar-pro' ),
				'olive'  => __( 'زيتي', 'matjar-pro' ),
			),
		),
	);
}

/**
 * منتجات المتجر التجريبي.
 *
 * كل منتج: الاسم، القسم، السعر، سعر التخفيض، الصور، الوصف، الحالة.
 * والتشكيلة مقصودة لا عشوائية: بسيط ومتغيّر، مخفَّض وكامل السعر، متوفّر
 * ونافد وبالطلب المسبق — حتى يرى المشتري كل حالة يرسمها القالب فعلاً
 * بدل أن يُصدّق لقطةً مُنتقاة.
 *
 * @return array<int,array<string,mixed>>
 */
function matjar_pro_demo_products() {
	return array(
		array(
			'name'     => __( 'عباية كلاسيك بقَصّة مستقيمة', 'matjar-pro' ),
			'cat'      => 'abayat',
			'price'    => 349,
			'images'   => array( 'abaya-classic', 'abaya-classic-2' ),
			'featured' => true,
			'variable' => 'size',
			'excerpt'  => __( 'قَصّة مستقيمة لا تكشف الجسم، بقماش لا يشفّ ولا يتجعّد في الحقيبة.', 'matjar-pro' ),
			'body'     => __( 'قماش كريب ثقيل قليلاً فينسدل بدل أن يلتصق. الأكمام واسعة من الأعلى وضيّقة عند المعصم فلا تعوق الحركة، والطول يصل إلى مشط القدم على متوسّط الطول.', 'matjar-pro' ),
		),
		array(
			'name'    => __( 'عباية كتّان صيفية', 'matjar-pro' ),
			'cat'     => 'abayat',
			'price'   => 299,
			'sale'    => 249,
			'images'  => array( 'abaya-linen', 'abaya-linen-2' ),
			'variable' => 'size',
			'excerpt' => __( 'كتّان مخلوط يتنفّس في حرّ الصيف، ولونه لا يبهت مع الغسيل.', 'matjar-pro' ),
			'body'    => __( 'الكتّان يتجعّد بطبيعته، وهذا الخليط يقلّل ذلك دون أن يفقد تهويته. يُغسل على ٣٠ درجة ويُنشر في الظلّ.', 'matjar-pro' ),
		),
		array(
			'name'     => __( 'قفطان مطرّز بغرزة يدوية', 'matjar-pro' ),
			'cat'      => 'abayat',
			'price'    => 529,
			'images'   => array( 'kaftan-royal', 'kaftan-royal-2' ),
			'featured' => true,
			'excerpt'  => __( 'تطريز يدوي على الصدر والأكمام، وكل قطعة تختلف قليلاً عن الأخرى.', 'matjar-pro' ),
			'body'     => __( 'الغرزة يدوية، فلا تتوقّع تطابقاً تامّاً بين قطعتين — هذا أثر الصنعة لا عيب فيها. يُنظَّف جافّاً.', 'matjar-pro' ),
		),
		array(
			'name'    => __( 'قفطان لؤلؤي بأكمام واسعة', 'matjar-pro' ),
			'cat'     => 'abayat',
			'price'   => 479,
			'images'  => array( 'kaftan-pearl', 'kaftan-pearl-2' ),
			'excerpt' => __( 'أكمام واسعة وقصّة مريحة، يصلح للمناسبات وللبيت معاً.', 'matjar-pro' ),
		),
		array(
			'name'    => __( 'شيلة حرير طبيعي', 'matjar-pro' ),
			'cat'     => 'abayat',
			'price'   => 129,
			'images'  => array( 'scarf-silk', 'scarf-silk-2' ),
			'excerpt' => __( 'حرير طبيعي ١٠٠٪، يثبت على الرأس بلا دبّوس.', 'matjar-pro' ),
		),
		array(
			'name'    => __( 'شيلة شيفون سادة', 'matjar-pro' ),
			'cat'     => 'abayat',
			'price'   => 89,
			'sale'    => 69,
			'images'  => array( 'scarf-chiffon', 'scarf-chiffon-2' ),
			'excerpt' => __( 'خفيفة وغير شفّافة، بأطراف مخيطة لا تنسلّ.', 'matjar-pro' ),
		),
		array(
			'name'     => __( 'عطر عود ملكي ٥٠ مل', 'matjar-pro' ),
			'cat'      => 'perfumes',
			'price'    => 389,
			'images'   => array( 'perfume-oud', 'perfume-oud-2' ),
			'featured' => true,
			'excerpt'  => __( 'عود وورد وعنبر، ثبات يتجاوز ثماني ساعات على الملابس.', 'matjar-pro' ),
			'body'     => __( 'تركيز زيتي عالٍ، فرشّة واحدة على الرسغ تكفي. يُحفظ بعيداً عن الشمس المباشرة ليحافظ على تركيبته.', 'matjar-pro' ),
		),
		array(
			'name'    => __( 'عطر مسك أبيض ١٠٠ مل', 'matjar-pro' ),
			'cat'     => 'perfumes',
			'price'   => 259,
			'sale'    => 219,
			'images'  => array( 'perfume-musk', 'perfume-musk-2' ),
			'excerpt' => __( 'مسك هادئ يصلح للدوام والاجتماعات، بلا حدّة.', 'matjar-pro' ),
		),
		array(
			'name'    => __( 'عود كمبودي معتّق', 'matjar-pro' ),
			'cat'     => 'perfumes',
			'price'   => 649,
			'images'  => array( 'oud-wood', 'oud-wood-2' ),
			'stock'   => 'onbackorder',
			'excerpt' => __( 'قطع مختارة يدوياً، تُباع بالوزن ودُفعتها محدودة.', 'matjar-pro' ),
		),
		array(
			'name'    => __( 'شمعة عنبر معطّرة', 'matjar-pro' ),
			'cat'     => 'perfumes',
			'price'   => 79,
			'images'  => array( 'candle-amber', 'candle-amber-2' ),
			'excerpt' => __( 'شمع صويا يحترق ٤٠ ساعة بلا سناج.', 'matjar-pro' ),
		),
		array(
			'name'     => __( 'سمّاعة رأس لاسلكية بعزل ضجيج', 'matjar-pro' ),
			'cat'      => 'electronics',
			'price'    => 449,
			'sale'     => 379,
			'images'   => array( 'headphones-pro', 'headphones-pro-2' ),
			'featured' => true,
			'variable' => 'color',
			'excerpt'  => __( 'عزل نشِط للضجيج، و٣٠ ساعة تشغيل، وشحن سريع يعطي ٥ ساعات في ١٠ دقائق.', 'matjar-pro' ),
			'body'     => __( 'الوسائد جلد صناعي يتنفّس فلا تسخن الأذن في جلسة طويلة. تُطوى في علبتها وتدعم الاتّصال بجهازين معاً.', 'matjar-pro' ),
		),
		array(
			'name'    => __( 'سمّاعات أذن لاسلكية', 'matjar-pro' ),
			'cat'     => 'electronics',
			'price'   => 249,
			'images'  => array( 'earbuds-air', 'earbuds-air-2' ),
			'excerpt' => __( 'مقاومة للعرق، و٦ ساعات تشغيل و٢٤ مع العلبة.', 'matjar-pro' ),
		),
		array(
			'name'     => __( 'ساعة ذكية رياضية', 'matjar-pro' ),
			'cat'      => 'electronics',
			'price'    => 599,
			'images'   => array( 'watch-smart', 'watch-smart-2' ),
			'featured' => true,
			'variable' => 'color',
			'excerpt'  => __( 'قياس نبض ونوم وأكسجين، وبطارية تصمد سبعة أيام.', 'matjar-pro' ),
		),
		array(
			'name'    => __( 'حامل جوال مغناطيسي للسيارة', 'matjar-pro' ),
			'cat'     => 'electronics',
			'price'   => 59,
			'images'  => array( 'phone-stand', 'phone-stand-2' ),
			'excerpt' => __( 'مغناطيس قوي يثبت الجوال على المطبّات، وتركيب بلا أدوات.', 'matjar-pro' ),
		),
		array(
			'name'    => __( 'مكبّر صوت محمول مقاوم للماء', 'matjar-pro' ),
			'cat'     => 'electronics',
			'price'   => 299,
			'images'  => array( 'speaker-mini', 'speaker-mini-2' ),
			'excerpt' => __( 'مقاومة IPX7 فيتحمّل السقوط في المسبح، و١٢ ساعة تشغيل.', 'matjar-pro' ),
		),
		array(
			'name'    => __( 'بطارية متنقّلة ٢٠٠٠٠ مللي أمبير', 'matjar-pro' ),
			'cat'     => 'electronics',
			'price'   => 139,
			'images'  => array( 'powerbank-slim', 'powerbank-slim-2' ),
			'stock'   => 'outofstock',
			'excerpt' => __( 'تشحن الجوال أربع مرّات، ومنفذان يعملان معاً.', 'matjar-pro' ),
		),
		array(
			'name'     => __( 'حقيبة يد جلدية بحزام قابل للفصل', 'matjar-pro' ),
			'cat'      => 'accessories',
			'price'    => 429,
			'images'   => array( 'bag-tote', 'bag-tote-2' ),
			'featured' => true,
			'excerpt'  => __( 'جلد طبيعي يلين مع الاستعمال، وتتّسع لحاسب ١٣ بوصة.', 'matjar-pro' ),
		),
		array(
			'name'    => __( 'محفظة بطاقات نحيفة', 'matjar-pro' ),
			'cat'     => 'accessories',
			'price'   => 119,
			'images'  => array( 'wallet-card', 'wallet-card-2' ),
			'excerpt' => __( 'ستّ بطاقات وحجرة للأوراق، بحجب RFID.', 'matjar-pro' ),
		),
		array(
			'name'    => __( 'نظارة شمسية بإطار معدني', 'matjar-pro' ),
			'cat'     => 'accessories',
			'price'   => 199,
			'sale'    => 159,
			'images'  => array( 'glasses-sun', 'glasses-sun-2' ),
			'excerpt' => __( 'عدسات مستقطبة بحماية UV400، وإطار خفيف لا يترك أثراً على الأنف.', 'matjar-pro' ),
		),
		array(
			'name'    => __( 'مصباح مكتب بإضاءة قابلة للتعديل', 'matjar-pro' ),
			'cat'     => 'accessories',
			'price'   => 189,
			'images'  => array( 'lamp-desk', 'lamp-desk-2' ),
			'excerpt' => __( 'ثلاث درجات حرارة لون، وذراع يثبت في أي زاوية.', 'matjar-pro' ),
		),
	);
}

/**
 * صفحات المتجر الثابتة.
 *
 * صفحات السياسات ليست حشواً: متجرٌ بلا سياسة شحن وإرجاع معلنة يخسر الثقة
 * عند أوّل تردّد، وبوّابات الدفع تطلبها في التوثيق.
 *
 * @return array<string,array{title: string, body: string}>
 */
function matjar_pro_demo_pages() {
	return array(
		'about'    => array(
			'title' => __( 'من نحن', 'matjar-pro' ),
			'body'  => __( "بدأ المتجر من طلب واحد، ثم صار عادةً عند من جرّبه.\n\nنختار ما نبيعه بأنفسنا، ونصوّره كما هو بلا معالجة تُجمّل العيب. وإن وصلك ما لا يطابق وصفه، فالإرجاع مجاني ولا نسأل عن السبب.\n\nاستبدل هذا النصّ بقصّة متجرك الحقيقية قبل الإطلاق.", 'matjar-pro' ),
		),
		'shipping' => array(
			'title' => __( 'سياسة الشحن', 'matjar-pro' ),
			'body'  => __( "نشحن خلال يوم عمل واحد من تأكيد الطلب.\n\nالتوصيل داخل المدن الرئيسية من يومين إلى ثلاثة أيام عمل، وباقي المناطق من ثلاثة إلى خمسة. يصلك رقم الشحنة برسالة نصّية فور خروجها من المستودع.\n\nالشحن مجاني للطلبات فوق الحدّ المعلن في سلّة الشراء، ويُحتسب تلقائياً.\n\nعدّل هذه الأرقام لتطابق شركة الشحن التي تتعامل معها.", 'matjar-pro' ),
		),
		'returns'  => array(
			'title' => __( 'سياسة الإرجاع', 'matjar-pro' ),
			'body'  => __( "لك أن ترجع أي قطعة خلال ١٤ يوماً من استلامها.\n\nالشرط الوحيد أن تكون بحالتها كما وصلت: غير مستعملة، وبملصقاتها، وفي عبوتها. العطور المفتوحة والملابس الداخلية مستثناة لأسباب صحّية.\n\nيُعاد المبلغ إلى وسيلة الدفع نفسها خلال خمسة أيام عمل من وصول القطعة إلينا.\n\nعدّل المدّة والاستثناءات بما يوافق نظام بلدك.", 'matjar-pro' ),
		),
		'faq'      => array(
			'title' => __( 'الأسئلة الشائعة', 'matjar-pro' ),
			'body'  => __( "هل الدفع عند الاستلام متاح؟\nنعم، ولكل المدن التي نغطّيها. تدفع للمندوب نقداً أو بالبطاقة.\n\nهل أستطيع تعديل طلبي بعد تأكيده؟\nنعم ما دام لم يخرج من المستودع. راسلنا برقم الطلب.\n\nكم يستغرق ردّ خدمة العملاء؟\nخلال ساعتين في أيام العمل.\n\nاستبدل هذه الأسئلة بما يسألك عنه عملاؤك فعلاً.", 'matjar-pro' ),
		),
		'contact'  => array(
			'title' => __( 'اتصل بنا', 'matjar-pro' ),
			'body'  => __( "نردّ خلال ساعتين في أيام العمل.\n\nضع هنا رقم واتساب متجرك وبريده وساعات العمل، أو ركّب نموذج تواصل من الإضافة التي تفضّلها.", 'matjar-pro' ),
		),
	);
}
