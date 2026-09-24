/**
 * حزمة تعزيز التحويل — لا تُحمَّل إلا إذا فعّل التاجر إحدى ميزتيها.
 */

import exitIntent from './modules/exit-intent.js';
import socialProof from './modules/social-proof.js';

const boot = () => {
	exitIntent();
	socialProof();
};

if ( 'loading' === document.readyState ) {
	document.addEventListener( 'DOMContentLoaded', boot, { once: true } );
} else {
	boot();
}
